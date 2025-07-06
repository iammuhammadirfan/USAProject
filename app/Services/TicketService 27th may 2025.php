<?php
namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\generatedTicket;
use App\Events\ReloadTicketsTable;
use Illuminate\Support\Facades\Auth;
use App\Events\CancelledTickets;
use App\Events\TicketsAnalytics;
use App\Events\MaxTicketsReached;
use App\Events\UncheckedInTickets;
use Illuminate\Support\Collection;

class TicketService
{
    protected $redis;
    
    const TICKET_LUA = <<<'LUA'
        local counterKey = KEYS[1]      -- "tickets:counter:date"
        local issuedKey = KEYS[2]       -- "tickets:issued:date"
        local cancelledKey = KEYS[3]    -- "tickets:cancelled:date"
        local maxNumbersKey = KEYS[4]   -- "tickets:max_numbers"
        local today = ARGV[1]          -- Date string
        local lockToken = ARGV[2]      -- Unique identifier

        -- First try to get a cancelled ticket (FIFO)
        local cancelledNumber = redis.call('LPOP', cancelledKey)
        if cancelledNumber then
            -- Mark as reissued
            redis.call('HSET', issuedKey, cancelledNumber, lockToken)
            return tonumber(cancelledNumber)
        end

        -- No cancelled tickets available, proceed with normal increment
        local nextNumber = redis.call('INCR', counterKey)
        
        -- Check if this number was already issued (handles race conditions)
        if redis.call('HEXISTS', issuedKey, nextNumber) == 1 then
            -- If duplicate, find next available number
            local maxIssued = tonumber(redis.call('HGET', maxNumbersKey, today)) or nextNumber
            nextNumber = maxIssued + 1
            redis.call('SET', counterKey, nextNumber)
        end
        
        -- Update max number and mark as issued
        redis.call('HSET', maxNumbersKey, today, nextNumber)
        redis.call('HSET', issuedKey, nextNumber, lockToken)
        
        return nextNumber
    LUA;

    const TICKET_LUA_NO_CANCELLED = <<<'LUA'
        local counterKey = KEYS[1]      -- "tickets:counter:date"
        local issuedKey = KEYS[2]       -- "tickets:issued:date"
        local maxNumbersKey = KEYS[3]   -- "tickets:max_numbers"
        local today = ARGV[1]          -- Date string
        local lockToken = ARGV[2]      -- Unique identifier

        -- Get next number from counter
        local nextNumber = redis.call('INCR', counterKey)
        
        -- Check if this number was already issued (handles race conditions)
        if redis.call('HEXISTS', issuedKey, nextNumber) == 1 then
            -- If duplicate, find next available number
            local maxIssued = tonumber(redis.call('HGET', maxNumbersKey, today)) or nextNumber
            nextNumber = maxIssued + 1
            redis.call('SET', counterKey, nextNumber)
        end
        
        -- Update max number and mark as issued
        redis.call('HSET', maxNumbersKey, today, nextNumber)
        redis.call('HSET', issuedKey, nextNumber, lockToken)
        
        return nextNumber
    LUA;

    public function __construct()
    {
        $this->redis = Redis::connection();
    }

    public function generateTickets($request)
    {
        $todaysDate = now()->format("Y-m-d");
        
        // Check ticket limits
        if ($this->exceedsTicketLimit($todaysDate)) {
            return $this->limitExceededResponse();
        }

        DB::beginTransaction();
        try {
            if ($request->input('submission_type') == 1) {
                $result = $this->handleMultipleTickets($request,$todaysDate);
            } else {
                $result = $this->handleSingleTicket($request,$todaysDate);
            }

            DB::commit();
            $this->updateAnalytics();

            return response()->json([
                'status' => 200,
                'id' => $result['id'] ?? null,
                'ticket_id' => $result['ticket_id'] ?? null,
                'message' => 'Ticket Generated Successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Ticket generation failed: " . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage()
            ]);
        }
    }

    protected function handleSingleTicket($request, $todaysDate)
    {

        $currentTicketsCount = count($this->getCancelledTickets($todaysDate));

        $cancelled = $this->getCancelledTickets($todaysDate);
            
        \Log::info($cancelled);

        if($currentTicketsCount>0)
        {
            $this->assignAndRemoveTickets($todaysDate,1);
            $ticketNumber = $cancelled[0];

            DB::table('generated_tickets')
            ->whereDate('created_at', $todaysDate)
            ->where(['is_active' => 0, 'status' => 1,'ticket_number'=>$ticketNumber])
            ->where('is_cancelled','!=', 0)
            ->update([
                'is_active' => 1,
                'updated_at' => Carbon::now()
            ]);

            $maxNumbersKey = "tickets:max_numbers";
            $issuedKey = "tickets:issued:$todaysDate";
            
            // Get current max from Redis
            $currentMax = (int)Redis::get($maxNumbersKey, $todaysDate);

            Redis::set("tickets:counter:$todaysDate", $currentMax);

        } elseif ($this->userHasTicketToday($request->userId,$todaysDate)) {

            // $maxNumbersKey = "tickets:max_numbers";
            // $issuedKey = "tickets:issued:$todaysDate";
            
            // // Get current max from Redis
            // $currentMax = (int)Redis::get($maxNumbersKey, $todaysDate);

            // Redis::set("tickets:counter:$todaysDate", $currentMax);

            // Redis::rpush(
            //     "tickets:cancelled:" .$todaysDate,$ticketNumber
            // );

            throw new \Exception('You already have a ticket for today');
        } else {
            // Get next available ticket number (prioritizes cancelled tickets)
            $ticketNumber = $this->generateTicketNumber($todaysDate);
        }

        

        // Create the ticket
        return $this->createTicket($request->userId,$ticketNumber,$request->generatedBy,$request);
    }

    protected function assignAndRemoveTickets($date, $count)
    {
         \Log::info('Cancelled tickets found');

        $redis = Redis::connection();
        $cancelledTicketsKey = "tickets:cancelled:$date";
        
        $assignedTickets = $redis->lrange($cancelledTicketsKey, 0, $count - 1);
        
        if (empty($assignedTickets)) {
            return [];
        }
        
        $redis->ltrim($cancelledTicketsKey, $count, -1);
        
        return array_map('intval', $assignedTickets);
    }

    protected function isCancelledAscending($array,$n)
    {
        if (count($array) < $n) {
            return false; // Not enough elements
        }

        return collect($array)
            ->take($n)
            ->values()
            ->every(function ($item, $key) use ($array) {
                if ($key === 0) return true; // First item, nothing to compare
                return $item === $array[$key - 1] + 1;
            });
    }


// protected function areFirstCancelledTicketsSequential($date, $numberToCheck)
// {
//     $cancelledTickets = DB::table('generated_tickets')
//         ->whereDate('created_at', $date)
//         ->where(['is_active' => 0, 'status' => 1, 'is_cancelled' => 1])
//         ->orderBy('ticket_number', 'asc')
//         ->take($numberToCheck)
//         ->pluck('ticket_number')
//         ->toArray();

//     // Not enough tickets to check
//     if (count($cancelledTickets) < $numberToCheck) {
//         return [
//             'is_sequential' => false,
//             'tickets' => $cancelledTickets,
//             'message' => "Not enough cancelled tickets (needed {$numberToCheck}, found " . count($cancelledTickets) . ")"
//         ];
//     }

//     // Check if each ticket is exactly +1 from previous
//     $isSequential = true;
//     for ($i = 1; $i < $numberToCheck; $i++) {
//         if ($cancelledTickets[$i] !== $cancelledTickets[$i-1] + 1) {
//             $isSequential = false;
//             break;
//         }
//     }

//     return [
//         'is_sequential' => $isSequential,
//         'tickets' => $cancelledTickets,
//         'message' => $isSequential 
//             ? "First {$numberToCheck} cancelled tickets are sequential" 
//             : "First {$numberToCheck} cancelled tickets are NOT sequential"
//     ];
// }

protected function getNextTicketNumberWithNoCancelledTicket($date)
{
    return $this->redis->eval(
        self::TICKET_LUA_NO_CANCELLED,
        3, // Number of keys
        "tickets:counter:$date",
        "tickets:issued:$date",
        "tickets:max_numbers",
        $date,
        uniqid()
    );
}


    protected function handleMultipleTickets($request, $todaysDate)
    {
        // Initialize variables
        $userIdsArray = array_unique(json_decode($request->userIdsObject));
        $userIdsCount = count($userIdsArray);
        $randomNumberId = mt_rand(1, 1000000000);
        $tickets = [];
        $ticketNumbers = [];

        // Validate users don't have existing tickets
        $existingTickets = DB::table('generated_tickets')
            ->whereDate('created_at', $todaysDate)
            ->where('is_cancelled', 0)
            ->whereIn('user_id', $userIdsArray)
            ->exists();

        if ($existingTickets) {
            return [
                'error' => true,
                'status' => 450,
                'message' => 'Some users already have tickets for today'
            ];
        }

        // Get cancelled tickets and latest ticket info in single queries
        $cancelledTickets = DB::table('generated_tickets')
            ->whereDate('created_at', $todaysDate)
            ->where(['is_active' => 0, 'status' => 1])
            ->where('is_cancelled','!=', 0)
            ->orderBy('ticket_number')
            ->pluck('ticket_number');

        $latestTicket = DB::table('generated_tickets')
            ->whereDate('created_at', $todaysDate)
            ->orderByDesc('ticket_number')
            ->first(['ticket_number', 'created_at']);

        // Determine ticket numbers to assign
        if ($latestTicket === null) {
            // First tickets of the day
            $ticketNumbers = range(1, $userIdsCount);
            Redis::set("tickets:counter:$todaysDate", $userIdsCount);
        } else {
            // Use cancelled tickets first if available
            // $ticketsFromCancelled = min($userIdsCount, $cancelledTickets->count());

            $cancelled = $this->getCancelledTickets($todaysDate);
            
            \Log::info(json_decode($cancelledTickets));

            // \Log::info(json_decode($cancelled));

            if ((count($cancelledTickets) > 0 && count($cancelledTickets) > $userIdsCount) || 
            (count($cancelledTickets) > 0 && count($cancelledTickets) == $userIdsCount)) 
            {
                // $ticketNumbers = $cancelledTickets->take($ticketsFromCancelled)->all();

                 \Log::info('the tickets are sequential and count is'.$userIdsCount);

                if ($this->isCancelledAscending($cancelledTickets, $userIdsCount))
                {
                    // DB::table('generated_tickets')
                    // ->whereIn('id', function($query) use($todaysDate,$count) {
                    //     $query->select('id')
                    //         ->from('generated_tickets')
                    //         ->where('is_cancelled','!=',0)
                    //         ->where(['is_active'=>0,'status' => 1])
                    //         ->whereDate('created_at', $todaysDate)
                    //         ->orderBy('ticket_number')
                    //         ->limit($count);
                    // })
                    // ->update([
                    //     'is_active' => 1,
                    //     'updated_at' => Carbon::now()
                    // ]);

                    $ticketsToUpdate = DB::table('generated_tickets')
                         ->where('is_cancelled','!=',0)
                        ->where(['is_active'=>0,'status' => 1])
                        ->whereDate('created_at', $todaysDate)
                        ->select('id','ticket_number')
                        ->orderBy('ticket_number', 'asc')
                        ->limit($userIdsCount)
                        ->get();

                    $ticketNumbers = DB::table('generated_tickets')
                        ->where('is_cancelled','!=',0)
                        ->where(['is_active'=>0,'status' => 1])
                        ->whereDate('created_at', $todaysDate)
                        ->orderBy('ticket_number', 'asc')
                        ->limit($userIdsCount)
                        ->pluck('ticket_number')
                        ->toArray();

                    \Log::info('the tickets are sequential and count is'.$userIdsCount);

                    DB::table('generated_tickets')
                        ->whereIn('id', $ticketsToUpdate->select('id'))
                        ->update([
                            'is_active' => 1,
                            'updated_at' => Carbon::now()
                        ]);

                    $this->assignAndRemoveTickets($todaysDate,$userIdsCount);

                    \Log::info($ticketNumbers);
                } else {
                    \Log::info('the tickets are not sequential');

                    $currentDbTicket = $this->getNextTicketNumberWithNoCancelledTicket($todaysDate);

                    $next_ticket = $currentDbTicket+$userIdsCount-1;

                    for ($i = 0; $i < $userIdsCount; $i++) {
                        $ticketNumbers[] = $currentDbTicket + $i;
                    }

                    Redis::set("tickets:counter:$todaysDate", $next_ticket);
                }
            } else if(count($cancelledTickets) == 0)
            {
                $currentDbTicket = $this->getNextTicketNumberWithNoCancelledTicket($todaysDate);

                $next_ticket = $currentDbTicket+$userIdsCount-1;

                for ($i = 0; $i < $userIdsCount; $i++) {
                    $ticketNumbers[] = $currentDbTicket + $i;
                }

                Redis::set("tickets:counter:$todaysDate", $next_ticket);
            } else if(count($cancelledTickets) > 0 && $userIdsCount > count($cancelledTickets))
            {
                $currentDbTicket = $this->getNextTicketNumberWithNoCancelledTicket($todaysDate);

                $next_ticket = $currentDbTicket+$userIdsCount-1;

                for ($i = 0; $i < $userIdsCount; $i++) {
                    $ticketNumbers[] = $currentDbTicket + $i;
                }

                Redis::set("tickets:counter:$todaysDate", $next_ticket);
            }
        }

        // Prepare tickets for insertion
        foreach ($userIdsArray as $key => $userId) {
            $tickets[] = [
                'user_id' => $userId,
                'is_first' => $key == 0 ? 1 : 0,
                'is_extra' => $key == 0 ? 0 : 1,
                'multiple_id' => $randomNumberId,
                'ticket_number' => $ticketNumbers[$key],
                'generated_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Insert tickets
        DB::table('generated_tickets')->insert($tickets);

        // Handle projected return time if needed
        if (session('returnTimesStatus') == 1) {
            $projectedReturnTime = collect(session('ticketsReturnTimes', []))
                ->first(fn($rt) => $ticketNumbers[0] >= $rt->start_ticket && $ticketNumbers[0] <= $rt->end_ticket)
                ?->time ?? 0;

            if ($projectedReturnTime) {
                DB::table('generated_tickets')
                    ->where('multiple_id', $randomNumberId)
                    ->update(['projected_return_time' => $projectedReturnTime]);
            }

            session()->forget(['returnTimesStatus', 'ticketsReturnTimes']);
        }

        if(Auth::user()->role == '1')
        {
            $firstTicket = DB::table('generated_tickets')
            ->where('multiple_id', $randomNumberId)
            ->select('id')
            ->first();

            DB::table('activity_log')->insertGetId([
                'staff_id' => Auth::id(),
                'ticket_id' => $firstTicket->id,
                'activity_id' => 2,
                'created_at' => Carbon::now()
            ]);
        }

        // Broadcast event
        event(new ReloadTicketsTable(generatedTicket::get_new_tickets($randomNumberId)));

        return [
            'id' => $randomNumberId,
            'ticket_numbers' => $ticketNumbers,
            'reused_cancelled' => min($userIdsCount, $cancelledTickets->count())
        ];
    }

    // protected function handleMultipleTickets($request, $todaysDate)
    // {
    //     $userIdsArray = array_unique(json_decode($request->userIdsObject));
    //     $userIdsCount = count($userIdsArray);
    //     $randomNumberId = mt_rand(1, 1000000000);
    //     $tickets = [];
        
    //     // 1. Get all available cancelled tickets (ordered chronologically)
    //     $cancelledTickets = DB::table('generated_tickets')
    //         ->whereDate('created_at', $todaysDate)
    //         ->where('is_cancelled', 1)
    //         ->where('is_active', 0)
    //         ->orderBy('ticket_number', 'asc')
    //         ->pluck('ticket_number')
    //         ->toArray();

    //     // 2. Get the latest ticket number (whether active or cancelled)
    //     $latestTicketNumber = DB::table('generated_tickets')
    //         ->whereDate('created_at', $todaysDate)
    //         ->max('ticket_number') ?? 0;

    //     // 3. Determine how many tickets we can take from cancelled pool
    //     $ticketsFromCancelled = min($userIdsCount, count($cancelledTickets));
    //     $ticketsFromNew = $userIdsCount - $ticketsFromCancelled;

    //     // 4. First check all users don't already have tickets
    //     foreach ($userIdsArray as $userId) {
    //         if ($this->userHasTicketToday($userId, $todaysDate)) {
    //             throw new \Exception("User $userId already has a ticket for today");
    //         }
    //     }

    //     // 5. Prepare ticket numbers (cancelled first, then new sequential)
    //     $ticketNumbers = [];
        
    //     // 5a. Use cancelled tickets (oldest first)
    //     if ($ticketsFromCancelled > 0) {
    //         $ticketNumbers = array_slice($cancelledTickets, 0, $ticketsFromCancelled);
            
    //         // Reactivate these cancelled tickets
    //         DB::table('generated_tickets')
    //             ->whereDate('created_at', $todaysDate)
    //             ->whereIn('ticket_number', $ticketNumbers)
    //             ->update([
    //                 'is_active' => 1,
    //                 'is_cancelled' => 0,
    //                 'updated_at' => Carbon::now()
    //             ]);
    //     }
        
    //     // 5b. Generate new sequential tickets if needed
    //     if ($ticketsFromNew > 0) {
    //         $newTicketNumbers = range($latestTicketNumber + 1, $latestTicketNumber + $ticketsFromNew);
    //         $ticketNumbers = array_merge($ticketNumbers, $newTicketNumbers);
    //     }

    //     // 6. Create all tickets
    //     foreach ($userIdsArray as $key => $userId) {
    //         $tickets[] = [
    //             'user_id' => $userId,
    //             'is_first' => $key == 0 ? 1 : 0,
    //             'is_extra' => $key == 0 ? 0 : 1,
    //             'multiple_id' => $randomNumberId,
    //             'ticket_number' => $ticketNumbers[$key],
    //             'generated_by' => Auth::id(),
    //             'created_at' => Carbon::now(),
    //             'updated_at' => Carbon::now()
    //         ];
    //     }

    //     // 7. Only insert NEW tickets (reactivated tickets are already in DB)
    //     if ($ticketsFromNew > 0) {
    //         DB::table('generated_tickets')->insert($tickets);
    //     }

    //     $this->broadcastNewTickets($randomNumberId);

    //     return ['id' => $randomNumberId];
    // }

    // protected function handleMultipleTickets($request,$todaysDate,$redis)
    // {
    //     $userIdsArray = json_decode($request->userIdsObject);
    //     $randomNumberId = mt_rand(1, 1000000000);
    //     $tickets = [];
    //     $ticketNumbers = [];
    //     $projectedReturnTime=[];

    //     $userIdsCount=count($userIdsArray);

    //         // $lockToken = $this->acquireLock($this->redis);
        
            
    //         $cancelledTickets = DB::table('generated_tickets')
    //             ->whereDate('created_at', $todaysDate)
    //             ->where(['is_active' => 0, 'status' => 1])
    //             ->where('is_cancelled', '!=', 0)
    //             ->orderBy('ticket_number', 'asc')
    //             ->select('id', 'ticket_number') 
    //             ->get();

    //         $getCancelledTicketNumbers = $cancelledTickets->pluck('ticket_number')->toArray();
            
    //         $getDeactivatedIds = $cancelledTickets->pluck('id')->toArray();
            
    //         $latestSavedTicket=DB::table('generated_tickets')
    //         ->whereDate('created_at',$todaysDate)
    //         ->select('ticket_number','created_at')
    //         ->orderBy('ticket_number', 'desc')
    //         ->first();

    //         if (collect($userIdsArray)->count() !== collect($userIdsArray)->unique()->count()) 
    //         {
    //             \Log::info(
    //                 'some users are have duplicates values '.json_encode($userIdsArray));
    //             $userIdsArray = collect($userIdsArray)->unique()->values()->all();

    //         }

    //         \Log::info('no duplicates values found '.json_encode($userIdsArray));

    //         if($latestSavedTicket == null)
    //         {
    //             $latestSavedDate='';
    //         } else {
    //             $latestSavedDate=Carbon::parse($latestSavedTicket->created_at)->format('Y-m-d');
    //         }

    //         if($latestSavedTicket == null)
    //         {
    //             $redis->set("tickets:counter:$todaysDate", $userIdsCount);

    //             $next_ticket=$redis->get('tickets:counter:$todaysDate');

    //             for ($i = 0; $i < $userIdsCount; $i++) {
    //                 $ticketNumbers[] =  1 + $i;
    //             }

    //             \Log::info('Next Ticket in queue is '.$userIdsCount.'
    //             Users are '.json_encode($userIdsArray).'
    //             Ticket Numbers are '.json_encode($ticketNumbers).'
    //             when the first ticket is a multiple ticket');
    //         } else if($todaysDate == $latestSavedDate)
    //         {
    //             $selectedTicketNumber = $this->getNextTicketNumber($redis, $todaysDate,$lockToken);

    //             foreach($userIdsArray as $userKey => $userId)
    //             {
    //                 $ticketCount = DB::table('generated_tickets')
    //                     ->whereDate('created_at', $todaysDate)
    //                     ->where([
    //                         'status' => 1, 
    //                         'user_id' => $userId, 
    //                         'is_reset'=>0,
    //                         'is_cancelled' => 0
    //                     ])->count();

    //                 if ($ticketCount > 0) {
    //                     $redis->set("tickets:counter:$todaysDate", $selectedTicketNumber - 1);

    //                     return [
    //                         'error' => true,
    //                         'status' => 450,
    //                         'message' => 'Some of the user have already been issued a ticket for today'
    //                     ];
    //                 }
    //             }
                
    //             if(count($getCancelledTicketNumbers)>0)
    //             {
    //                 \Log::info('Users are '.json_encode($userIdsArray).',All deactivated Tickets are '.json_encode($getCancelledTicketNumbers));

    //                 if($userIdsCount > count($getCancelledTicketNumbers))
    //                 {
    //                     $ticketCount = DB::table('generated_tickets')
    //                     ->whereDate('created_at', $todaysDate)
    //                     ->where(['status' => 1, 'ticket_number' => $selectedTicketNumber, 'is_reset'=>0,'is_cancelled' => 0])
    //                     ->count();

    //                     if ($ticketCount > 0) 
    //                     {
    //                         $latestTicket = DB::table('generated_tickets')
    //                         ->whereDate('created_at', $todaysDate)
    //                         ->max('ticket_number');

    //                         $currentDbTicket = $latestTicket+1;

    //                         $new_ticket_counter = DB::table('generated_tickets')
    //                             ->whereDate('created_at', $todaysDate)
    //                             ->max('ticket_number') + $userIdsCount;
                        
    //                     } else {
    //                         $currentDbTicket = $selectedTicketNumber;

    //                         $new_ticket_counter = $currentDbTicket + $userIdsCount - 1;
    //                     }

    //                     for ($i = 0; $i < $userIdsCount; $i++) {
    //                         $ticketNumbers[] = $currentDbTicket + $i;
    //                     }

    //                     $redis->set("tickets:counter:$todaysDate", $new_ticket_counter);

    //                     $next_ticket=$redis->get('tickets:counter:$todaysDate');
    //                     \Log::info('Next Ticket in queue is '.$new_ticket_counter.'
    //                     Users are '.json_encode($userIdsArray).'
    //                     Ticket Numbers are '.json_encode($ticketNumbers).'
    //                     All deactivated Tickets are '.json_encode($getCancelledTicketNumbers).'
    //                     when user tickets are greater than the number of cancelled tickets');

    //                 }

    //                 if($userIdsCount < count($getCancelledTicketNumbers))
    //                 {
    //                     $array = array_slice($getCancelledTicketNumbers, 0, $userIdsCount);

    //                     $array = array_map('intval', $array);

    //                     $tempSequence = [$array[0]];

    //                     // Iterate through the array to find the first sequential sequence
    //                     for ($i = 1; $i < count($array); $i++) {
    //                         // Check if the current number is sequential (current number should be one more than the last)
    //                         if ($array[$i] == $array[$i - 1] + 1) {
    //                             $tempSequence[] = $array[$i];  // Continue the sequence
    //                         } else {
    //                             break;  // Break the loop if the sequence is broken
    //                         }
    //                     }

    //                     if($userIdsCount === count($tempSequence))
    //                     {
    //                         // foreach($tempSequence as $key => $cancelled_ticket)
    //                         // {
    //                         //     DB::table('generated_tickets')
    //                         //     ->where(['ticket_number'=>$cancelled_ticket,'is_active'=>0,'status' => 1])
    //                         //     ->where('is_cancelled','!==',0)
    //                         //     ->whereDate('created_at', $todaysDate)
    //                         //     ->update([
    //                         //         'is_active' => 1,
    //                         //         'updated_at' => Carbon::now()
    //                         //     ]);
    //                         // }

    //                         $this->assignAndRemoveTickets($todaysDate,count($tempSequence));

    //                         DB::table('generated_tickets')
    //                             ->where(['is_active'=>0,'status' => 1])
    //                             ->where('is_cancelled','!=',0)
    //                             ->whereIn('ticket_number',$tempSequence)
    //                             ->whereDate('created_at', $todaysDate)
    //                             ->update([
    //                                 'is_active' => 1,
    //                                 'updated_at' => Carbon::now()
    //                             ]);

    //                         $ticketNumbers = $tempSequence;

    //                         $new_ticket_counter = DB::table('generated_tickets')
    //                         ->whereDate('created_at', $todaysDate)
    //                         ->max('ticket_number');

    //                         $redis->set("tickets:counter:$todaysDate", $new_ticket_counter);

    //                         $next_ticket=$redis->get('tickets:counter:$todaysDate');

    //                         \Log::info('Next Ticket in queue is '.$new_ticket_counter.'
    //                         Users are '.json_encode($userIdsArray).'
    //                         Ticket Numbers are '.json_encode($ticketNumbers).'
    //                         All deactivated Tickets are '.json_encode($getCancelledTicketNumbers).'
    //                         when user tickets are equal to first equal number of '.$userIdsCount.' from the deleted tickets');

    //                         // for ($i = 0; $i < $userIdsCount; $i++) {
    //                         //     $ticketNumbers[] = $currentDbTicket + $i;
    //                         // }

    //                     } else if ($userIdsCount > count($tempSequence))
    //                     {
    //                         $ticketCount = DB::table('generated_tickets')
    //                         ->whereDate('created_at', $todaysDate)
    //                         ->where(['status' => 1, 'ticket_number' => $selectedTicketNumber, 'is_reset'=>0,'is_cancelled' => 0])
    //                         ->count();

    //                         if ($ticketCount > 0) 
    //                         {
    //                             $latestTicket = DB::table('generated_tickets')
    //                             ->whereDate('created_at', $todaysDate)
    //                             ->max('ticket_number');

    //                             $currentDbTicket = $latestTicket+1;

    //                             $new_ticket_counter = DB::table('generated_tickets')
    //                                 ->whereDate('created_at', $todaysDate)
    //                                 ->max('ticket_number') + $userIdsCount;
                            
    //                         } else {
    //                             $currentDbTicket = $selectedTicketNumber;

    //                             $new_ticket_counter = $currentDbTicket + $userIdsCount - 1;
    //                         }


    //                         // $currentDbTicket = DB::table('generated_tickets')
    //                         // ->whereDate('created_at', $todaysDate)
    //                         // ->max('ticket_number') + 1;

    //                         for ($i = 0; $i < $userIdsCount; $i++) {
    //                             $ticketNumbers[] = $currentDbTicket + $i;
    //                         }

    //                         // $new_ticket_counter = DB::table('generated_tickets')
    //                         // ->whereDate('created_at', $todaysDate)
    //                         // ->max('ticket_number') + $userIdsCount;

    //                         $redis->set("tickets:counter:$todaysDate", $new_ticket_counter);

    //                         $next_ticket=$redis->get('tickets:counter:$todaysDate');

    //                         \Log::info('Next Ticket in queue is '.$new_ticket_counter.'
    //                         Users are '.json_encode($userIdsArray).'
    //                         Ticket Numbers are '.json_encode($ticketNumbers).'
    //                         All deactivated Tickets are '.json_encode($getCancelledTicketNumbers).'
    //                         when user tickets are greater than the first number of '.$userIdsCount.' from the deleted tickets');
    //                     } else if ($userIdsCount < count($tempSequence))
    //                     {

    //                         $ticketNumbers = array_slice($tempSequence, 0, $userIdsCount);

    //                         $new_ticket_counter = DB::table('generated_tickets')
    //                         ->whereDate('created_at', $todaysDate)
    //                         ->max('ticket_number');

    //                         // DB::table('generated_tickets')
    //                         //     ->where(['is_active'=>0,'status' => 1,'is_cancelled'=>1])
    //                         //     ->whereIn('ticket_number',$tempSequence)
    //                         //     ->whereDate('created_at', $todaysDate)
    //                         //     ->update([
    //                         //         'is_active' => 1,
    //                         //         'is_cancelled' => 1,
    //                         //         'updated_at' => Carbon::now()
    //                         //     ]);

    //                         DB::table('generated_tickets')
    //                             ->where(['is_active'=>0,'status' => 1])
    //                             ->where('is_cancelled','!=',0)
    //                             ->whereIn('ticket_number',$tempSequence)
    //                             ->whereDate('created_at', $todaysDate)
    //                             ->update([
    //                                 'is_active' => 1,
    //                                 'updated_at' => Carbon::now()
    //                             ]);

    //                         $redis->set("tickets:counter:$todaysDate", $new_ticket_counter);

    //                         $next_ticket=$redis->get('tickets:counter:$todaysDate');

    //                         \Log::info('Next Ticket in queue is '.$new_ticket_counter.'
    //                         Users are '.json_encode($userIdsArray).'
    //                         Ticket Numbers are '.json_encode($ticketNumbers).'
    //                         All deactivated Tickets are '.json_encode($getCancelledTicketNumbers).'
    //                         when user tickets are less than the to first number of '.$userIdsCount.' from the deleted tickets');
    //                     }
    //                 }

    //                 if($userIdsCount == count($getCancelledTicketNumbers))
    //                 {
    //                     $currentDbTicket = DB::table('generated_tickets')
    //                     ->whereDate('created_at', $todaysDate)
    //                     ->max('ticket_number') + 1;

    //                     for ($i = 0; $i < $userIdsCount; $i++) {
    //                         $ticketNumbers[] = $currentDbTicket + $i;
    //                     }

    //                     $new_ticket_counter = $currentDbTicket + $userIdsCount - 1;

    //                     $redis->set("tickets:counter:$todaysDate", $new_ticket_counter);

    //                     $next_ticket=$redis->get('tickets:counter:$todaysDate');

    //                     \Log::info('Next Ticket in queue is '.$new_ticket_counter.'
    //                         Users are '.json_encode($userIdsArray).'
    //                         Ticket Numbers are '.json_encode($ticketNumbers).'
    //                         All deactivated Tickets are '.json_encode($getCancelledTicketNumbers).'
    //                         when user tickets are equal to the number of deleted tickets');
    //                 }
                    
    //             } 
    //             else 
    //             {
    //                 if($selectedTicketNumber == 0)
    //                 {
    //                     $currentDbTicket = 1;
    //                 } else {
    //                     $ticketCount = DB::table('generated_tickets')
    //                     ->whereDate('created_at', $todaysDate)
    //                     ->where(['status' => 1, 'ticket_number' => $selectedTicketNumber, 'is_reset'=>0,'is_cancelled' => 0])
    //                     ->count();

    //                     if ($ticketCount > 0) 
    //                     {
    //                         $latestTicket = DB::table('generated_tickets')
    //                         ->whereDate('created_at', $todaysDate)
    //                         ->max('ticket_number');

    //                         $currentDbTicket = $latestTicket+1;

    //                         $new_ticket_counter = DB::table('generated_tickets')
    //                             ->whereDate('created_at', $todaysDate)
    //                             ->max('ticket_number') + $userIdsCount;
                        
    //                     } else {
    //                         $currentDbTicket = $selectedTicketNumber;

    //                         $new_ticket_counter = $currentDbTicket + $userIdsCount - 1;
    //                     }
    //                 }

    //                 // $new_ticket_counter = $currentDbTicket + $userIdsCount - 1;

    //                 $redis->set("tickets:counter:$todaysDate", $new_ticket_counter);

    //                 $next_ticket=$redis->get('tickets:counter:$todaysDate');

                    
    //                 $ticketNumbers = range($currentDbTicket, $currentDbTicket + $userIdsCount - 1);

    //                 \Log::info('Next Ticket in queue is '.$new_ticket_counter.'
    //                 Users are '.json_encode($userIdsArray).'
    //                 Ticket Numbers are '.json_encode($ticketNumbers).'
    //                 when there are no deleted tickets');
    //             }
    //         }

    //         foreach ($userIdsArray as $key => $userId) 
    //         {

    //             $tickets[] = [
    //                 'user_id' => $userId,
    //                 'is_first' => $key == 0 ? 1 : 0,
    //                 'is_extra' => $key == 0 ? 0 : 1,
    //                 'multiple_id' => $randomNumberId,
    //                 'ticket_number' => $ticketNumbers[$key],
    //                 'generated_by' => Auth::user()->id,
    //                 'created_at' => Carbon::now(),
    //                 'updated_at' => Carbon::now()
    //             ];
    //         }

    //     DB::table('generated_tickets')->insert($tickets);

    //     if (session('returnTimesStatus') == 1) {
    //         $projectedReturnTime = collect(session('ticketsReturnTimes', []))
    //             ->first(function ($returnTime) use ($ticketNumbers) {
    //                 return $ticketNumbers[0] >= $returnTime->start_ticket 
    //                     && $ticketNumbers[0] <= $returnTime->end_ticket;
    //             })?->time ?? 0;
        
    //         if ($projectedReturnTime) {
    //             DB::table('generated_tickets')
    //                 ->where('multiple_id', $randomNumberId)
    //                 ->update(['projected_return_time' => $projectedReturnTime]);
    //         }

    //         session()->forget(['returnTimesStatus', 'ticketsReturnTimes']);
    //     }

    //     $newTicketsData=generatedTicket::get_new_tickets($randomNumberId);

    //     event(new ReloadTicketsTable($newTicketsData));

    //     return ['id' => $randomNumberId];
    // }

    protected function userHasTicketToday($userId, $date)
    {
        return DB::table('generated_tickets')
            ->whereDate('created_at', $date)
            ->where('user_id', $userId)
            ->where('is_cancelled', 0)
            ->where('status', 1)
            ->exists();
    }

    protected function generateTicketNumber($date)
    {
        $lockToken = uniqid();
        
        return $this->redis->eval(
            self::TICKET_LUA,
            4, // Number of keys
            "tickets:counter:$date",
            "tickets:issued:$date",
            "tickets:cancelled:$date",
            "tickets:max_numbers",
            $date,
            $lockToken
        );
    }

    protected function getCancelledTickets($date)
    {
        $cancelledTicketsKey = "tickets:cancelled:$date";
        
        // Get all cancelled ticket numbers from Redis
        $cancelledTickets = $this->redis->lrange($cancelledTicketsKey, 0, -1);
        
        // Convert to integers and sort chronologically
        $cancelledTickets = array_map('intval', $cancelledTickets);
        sort($cancelledTickets);
        
        return $cancelledTickets;
    }

    protected function createTicket($userId,$ticketNumber,$generatedBy,$request)
    {
        \Log::info($userId.','.$ticketNumber.','.$generatedBy);
        
        $ticketId = DB::table('generated_tickets')->insertGetId([
            'user_id' => $userId,
            'is_first' => 1,
            'ticket_number' => $ticketNumber,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $savedTicketDetails = generatedTicket::find($ticketId);

        // Handle return times
        if (session('returnTimesStatus') == 1) {
            $projectedReturnTime = collect(session('ticketsReturnTimes'))
                ->firstWhere(fn($rt) => $ticketNumber >= $rt->start_ticket 
                                    && $ticketNumber <= $rt->end_ticket)?->time ?? null;
            
            $savedTicketDetails->update(['projected_return_time' => $projectedReturnTime]);
            session()->forget(['returnTimesStatus', 'ticketsReturnTimes']);
        }

        // Handle generated by
        if ($generatedBy == 1) {
            $savedTicketDetails->update(['generated_by' => Auth::id()]);

            DB::table('activity_log')->insertGetId([
                'staff_id' => Auth::id(),
                'user_id' => $userId,
                'ticket_id' => $ticketId,
                'activity_id' => 1,
                'created_at' => Carbon::now()
            ]);
        }

        // Handle bot flag
        if ($request->session()->pull('is_bot', false)) {
            $savedTicketDetails->update(['is_bot' => 1]);

            session()->forget(['is_bot']);
        }

        $this->broadcastNewTickets($ticketId);

        return [
                'ticket_id' => $ticketId,
                'status' => 200,
                'message' => 'Ticket generated successfully'
            ];
    }

    protected function broadcastNewTickets($identifier)
    {
        event(new ReloadTicketsTable(generatedTicket::get_new_tickets($identifier)));
    }

    // protected function userHasTicketToday($userId, $date)
    // {
    //     return DB::table('generated_tickets')
    //         ->whereDate('created_at', $date)
    //         ->where([
    //             'status' => 1, 
    //             'user_id' => $userId, 
    //             'is_reset' => 0,
    //             'is_cancelled' => 0
    //         ])->exists();
    // }

    protected function exceedsTicketLimit($date)
    {
        if (Auth::user()->role == 2) { // Admin role
            $ticketLimitChecker = DB::table('tickets_management')->first();
            $totalTickets = DB::table('generated_tickets')
                ->whereDate('created_at', $date)
                ->where(['status' => 1, 'is_active' => 1])
                ->count();

            if ($ticketLimitChecker->ticket_limit_status == 1 && 
                $totalTickets > $ticketLimitChecker->ticket_limit) {
                return true;
            }
        }
        return false;
    }

    protected function limitExceededResponse()
    {
        return response()->json([
            'status' => 550,
            'message' => Session::get('notificationMessages')
                ->firstWhere('modal_no', 12)->message
        ]);
    }

    protected function updateAnalytics()
    {
        $ticketLimitChecker = DB::table('tickets_management')->first();
        $newTotalTicketsCount = DB::table('generated_tickets')
            ->where(['status' => 1, 'is_active' => 1, 'is_cancelled' => 0])
            ->whereDate('created_at', Carbon::today())
            ->count();

        $maxTicketsLimitData = ($newTotalTicketsCount >= $ticketLimitChecker->ticket_limit && 
                            $ticketLimitChecker->ticket_limit_status == 1) ? 1 : 0;
        
        event(new MaxTicketsReached($maxTicketsLimitData));
        event(new TicketsAnalytics(generatedTicket::tickets_analytics()));
    }
}