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
use Illuminate\Support\Str;

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

    
    const TICKET_LUA_GET_CURRENT = <<<'LUA'
        local counterKey = KEYS[1]      -- "tickets:counter:date"
        local issuedKey = KEYS[2]       -- "tickets:issued:date"
        local maxNumbersKey = KEYS[3]   -- "tickets:max_numbers"
        local today = ARGV[1]          -- Date string
        

        -- Get current counter and max issued numbers
        local currentNumber = tonumber(redis.call('GET', counterKey)) or 0
        local maxIssued = tonumber(redis.call('HGET', maxNumbersKey, today)) or currentNumber

        -- Return the latest ticket number (max between counter and issued)
        return math.max(currentNumber, maxIssued)
    LUA;

    // const TICKET_LUA_WITH_CURRENT = <<<'LUA'
    //     local counterKey = KEYS[1]      -- "tickets:counter:date"
    //     local issuedKey = KEYS[2]       -- "tickets:issued:date"
    //     local cancelledKey = KEYS[3]    -- "tickets:cancelled:date"
    //     local maxNumbersKey = KEYS[4]   -- "tickets:max_numbers"
    //     local today = ARGV[1]          -- Date string
    //     local lockToken = ARGV[2]      -- Unique identifier

    //     -- Get current counter value (before incrementing)
    //     local ticketToIssue = tonumber(redis.call('INCR', counterKey)) or 0
    //     local maxIssued = tonumber(redis.call('HGET', maxNumbersKey, today)) or ticketToIssue
    //     local latestNumber = math.max(ticketToIssue, maxIssued)

    //     -- Handle race condition (duplicate number)
    //     if redis.call('HEXISTS', counterKey, ticketToIssue) == 1 then
    //         ticketToIssue = maxIssued + 1
    //         redis.call('SET', counterKey, ticketToIssue)
    //     end

        

    //     -- Try to get a cancelled ticket (FIFO)
    //     local cancelledNumber = redis.call('LPOP', cancelledKey)
    //     if cancelledNumber then
    //         redis.call('HSET', issuedKey, cancelledNumber, lockToken)
    //         return {tonumber(cancelledNumber), latestNumber} -- {next, current}
    //     end

    //     -- No cancelled tickets, proceed with increment
    //     local nextNumber = redis.call('INCR', counterKey)
        
    //     -- Handle race condition (duplicate number)
    //     if redis.call('HEXISTS', issuedKey, nextNumber) == 1 then
    //         nextNumber = maxIssued + 1
    //         redis.call('SET', counterKey, nextNumber)
    //     end
        
    //     -- Update max number and mark as issued
    //     redis.call('HSET', maxNumbersKey, today, nextNumber)
    //     redis.call('HSET', issuedKey, nextNumber, lockToken)
        
    //     return {nextNumber, latestNumber} -- {next, current}
    // LUA;

    public function __construct()
    {
        $this->redis = Redis::connection();
    }

    public function generateTickets($request)
    {
        // dd($request->all());die();

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

        $cancelledTicketsCount = count($this->getCancelledTickets($todaysDate));

        $cancelled = $this->getCancelledTickets($todaysDate);

        if($cancelledTicketsCount>0)
        {
            

            $replaced_tickets=$this->assignAndRemoveTickets($todaysDate,1);

            $ticketNumber = $replaced_tickets['removed'][0];

            DB::table('generated_tickets')
            ->whereDate('created_at', $todaysDate)
            ->where(['is_active' => 0, 'status' => 1,'ticket_number'=>$ticketNumber])
            ->where('is_cancelled','!=', 0)
            ->update([
                'is_active' => 1,
                'updated_at' => Carbon::now()
            ]);

            // \Log::info([
            //     'Assigned and removed tickets are ' => $replaced_tickets
            // ]);

            \Log::info('cancelled single tickets found.Ticket to issue is '.$ticketNumber);

        } elseif ($this->userHasTicketToday($request->userId,$todaysDate)) {
            
            throw new \Exception('You already have a ticket for today');
        } else {

            $ticketNumber = $this->generateNextTicketNumber($todaysDate);

            \Log::info('cancelled single tickets not found.Ticket to issue is '.$ticketNumber);
        }

        // Create the ticket
        return $this->createTicket($request->userId,$ticketNumber,$request->generatedBy,$request);
    }

    protected function assignAndRemoveTickets($date, $count)
    {
        $redis = Redis::connection();
        $key = "tickets:cancelled:$date";

        $initialTickets = $this->getCancelledTickets($date);
        
        // 2. Perform atomic removal
        $removedTickets = [];
        for ($i = 0; $i < $count; $i++) {
            $ticket = $redis->lpop($key);
            if ($ticket === null) break;
            $removedTickets[] = (int)$ticket;
        }

        // 3. Get remaining tickets
        $remainingTickets = $this->getCancelledTickets($date);

        \Log::info([
            'Initial tickets' => $initialTickets,
            'Removed tickets' => $removedTickets,
            'Remaining tickets' => $remainingTickets
        ]);

        return [
            'removed' => $removedTickets,
            'remaining' => $remainingTickets
        ];
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

// protected function getNextTicketNumberWithNoCancelledTicket($date)
// {
//     return $this->redis->eval(
//         self::TICKET_LUA_NO_CANCELLED,
//         3, // Number of keys
//         "tickets:counter:$date",
//         "tickets:issued:$date",
//         "tickets:max_numbers",
//         $date,
//         uniqid()
//     );
// }


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

        // $cancelledTicketsCount = count($this->getCancelledTickets($todaysDate));
        // $cancelledTickets = DB::table('generated_tickets')
        //     ->whereDate('created_at', $todaysDate)
        //     ->where(['is_active' => 0, 'status' => 1])
        //     ->where('is_cancelled','!=', 0)
        //     ->orderBy('ticket_number')
        //     ->pluck('ticket_number');

        $latestTicket = DB::table('generated_tickets')
            ->whereDate('created_at', $todaysDate)
            ->orderByDesc('ticket_number')
            ->first(['ticket_number', 'created_at']);

        // Determine ticket numbers to assign
        if ($latestTicket === null) 
        {
            // First tickets of the day
            $ticketNumbers = range(1, $userIdsCount);
            Redis::set("tickets:counter:$todaysDate", $userIdsCount);
        } else {
            // Use cancelled tickets first if available
            // $ticketsFromCancelled = min($userIdsCount, $cancelledTickets->count());

            $cancelled = $this->getCancelledTickets($todaysDate);

            if ((count($cancelled) > 0 && count($cancelled) > $userIdsCount) || 
            (count($cancelled) > 0 && count($cancelled) == $userIdsCount)) 
            {
                // $ticketNumbers = $cancelled->take($ticketsFromCancelled)->all();

                //  \Log::info('the tickets are sequential and count is'.$userIdsCount);

                $cancelledSequentialTickets = $this->isCancelledAscending($cancelled, $userIdsCount);

                \Log::info('Cancelled Sequential tickets are', [
                    'tickets' => $cancelledSequentialTickets
                ]);

                if ($this->isCancelledAscending($cancelled, $userIdsCount))
                {
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

                    \Log::info('the tickets are sequential and count is '.$userIdsCount);

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

                    $currentDbTicket = ($this->generateNextMultipleTicketNumber($todaysDate))+1;

                    $next_ticket = $currentDbTicket+$userIdsCount-1;

                    for ($i = 0; $i < $userIdsCount; $i++) {
                        $ticketNumbers[] = $currentDbTicket + $i;
                    }

                    Redis::set("tickets:counter:$todaysDate", $next_ticket);
                }
            }  else if(count($cancelled) > 0 && $userIdsCount > count($cancelled))
            {
                $currentDbTicket = ($this->generateNextMultipleTicketNumber($todaysDate))+1;

                \Log::info('cancelled tickets found and user has more ticket requests than cancelled tickets.Ticket will start from '.$currentDbTicket);

                $next_ticket = $currentDbTicket+$userIdsCount-1;

                for ($i = 0; $i < $userIdsCount; $i++) {
                    $ticketNumbers[] = $currentDbTicket + $i;
                }

                Redis::set("tickets:counter:$todaysDate", $next_ticket);
            } else if(count($cancelled) == 0)
            {
               
                $currentDbTicket = ($this->generateNextMultipleTicketNumber($todaysDate))+1;

                 \Log::info('cancelled tickets not found and ticket will start from '.$currentDbTicket);

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
                'generated_by' => Auth::user()->id,
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

        if (in_array(Auth::user()->role, [1, 3]))
        {
            $firstTicket = DB::table('generated_tickets')
            ->where('multiple_id', $randomNumberId)
            ->select('id')
            ->first();

            DB::table('activity_log')->insertGetId([
                'staff_id' => Auth::user()->id,
                'user_id' => $userIdsArray[0],
                'ticket_id' => $firstTicket->id,
                'activity_id' => 2,
                'created_at' => Carbon::now()
            ]);
        }

        // Broadcast event
        event(new ReloadTicketsTable(generatedTicket::get_new_tickets($randomNumberId)));

        return [
            'id' => $randomNumberId,
            'ticket_numbers' => $ticketNumbers
        ];
    }

    
    protected function userHasTicketToday($userId, $date)
    {
        return DB::table('generated_tickets')
            ->whereDate('created_at', $date)
            ->where(['user_id'=>$userId,'is_cancelled'=>0,'status'=>1])
            ->exists();
    }

    protected function ticketIsactiveToday($ticket, $date)
    {
        return DB::table('generated_tickets')
            ->whereDate('created_at', $date)
            ->where(['ticket_number'=>$ticket,'is_cancelled'=>0,'status'=>1])
            ->exists();
    }

    // protected function generateNextTicketNumber($date)
    // {
    //     $lockToken = uniqid();

    //     [$nextTicket, $currentTicket] = Redis::eval(
    //         $TICKET_LUA_WITH_CURRENT,
    //         4,
    //         "tickets:counter:$date",
    //         "tickets:issued:$date",
    //         "tickets:cancelled:$date",
    //         "tickets:max_numbers",
    //        $date,
    //         $lockToken
    //     );
    // }

    protected function generateNextTicketNumber($date)
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

    protected function generateNextMultipleTicketNumber($date)
    {
        $lockToken = Str::uuid()->toString();

        return (int) Redis::eval(
            self::TICKET_LUA_GET_CURRENT,
            3, // Number of Redis KEYS
            "tickets:counter:$date",
            "tickets:issued:$date",
            "tickets:max_numbers",
            now()->format('Y-m-d'),
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
            $savedTicketDetails->update(['generated_by' => Auth::user()->id]);
        }

        // Handle bot flag
        if ($request->session()->pull('is_bot', false)) {
            $savedTicketDetails->update(['is_bot' => 1]);

            session()->forget(['is_bot']);
        }

        if (in_array(Auth::user()->role, [1, 3]))
        {
            DB::table('activity_log')->insertGetId([
                'staff_id' => Auth::user()->id,
                'user_id' => $userId,
                'ticket_id' => $ticketId,
                'activity_id' => 1,
                'created_at' => Carbon::now()
            ]);
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