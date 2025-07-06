<?php
namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use DateTime;
use Session;
use DateTimeZone;
use App\Models\User;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use App\Services\CircuitBreaker;
use App\Models\ticketManagement;
use App\Models\generatedTicket;
use App\Events\CancelledTickets;
use App\Events\TicketsAnalytics;
use App\Events\MaxTicketsReached;
use App\Models\ticketReturnTime;
use App\Events\ReloadTicketsTable;
use App\Events\UncheckedInTickets;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class TicketService
{
    protected $redis;

    public function __construct()
    {
        $this->redis = Redis::connection();
    }

    public function generateTickets($request)
    {
        // Initial setup and validation
        $submissionType = $request->input('submission_type', 0);
        $startTime = microtime(true);
        $dateObj = new DateTime("now", new DateTimeZone("America/Vancouver"));
        $todaysDate = $dateObj->format("Y-m-d");
        $dateCreated = Carbon::now()->toDateTimeString();
        
        // Check ticket limits
        if ($this->exceedsTicketLimit($todaysDate)) {
            return $this->limitExceededResponse();
        }

    //     $lockToken = null;
    // $redis = Redis::connection();
    
    // try {
    //     // Acquire global lock
    //     $lockToken = $this->acquireLock($redis);

        // Redis setup
        $lockToken = null;
        $type=0;
        $redis = Redis::connection();
        $lockKey = "ticket_gen_lock:" . ($submissionType == 1 ? 'multi' : $request->userId);
        $lockToken = $this->acquireLock($redis,$lockKey,$type);
        
        \Log::info('i am the lock token '.$lockToken);
        // Acquire lock
        // if (!$redis->set($lockKey, 1, 'NX', 'EX', 5)) {
        //     return response()->json([
        //         'status' => 429,
        //         'message' => 'Another ticket process is already in progress. Please wait.'
        //     ], 429);
        // }

        DB::beginTransaction();
        try {

            if ($submissionType == 1) {
                $result = $this->handleMultipleTickets($request, $redis, $todaysDate,$lockKey);
            } else {
                $result = $this->handleSingleTicket($request, $redis, $todaysDate,$lockKey);
            }

            DB::commit();

            $this->updateAnalytics();

            $this->logPerformance($startTime, $submissionType);

            \Log::info($result);

            if (isset($result['error'])) 
            {
                // DB::rollBack();
                return response()->json([
                    'status' =>500,
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'status' => 200,
                    'id' => $result['id'] ?? null,
                    'ticket_id' => $result['ticket_id'] ?? null,
                    'message' => 'Ticket Generated Successfully.'
                ]);
            }

            // if (!isset($result['error'])) {
            //     $result['error'] = false;
            //     $result['status'] = $result['status'] ?? 200;
            // }
            
            \Log::debug('Final Result Structure:', $result);
            
            // return response()->json([
            //     'success' => !$result['error'],
            //     'data' => $result,
            //     'message' => $result['error'] ? $result['message'] : 'Success'
            // ], $result['status']);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Ticket generation failed: " . $e->getMessage());

            $redis->del($lockKey);

            if ($lockToken) {
                $this->releaseLock($redis,$lockKey,$lockToken);
            }
        
            return response()->json([
                'status' => $result['status'],
                'message' => $result['message']
                ], $result['status']
            );

            // return response()->json([
            //     'status' => 550,
            //     'message' => 'Another Ticket is being processed.Please Try again after some seconds.'
            // ], 550);
        } finally {
            if ($lockToken) {
                $this->releaseLock($redis,$lockKey,$lockToken);
            }
        }
    }

    protected function exceedsTicketLimit($todaysDate)
    {
        $loggedinUserRole = DB::table('users')
            ->where(['status' => 1, 'is_active' => 1, 'id' => Auth::user()->id])
            ->value('role');

        $ntfcationMsgs=Session::get('notificationMessages');

        $maxTicketsNotificationMsg = $ntfcationMsgs->firstWhere('modal_no', 12);

        if ($loggedinUserRole == 2) 
        {
            $ticketLimitChecker = DB::table('tickets_management')->first();
            $totalTickets = DB::table('generated_tickets')
                ->whereDate('created_at', $todaysDate)
                ->where(['status' => 1, 'is_active' => 1])
                ->count();

            if($ticketLimitChecker->ticket_limit_status == 1 && $totalTickets > $ticketLimitChecker->ticket_limit)
            {
                return response()->json([
                    'status' => 550,
                    'message' => $maxTicketsNotificationMsg->message
                ]);
            }
        }
        return false;
    }
     

    protected function acquireLock($redis)
    {
        $lockKey = 'ticket_global_lock';
        $lockToken = uniqid();
        $acquired = $redis->set($lockKey, $lockToken, 'NX', 'EX', 5);
        
        \Log::info('acquired lock is '.$acquired.' lock token is '.$lockToken);
        
        return $lockToken;
    }

    // protected function releaseLock($redis, $key, $token)
    // {
    //     $script = '
    //         if redis.call("get", KEYS[1]) == ARGV[1] then
    //             return redis.call("del", KEYS[1])
    //         else
    //             return 0
    //         end';

    //     $redis->eval($script, 1, $key, $token);
    // }

    /**
     * Releases a lock only if owned by this instance
     */
    protected function releaseLock($redis, $lockKey,$lockToken)
    {
        $lockKey = 'ticket_global_lock';
        $currentToken = $redis->get($lockKey);

        \Log::info('released lock is '.$currentToken.' lock token is '.$lockToken);
        
        if ($currentToken === $lockToken) {
            $redis->del($lockKey);
        }
    }

    protected function handleSingleTicket($request, $redis, $todaysDate,$lockKey)
    {
        $lockToken = $this->acquireLock($this->redis);

        try {
            // Get next ticket number
            $selectedTicketNumber = $this->getNextTicketNumber($redis, $todaysDate);

            // Check for existing ticket
            $ticketCount = DB::table('generated_tickets')
                ->whereDate('created_at', $todaysDate)
                ->where(['status' => 1, 'user_id' => $request->userId, 'is_reset'=>0,'is_cancelled' => 0])
                ->count();

            if ($ticketCount > 0) {
                $redis->set("tickets:counter:$todaysDate", $selectedTicketNumber - 1);
                $redis->del($lockKey);

                return [
                    'error' => true,
                    'status' => 500,
                    'message' => 'The User has already been issued a ticket for today'
                ];
            }
                
            $deactivatedTicket = DB::table('generated_tickets')
            ->whereDate('created_at', $todaysDate)
            ->where(['is_active' => 0, 'status' => 1])
            ->where('is_cancelled', '!=', 0)
            ->first();

            $todaysTickets=DB::table('generated_tickets')
            ->where(['is_active'=>1,'status'=>1])
            ->whereDate('created_at', now()->toDateString()) 
            ->orderBy('ticket_number','ASC')
            ->pluck('ticket_number')
            ->toArray();
        
            $msnTicketsArray = [];
        
            if(count($todaysTickets)>0)
            {
                $todaysTickets = array_map('intval', $todaysTickets);
                $fullSequence = range(min($todaysTickets), max($todaysTickets));
                $missingNumbers = array_diff($fullSequence, $todaysTickets);
                $msnTicketsArray = array_values($missingNumbers);
            }

            if ($deactivatedTicket) 
            {
                $ticketNumber = $deactivatedTicket->ticket_number;
                
                DB::table('generated_tickets')
                ->where('id', $deactivatedTicket->id)
                ->update([
                    'is_active' => 1,
                    'is_cancelled' => 1
                ]);

                $redis->set("tickets:counter:$todaysDate", $selectedTicketNumber - 1);

                $current_ticket=$redis->get('tickets:counter:$todaysDate');

                \Log::info($ticketNumber.' assigned for user '.$request->userId.'
                Next Ticket in queue is '.$current_ticket);
            } 
            // else {
            //     if($selectedTicketNumber == 0)
            //     {
            //         $ticketNumber=1;
            //     } else {
            //         $ticketNumber=$selectedTicketNumber;
            //     }

            //     $redis->set("tickets:counter:$todaysDate", $selectedTicketNumber);

            //     $current_ticket=$redis->get('tickets:counter:$todaysDate');

            //     \Log::info($ticketNumber.' for user '.$request->userId.' whose lock token is'.$lockToken);
            // }
            else if (count($msnTicketsArray)>0)
            {
                $ticketNumber=$msnTicketsArray[0];

                \Log::info($selectedTicketNumber.' is the current ticket number ');

                $latestTicket = DB::table('generated_tickets')
                    ->whereDate('created_at', $todaysDate)
                    ->max('ticket_number');

                $redis->set("tickets:counter:$todaysDate", 0);
                
                // $redis->set("tickets:counter:$todaysDate", $latestTicket);

                $current_ticket=$redis->get('tickets:counter:$todaysDate');

                \Log::info($ticketNumber.' assigned for user '.$request->userId.',
                skipped tickets are '.json_encode($msnTicketsArray).',
                Next Ticket in queue is '.$current_ticket);
            } 
            else if (count($msnTicketsArray) == 0)
            {

                if($selectedTicketNumber == 0)
                {
                    $ticketNumber=1;
                } else {
                    $ticketNumber=$selectedTicketNumber;
                }

                $redis->set("tickets:counter:$todaysDate", $selectedTicketNumber);
                // $redis->set("tickets:counter:$todaysDate", 0);

                $current_ticket=$redis->get('tickets:counter:$todaysDate');

                \Log::info($ticketNumber.' for user '.$request->userId);
            } 

            // $lock_key = ', Am the lock single'.$lockKey;

            // if (isset($lockKey)) $redis->del($lockKey);

            

            // Create ticket
            $ticketId = DB::table('generated_tickets')->insertGetId([
                'user_id' => $request->userId,
                'is_first' => 1,
                'ticket_number' => $ticketNumber,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            if ($lockToken) {
                $this->releaseLock($redis,$lockKey,$lockToken);

                \Log::info('Lock key token '.$lockToken.' released');
            }

            $savedTicketDetails = generatedTicket::find($ticketId);

            // Handle return times
            if (session('returnTimesStatus') == 1) {
                $projectedReturnTime = collect(session('ticketsReturnTimes'))
                    ->firstWhere(fn($rt) => $ticketNumber >= $rt->start_ticket 
                                        && $ticketNumber <= $rt->end_ticket)?->time ?? 0;
                
                $savedTicketDetails->update(['projected_return_time' => $projectedReturnTime]);
                session()->forget(['returnTimesStatus', 'ticketsReturnTimes']);
            }

            // Handle generated by
            if ($request->generatedBy == 1) {
                $savedTicketDetails->update(['generated_by' => Auth::id()]);
                
            }

            // Handle bot flag
            if ($request->session()->pull('is_bot', false)) {
                $savedTicketDetails->update(['is_bot' => 1]);

                session()->forget(['is_bot']);
            }

            $newTicketsData=generatedTicket::get_new_tickets($ticketId);

            event(new ReloadTicketsTable($newTicketsData));

            return ['ticket_id' => $ticketId];

        } finally {
            // Always release the global lock
            $redis->del($lockKey);
        }
    }

    protected function handleMultipleTickets($request, $redis, $todaysDate,$lockKey)
    {
        $userIdsArray = json_decode($request->userIdsObject);
        $randomNumberId = mt_rand(1, 1000000000);
        $tickets = [];
        $ticketNumbers = [];
        $projectedReturnTime=[];

        $userIdsCount=count($userIdsArray);
            
            // $firstUserId=$userIdsArray[0];

            // $extraSelectedTickets= array_diff($userIdsArray, [$firstUserId]);
            
            // $tickets=array();

            // $newTicketsArray=array();

            // $projectedReturnTime=array();

            // $randomNumberId=mt_rand(1, 1000000000);

            // foreach($userIdsArray as $key=>$userId)
            // {
            //     $ticketId=DB::table('generated_tickets')->insertGetId([
            //         'user_id' => $data['first_name'], 
            //         'last_name' => $data['last_name'],
            //         'created_at' => Carbon::now(),
            //         'updated_at' => Carbon::now()
            //     ]);
            //     $ticket=new generatedTicket();
            //     $ticket->user_id=$userIdsArray[$key];
            //     $ticket->is_first=1;
            //     $ticket->is_reset=0;
            //     $ticket->multiple_id=$randomNumberId;
            //     $ticket->generated_by=Auth::user()->id;
            //     $ticket->save();
            //     $tickets[]=$ticket->id;
            // }

            $lockToken = $this->acquireLock($this->redis);
        
            
            $deactivatedTickets = DB::table('generated_tickets')
                ->whereDate('created_at', $todaysDate)
                ->where(['is_active' => 0, 'status' => 1])
                ->where('is_cancelled', '!=', 0)
                ->orderBy('ticket_number', 'asc')
                ->select('id', 'ticket_number') 
                ->get();

            $getDeactivatedTicketNumbers = $deactivatedTickets->pluck('ticket_number')->toArray();
            $getDeactivatedIds = $deactivatedTickets->pluck('id')->toArray();
            
            $latestSavedTicket=DB::table('generated_tickets')
            ->whereDate('created_at',$todaysDate)
            ->select('ticket_number','created_at')
            ->orderBy('ticket_number', 'desc')
            ->first();

            $latestSavedDate=Carbon::parse($latestSavedTicket->created_at)->format('Y-m-d');

            if($todaysDate == $latestSavedDate)
            {
                $selectedTicketNumber = $this->getNextTicketNumber($redis, $todaysDate);

                // \Log::info($new_ticket_counter.' is the Next Ticket in queue after assigning tickets 
                // '.json_encode($ticketNumbers).' when user tickets are equal to deleted tickets');

                foreach($userIdsArray as $userKey => $userId)
                {
                    $ticketCount = DB::table('generated_tickets')
                        ->whereDate('created_at', $todaysDate)
                        ->where([
                            'status' => 1, 
                            'user_id' => $userId, 
                            'is_reset'=>0,
                            'is_cancelled' => 0
                        ])->count();

                    if ($ticketCount > 0) {
                        $redis->set("tickets:counter:$todaysDate", $selectedTicketNumber - 1);
                        $redis->del($lockKey);

                        return [
                            'error' => true,
                            'status' => 450,
                            'message' => 'Some of the user have already been issued a ticket for today'
                        ];
                    }
                }
                
                if(count($getDeactivatedTicketNumbers)>0)
                {
                    
                    if($userIdsCount > count($getDeactivatedTicketNumbers))
                    {
                        // $selectedTicketNumber = $this->getNextTicketNumber($redis, $todaysDate);

                        $currentDbTicket = $selectedTicketNumber;

                        $new_ticket_counter = DB::table('generated_tickets')
                        ->whereDate('created_at', $todaysDate)
                        ->max('ticket_number') + $userIdsCount;

                        for ($i = 0; $i < $userIdsCount; $i++) {
                            $ticketNumbers[] = $currentDbTicket + $i;
                        }

                        $redis->set("tickets:counter:$todaysDate", $new_ticket_counter);

                        $next_ticket=$redis->get('tickets:counter:$todaysDate');
                        \Log::info('Next Ticket in queue is '.$new_ticket_counter.'
                        Users are '.json_encode($userIdsArray).'
                        Ticket Numbers are '.json_encode($ticketNumbers).'
                        All deactivated Tickets are '.json_encode($getDeactivatedTicketNumbers).'
                        Lock token is '.$lockToken.'
                        when user tickets are equal to the number of deleted tickets');

                        

                        // // Get all ticket numbers at once
                        // for ($i = 0; $i < count($userIdsArray); $i++) {
                        //     $ticketNumbers[] = $this->getNextTicketNumber($redis, $todaysDate);
                        // }
                    }

                    if($userIdsCount < count($getDeactivatedTicketNumbers))
                    {
                        // $selectedTicketNumber = $this->getNextTicketNumber($redis, $todaysDate);

                        // $currentDbTicket = $selectedTicketNumber - 1;
                        

                        $array = array_slice($getDeactivatedTicketNumbers, 0, $userIdsCount);

                        $array = array_map('intval', $array);

                        // $tempSequence = [$array[0]];


                        $retaliateCount = $userIdsCount-1;

                        for ($i = 0; $i < count($array) - $retaliateCount; $i++) {
                            // Check if current, next, and next+1 are sequential (+1 each)
                            if ($array[$i + 1] == $array[$i] + 1 && $array[$i + $retaliateCount] == $array[$i] + $retaliateCount) {
                                $tempSequence = [$array[$i], $array[$i + 1], $array[$i + $retaliateCount]];
                                break; // Stop after finding the first sequence
                            }
                        }

                        \Log::info($tempSequence);

                        if($userIdsCount === count($tempSequence))
                        {
                            foreach($tempSequence as $key => $cancelled_ticket)
                            {
                                DB::table('generated_tickets')
                                ->where(['ticket_number'=>$cancelled_ticket,'is_active'=>0,'status' => 1,'is_cancelled'=>1])
                                ->whereDate('created_at', $todaysDate)
                                ->update([
                                    'is_active' => 1,
                                    'is_cancelled' => 1,
                                    'updated_at' => Carbon::now()
                                ]);
                            }

                            $ticketNumbers = $tempSequence;

                            $new_ticket_counter = DB::table('generated_tickets')
                            ->whereDate('created_at', $todaysDate)
                            ->max('ticket_number');

                            $redis->set("tickets:counter:$todaysDate", $new_ticket_counter);

                            $next_ticket=$redis->get('tickets:counter:$todaysDate');

                            \Log::info('Next Ticket in queue is '.$new_ticket_counter.'
                            Users are '.json_encode($userIdsArray).'
                            Ticket Numbers are '.json_encode($ticketNumbers).'
                            All deactivated Tickets are '.json_encode($getDeactivatedTicketNumbers).'
                            Lock token is '.$lockToken.'
                            when user tickets are equal to first equal number of '.$userIdsCount.' from the deleted tickets');

                            // for ($i = 0; $i < $userIdsCount; $i++) {
                            //     $ticketNumbers[] = $currentDbTicket + $i;
                            // }

                        } else if ($userIdsCount > count($tempSequence))
                        {
                            $currentDbTicket = DB::table('generated_tickets')
                            ->whereDate('created_at', $todaysDate)
                            ->max('ticket_number') + 1;

                            for ($i = 0; $i < $userIdsCount; $i++) {
                                $ticketNumbers[] = $currentDbTicket + $i;
                            }

                            $new_ticket_counter = DB::table('generated_tickets')
                            ->whereDate('created_at', $todaysDate)
                            ->max('ticket_number') + $userIdsCount;

                            $redis->set("tickets:counter:$todaysDate", $new_ticket_counter);

                            $next_ticket=$redis->get('tickets:counter:$todaysDate');

                            \Log::info('Next Ticket in queue is '.$new_ticket_counter.'
                            Users are '.json_encode($userIdsArray).'
                            Ticket Numbers are '.json_encode($ticketNumbers).'
                            All deactivated Tickets are '.json_encode($getDeactivatedTicketNumbers).'
                            Lock token is '.$lockToken.'
                            when user tickets are greater than the to first number of '.$userIdsCount.' from the deleted tickets');
                        } else if ($userIdsCount < count($tempSequence))
                        {

                            $ticketNumbers = array_slice($tempSequence, 0, $userIdsCount);

                            $new_ticket_counter = DB::table('generated_tickets')
                            ->whereDate('created_at', $todaysDate)
                            ->max('ticket_number');

                            $redis->set("tickets:counter:$todaysDate", $new_ticket_counter);

                            $next_ticket=$redis->get('tickets:counter:$todaysDate');

                            \Log::info('Next Ticket in queue is '.$new_ticket_counter.'
                            Users are '.json_encode($userIdsArray).'
                            Ticket Numbers are '.json_encode($ticketNumbers).'
                            All deactivated Tickets are '.json_encode($getDeactivatedTicketNumbers).'
                            Lock token is '.$lockToken.'
                            when user tickets are less than the to first number of '.$userIdsCount.' from the deleted tickets');
                        }
                    }

                    if($userIdsCount == count($getDeactivatedTicketNumbers))
                    {
                        $currentDbTicket = DB::table('generated_tickets')
                        ->whereDate('created_at', $todaysDate)
                        ->max('ticket_number') + 1;

                        for ($i = 0; $i < $userIdsCount; $i++) {
                            $ticketNumbers[] = $currentDbTicket + $i;
                        }

                        $new_ticket_counter = $currentDbTicket + $userIdsCount;

                        $redis->set("tickets:counter:$todaysDate", $new_ticket_counter);

                        $next_ticket=$redis->get('tickets:counter:$todaysDate');

                        \Log::info('Next Ticket in queue is '.$new_ticket_counter.'
                            Users are '.json_encode($userIdsArray).'
                            Ticket Numbers are '.json_encode($ticketNumbers).'
                            All deactivated Tickets are '.json_encode($getDeactivatedTicketNumbers).'
                            Lock token is '.$lockToken.'
                            when user tickets are equal to the number of deleted tickets');
                    }
                    
                } 
                else 
                {
                    if($selectedTicketNumber == 0)
                    {
                        $currentDbTicket = 1;
                    } else {
                        $currentDbTicket = $selectedTicketNumber;
                    }

                    $new_ticket_counter = $currentDbTicket + $userIdsCount - 1;

                    $redis->set("tickets:counter:$todaysDate", $new_ticket_counter);

                    $next_ticket=$redis->get('tickets:counter:$todaysDate');

                    for ($i = 0; $i < $userIdsCount; $i++) {
                        $ticketNumbers[] =  $currentDbTicket + $i;
                    }

                    \Log::info('Next Ticket in queue is '.$new_ticket_counter.'
                    Users are '.json_encode($userIdsArray).'
                    Ticket Numbers are '.json_encode($ticketNumbers).'
                    Lock token is '.$lockToken.'
                    when user tickets are not equal to the number of deleted tickets');

                }

                foreach ($userIdsArray as $key => $userId) 
                {

                    $tickets[] = [
                        'user_id' => $userId,
                        'is_first' => $key == 0 ? 1 : 0,
                        'is_extra' => $key == 0 ? 0 : 1,
                        'multiple_id' => $randomNumberId,
                        'ticket_number' => $ticketNumbers[$key],
                        'generated_by' => Auth::user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                }
            }

            $lock_key = ', Am the lock multiple'.$lockKey;

            if (isset($lockKey)) $redis->del($lockKey);

        DB::table('generated_tickets')->insert($tickets);

        if (session('returnTimesStatus') == 1) {
            $projectedReturnTime = collect(session('ticketsReturnTimes', []))
                ->first(function ($returnTime) use ($ticketNumbers) {
                    return $ticketNumbers[0] >= $returnTime->start_ticket 
                        && $ticketNumbers[0] <= $returnTime->end_ticket;
                })?->time ?? 0;
        
            if ($projectedReturnTime) {
                DB::table('generated_tickets')
                    ->where('multiple_id', $randomNumberId)
                    ->update(['projected_return_time' => $projectedReturnTime]);
            }

            session()->forget(['returnTimesStatus', 'ticketsReturnTimes']);
        }

        $newTicketsData=generatedTicket::get_new_tickets($randomNumberId);

        event(new ReloadTicketsTable($newTicketsData));

        return ['id' => $randomNumberId];
    }

    protected function findNextAvailableNumber($todaysDate)
    {
        // Get all existing numbers for today
        $existingNumbers = DB::table('generated_tickets')
            ->whereDate('created_at', $todaysDate)
            ->pluck('ticket_number')
            ->toArray();
        
        if (empty($existingNumbers)) {
            return 1;
        }
        
        // Find the first gap
        sort($existingNumbers);
        $lastNumber = 0;
        foreach ($existingNumbers as $num) {
            if ($num > $lastNumber + 1) {
                return $lastNumber + 1;
            }
            $lastNumber = $num;
        }
        
        return $lastNumber + 1;
    }

    // protected function getNextTicketNumber($redis, $todaysDate)
    // {
    //     $ticketNumberKey = "tickets:counter:$todaysDate";
    
    //     // Use Lua script for atomic operations
    //     $lua = <<<LUA
    //         -- Initialize if not exists
    //         if redis.call('exists', KEYS[1]) == 0 then
    //             local maxNumber = tonumber(redis.call('hget', 'tickets:max_numbers', KEYS[1])) or 0
    //             redis.call('set', KEYS[1], maxNumber)
    //         end
            
    //         -- Increment and get next number
    //         local nextNumber = redis.call('incr', KEYS[1])
            
    //         -- Verify uniqueness (simplified for example)
    //         return nextNumber
    //     LUA;
        
    //     $nextNumber = $redis->eval($lua, 1, $ticketNumberKey);
        
    //     // Double-check uniqueness in database
    //     $exists = DB::table('generated_tickets')
    //         ->whereDate('created_at', $todaysDate)
    //         ->where('ticket_number', $nextNumber)
    //         ->exists();

    //     if ($exists) {
    //         // If duplicate, find next gap
    //         $nextAvailable = $this->findNextAvailableNumber($todaysDate);
    //         $redis->set($ticketNumberKey, $nextAvailable);
    //         return $nextAvailable;
    //     }

    //     // Update the max number in Redis
    //     $redis->hset('tickets:max_numbers', $todaysDate, $nextNumber);
        
    //     return $nextNumber;
        
    //                     // // Initialize counter if not exists
    //                     // if (!$redis->exists($ticketNumberKey)) {
    //                     //     $maxNumber = DB::table('generated_tickets')
    //                     //         ->whereDate('created_at', $todaysDate)
    //                     //         ->max('ticket_number') ?? 0;
    //                     //     $redis->set($ticketNumberKey, $maxNumber);
    //                     // }

    //                     // // Atomic increment
    //                     // $nextNumber = $redis->incr($ticketNumberKey);

    //                     // \Log::info("No duplicate Ticket number is $nextNumber");

    //                     // // Verify uniqueness
    //                     // $exists = DB::table('generated_tickets')
    //                     //     ->whereDate('created_at', $todaysDate)
    //                     //     ->where('ticket_number', $nextNumber)
    //                     //     ->exists();

    //                     // if ($exists) {
    //                     //     // If duplicate, find next available number
    //                     //     $nextAvailable = DB::table('generated_tickets')
    //                     //         ->whereDate('created_at', $todaysDate)
    //                     //         ->max('ticket_number') + 1;

    //                     //     \Log::info("Duplicate found ticket number is $nextAvailable");
                            
    //                     //     $redis->set($ticketNumberKey, $nextAvailable);
    //                     //     return $nextAvailable;
    //                     // }

    //                     // return $nextNumber;
    // }

    protected function getNextTicketNumber($redis, $todaysDate)
    {
        $ticketNumberKey = "tickets:counter:$todaysDate";

        // // Properly formatted Lua script with safe parameter passing
        // $lua = <<<'LUA'
        // -- Initialize if not exists
        // if redis.call("EXISTS", KEYS[1]) == 0 then
        // local max = tonumber(redis.call("HGET", "tickets:max_numbers", KEYS[1])) or 0
        // redis.call("SET", KEYS[1], max)
        // end

        // -- Atomic increment
        // local nextNum = redis.call("INCR", KEYS[1])

        // -- Update max number
        // redis.call("HSET", "tickets:max_numbers", KEYS[1], nextNum)

        // return nextNum
        // LUA;

        $ticketNumberKey = "tickets:counter:$todaysDate";
    
        // Initialize counter if not exists
        if (!$redis->exists($ticketNumberKey)) {
            $maxNumber = DB::table('generated_tickets')
                ->whereDate('created_at', $todaysDate)
                ->max('ticket_number') ?? 0;
                
            // PROPER REDIS SET COMMAND
            $redis->set($ticketNumberKey, $maxNumber);
        }
    
        // Atomic increment - returns the new number
        $nextNumber = $redis->incr($ticketNumberKey);

        // Verify uniqueness in database
        $exists = DB::table('generated_tickets')
            ->whereDate('created_at', $todaysDate)
            ->where('ticket_number', $nextNumber)
            ->exists();

        if ($exists) {
            // If duplicate, find next available number
            $nextAvailable = DB::table('generated_tickets')
                ->whereDate('created_at', $todaysDate)
                ->max('ticket_number') + 1;

                \Log::info('Duplicate detected: '.$nextAvailable.' is the next ticket in Queue.
                Lock token is '.$lockToken);
                
            // PROPER REDIS SET COMMAND
            $redis->set($ticketNumberKey, $nextAvailable);
            return $nextAvailable;
        }

        return $nextNumber;
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

        $ticketsAnalyticsData=generatedTicket::tickets_analytics();

        event(new TicketsAnalytics($ticketsAnalyticsData));

        // $newTotalTicketsCount=DB::table('generated_tickets')
        // ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,])
        // ->whereDate('created_at', Carbon::today())
        // ->count();

        // if($newTotalTicketsCount >= $ticketLimitChecker->ticket_limit && $ticketLimitChecker->ticket_limit_status == 1)
        // {
        //     $maxTicketsLimitData=1;
        // } else {
        //     $maxTicketsLimitData=0;
        // }

        // event(new MaxTicketsReached($maxTicketsLimitData));

        // $ticketsAnalyticsData=generatedTicket::tickets_analytics();

        // event(new TicketsAnalytics($ticketsAnalyticsData));
    }

    protected function logPerformance($startTime, $submissionType)
    {
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $seconds = floor($duration);
        $microseconds = round(($duration - $seconds) * 1000000);
        
        $type = $submissionType == 1 ? 'Multiple tickets' : 'Single ticket';
        \Log::info("$type processed in $seconds seconds and $microseconds microseconds");
    }
}