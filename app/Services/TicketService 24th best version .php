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
use App\Models\ticketReturnTime;
use App\Events\CancelledTickets;
use App\Events\TicketsAnalytics;
use App\Events\MaxTicketsReached;
use App\Events\ReloadTicketsTable;
use App\Events\UncheckedInTickets;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TicketService
{
    protected $redis;
    
    const TICKET_NUMBER_LUA = <<<'LUA'
        local key = KEYS[1]
        local today = ARGV[1]
        local lockToken = ARGV[2]

        -- Initialize if not exists
        local currentCounter = tonumber(redis.call('get', key)) or 0

        -- Atomic increment
        local nextNumber = redis.call('incr', key)

        -- Update max number tracking
        redis.call('hset', 'tickets:max_numbers', today, nextNumber)

        -- Verify uniqueness (atomic check)
        local exists = redis.call('hexists', 'tickets:issued:'..today, nextNumber)

        -- When handling duplicates
        if exists == 1 then
            nextNumber = currentCounter + 1  -- Use the already incremented counter
            redis.call('set', key, nextNumber)
            redis.call('hset', 'tickets:max_numbers', today, nextNumber)
        end

        -- Mark as issued
        redis.call('hset', 'tickets:issued:'..today, nextNumber, lockToken)

        return nextNumber
    LUA;

    const CHECK_AND_ISSUE_LUA = <<<'LUA'
        local counterKey = KEYS[1]       -- "tickets:counter:2023-10-25"
        local issuedKey = KEYS[2]        -- "tickets:issued:2023-10-25"
        local maxNumbersKey = KEYS[3]    -- "tickets:max_numbers"
        local ticketNumber = tonumber(ARGV[1]) -- Proposed ticket number
        local lockToken = ARGV[2]        -- Unique identifier
        local today = ARGV[3]            -- Date string

        -- Check if ticket already issued
        if redis.call('HEXISTS', issuedKey, ticketNumber) == 1 then
            return 0 -- Already issued
        end

        -- Mark as issued
        redis.call('HSET', issuedKey, ticketNumber, lockToken)

        -- Update counter if this is higher than current
        local currentCounter = tonumber(redis.call('GET', counterKey)) or 0
        if ticketNumber > currentCounter then
            redis.call('SET', counterKey, ticketNumber)
            redis.call('HSET', maxNumbersKey, today, ticketNumber)
        end

        return 1 -- Successfully issued
    LUA;
        

    public function __construct()
    {
        $this->redis = Redis::connection();
    }

    public function generateTickets($request)
    {
        // Initial setup and validation
        $submissionType = $request->input('submission_type');
        $startTime = microtime(true);
        $dateObj = new DateTime("now", new DateTimeZone("America/Vancouver"));
        $todaysDate = $dateObj->format("Y-m-d");
        $dateCreated = Carbon::now()->toDateTimeString();
        
        // Check ticket limits
        if ($this->exceedsTicketLimit($todaysDate)) {
            return $this->limitExceededResponse();
        }

        // Redis setup
        $lockToken = null;
        $type=0;
        $redis = Redis::connection();
        $lockKey = "ticket_gen_lock:" . ($submissionType == 1 ? 'multi' : $request->userId);

        $rediskey = "tickets:counter:$todaysDate";
        $redis->watch($rediskey);

        $lockToken = $this->acquireLock($redis,$lockKey,$type);
        
        // \Log::info('i am the lock token '.$lockToken);

        DB::beginTransaction();
        try {

            if ($submissionType == 1) {
                $result = $this->handleMultipleTickets($request, $redis, $todaysDate,$lockKey);
            } else if ($submissionType == 0) 
            {
                $result = $this->handleSingleTicket($request, $redis, $todaysDate,$lockKey);
            } 
            else if ($submissionType == 3) 
            {
                $result = $this->resetCurrentRedisTicket($request, $redis, $todaysDate,$lockKey);
            }

            DB::commit();

            $this->logPerformance($startTime, $submissionType);

            \Log::info($result);
            

            $this->updateAnalytics();

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
            
            // \Log::debug('Final Result Structure:', $result);

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
            ], $result['status']);

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
        $acquired = $redis->set($lockKey, $lockToken, 'NX', 'EX', 8);
        
        // \Log::info('acquired lock is '.$acquired.' lock token is '.$lockToken);

        if (!$acquired) {
            throw new \Exception('System is busy processing another ticket request');
        }
        
        return $lockToken;
    }

    protected function releaseLock($redis, $lockKey,$lockToken)
    {
        $lockKey = 'ticket_global_lock';
        $currentToken = $redis->get($lockKey);

        // \Log::info('released lock is '.$currentToken.' lock token is '.$lockToken);
        
        if ($currentToken === $lockToken) {
            $redis->del($lockKey);
        }
    }

    protected function resetCurrentRedisTicket($request, $redis, $todaysDate,$lockKey)
    {
        $lockToken = $this->acquireLock($this->redis);

        try {
            // Get next ticket number
            $value = 0;

            // $redis = Redis::connection();
            $todaysDate = now()->format('Y-m-d');
            $ticketNumberKey = "tickets:counter:$todaysDate";

            $redis->set($ticketNumberKey, $value);

            generatedTicket::where(['is_reset'=>0])->update(array('is_reset' => 1));

            $redis->set("tickets:counter:$todaysDate", $value);

            generatedTicket::where(['is_reset'=>0])->update(array('is_reset' => 1));

            $current_set_ticket = (int)$redis->get($ticketNumberKey);

            $redis->del($lockKey);
                
            if ($lockToken) {
                $this->releaseLock($redis,$lockKey,$lockToken);

                \Log::info('Current Ticket is '.$current_set_ticket);

                \Log::info('Lock key token '.$lockToken.' released');
            }

        } finally {
            // Always release the global lock
            $redis->del($lockKey);
        }
    }

    protected function handleSingleTicket($request, $redis, $todaysDate,$lockKey)
    {
        $lockToken = $this->acquireLock($this->redis);

        try {
            // Get next ticket number
            $selectedTicketNumber = $this->getNextTicketNumber($redis, $todaysDate,$lockToken);

            $existingNumber = $redis->get("tickets:counter:$todaysDate");

            $latestTicket = DB::table('generated_tickets')
                ->whereDate('created_at', $todaysDate)
                ->max('ticket_number');

            

            \Log::info($selectedTicketNumber.' is the selected Ticket Number to assign
            and the current ticket number in redis is '.$existingNumber.' 
            and current ticket in db is '.$latestTicket.'
            and user '.$request->userId.' sent the request');


            // \Log::info('Ticket to assign is '.$selectedTicketNumber);
            

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
                
            $cancelledTicket = DB::table('generated_tickets')
            ->whereDate('created_at', $todaysDate)
            ->where(['is_active' => 0, 'status' => 1])
            ->where('is_cancelled', '!=', 0)
            ->first();

            $deletedTickets = DB::table('generated_tickets')
            ->where(['is_active'=>1,'status'=>0])
            ->whereDate('created_at', now()->toDateString()) 
            ->orderBy('ticket_number','ASC')
            ->pluck('ticket_number')
            ->toArray();

            $todaysTickets=DB::table('generated_tickets')
            ->where(['is_active'=>1,'status'=>1])
            ->whereDate('created_at', now()->toDateString()) 
            ->orderBy('ticket_number','ASC')
            ->pluck('ticket_number')
            ->toArray();
        
            $msnTicketsArray = [];
        
            if(count($todaysTickets)>0)
            {
                if(count($deletedTickets)>0)
                {
                    $fullRange = range(min($todaysTickets), max($todaysTickets));
                    $deletedTickets = array_unique($deletedTickets);

                    $msnTicketsArray = array_diff(
                        $fullRange,
                        array_merge($todaysTickets, $deletedTickets)
                    );

                    $msnTicketsArray = array_values($msnTicketsArray);
                } else {
                    $todaysTickets = array_map('intval', $todaysTickets);
                    $fullSequence = range(min($todaysTickets), max($todaysTickets));
                    $missingNumbers = array_diff($fullSequence, $todaysTickets);
                    $msnTicketsArray = array_values($missingNumbers);
                }

                
            }

            if ($cancelledTicket) 
            {
                $ticketNumber = $cancelledTicket->ticket_number;
                
                DB::table('generated_tickets')
                ->where('id', $cancelledTicket->id)
                ->update([
                    'is_active' => 1,
                    'is_cancelled' => 1
                ]);

                $redis->set("tickets:counter:$todaysDate", $selectedTicketNumber - 1);

                \Log::info($ticketNumber.' assigned for user '.$request->userId.'. Cancelled ticket found.');
            } 
            else if (count($msnTicketsArray)>0)
            {
                $latestTicket = DB::table('generated_tickets')
                    ->whereDate('created_at', $todaysDate)
                    ->max('ticket_number');

                $checkSelectedTicketCount = DB::table('generated_tickets')
                ->whereDate('created_at', $todaysDate)
                ->where(['status' => 1, 'ticket_number' => $msnTicketsArray[0], 'is_reset'=>0,'is_cancelled' => 0])
                ->count();

                $ticketNumber = 0;
                $ticketKey = 0;

                $existingNumber = $redis->get("tickets:counter:$todaysDate");

                // foreach ($msnTicketsArray as $key=>$ticket) {
                //     $checkSelectedTicketCount = DB::table('generated_tickets')
                //         ->whereDate('created_at', $todaysDate)
                //         ->where(['status' => 1, 'ticket_number' => $ticket, 'is_reset'=>0,'is_cancelled' => 0])
                //         ->count();

                //     if($checkSelectedTicketCount == 0)
                //     {
                //         $ticketNumber = $ticket;
                //         $ticketKey = $key;
                //         break;
                //     }
                // }

                // unset($msnTicketsArray[$key]);

                // $remainingMissedTickets = $msnTicketsArray;

                // \Log::info(json_encode($remainingMissedTickets).' is the number of remaining tickets');

                // if(count($remainingMissedTickets)>0)
                // {
                //     $redis->set("tickets:counter:$todaysDate", max($remainingMissedTickets));
                // } else {
                //     $redis->set("tickets:counter:$todaysDate", $existingNumber);
                // }

                if($checkSelectedTicketCount>0)
                {
                    $ticketNumber = $existingNumber;

                    \Log::info($ticketNumber.' assigned for user '.$request->userId.'.Missing tickets found are '.json_encode($msnTicketsArray).'. Selected ticket is present in db');

                } else {
                        $ticketNumber = $msnTicketsArray[0];

                        $remainingMissedTickets = array_slice($msnTicketsArray, 1);

                        if(count($remainingMissedTickets)>0)
                        {
                            $redis->set("tickets:counter:$todaysDate", max($remainingMissedTickets));
                        } else {
                            $redis->set("tickets:counter:$todaysDate", $existingNumber);
                        }

                        \Log::info($ticketNumber.' assigned to user '.$request->userId.'.Missing tickets found are '.json_encode($msnTicketsArray));

                        // $redis->set("tickets:counter:$todaysDate", $ticketNumber);
                        
                                // $success = $this->reserveSpecificTicket($redis, $todaysDate, $selectedTicketNumber, $lockToken);
                                // if (!$success) {
                                //     // Fall back to normal incrementing
                                //     $ticketNumber = $existingNumber;

                                //     $redis->set("tickets:counter:$todaysDate", $ticketNumber);

                                //     \Log::info($selectedTicketNumber.' has already been assigned for today.'.$existingNumber.' assigned to user '.$request->userId.'.Missing tickets found are '.json_encode($msnTicketsArray));
                                    
                                // } else {

                                //     $ticketNumber = $selectedTicketNumber;

                                //     $remainingMissedTickets = array_slice($msnTicketsArray, 1);
                                
                                //     if(count($remainingMissedTickets)>0)
                                //     {
                                //         $redis->set("tickets:counter:$todaysDate", max($remainingMissedTickets));
                                //     } else {
                                //         $redis->set("tickets:counter:$todaysDate", $existingNumber);
                                //     }

                                //     \Log::info($selectedTicketNumber.' has not been assigned for today.'.$existingNumber.' assigned to user '.$request->userId.'.Missing tickets found are '.json_encode($msnTicketsArray));
                                // }

                        // if ($this->isTicketIssued($todaysDate,$selectedTicketNumber))
                        // {
                        //     // Ticket already issued
                        //     $ticketNumber = $existingNumber;

                        //     \Log::info("Ticket ".$selectedTicketNumber." has already been issued today .
                        //     ".$ticketNumber." assigned for user ".$request->userId.".
                        //     Missing tickets found are ".json_encode($msnTicketsArray)." .
                        //     Selected ticket not present in db.");
                        // } else {
                        //     // Ticket is available
                        //     $remainingMissedTickets = array_slice($msnTicketsArray, 1);
                        //     $ticketNumber = $selectedTicketNumber;

                        //     \Log::info("Ticket ".$selectedTicketNumber." has not been issued today .
                        //     ".$ticketNumber." assigned for user ".$request->userId.".
                        //     Missing tickets found are ".json_encode($msnTicketsArray)." .
                        //     Selected ticket not present in db.");
                        // }

                        
                }
            } 
            else if (count($msnTicketsArray) == 0)
            {
                $existingNumber = $redis->get("tickets:counter:$todaysDate");

                $latestTicket = DB::table('generated_tickets')
                    ->whereDate('created_at', $todaysDate)
                    ->max('ticket_number');

                

                \Log::info($selectedTicketNumber.' is the selected Ticket Number 
                and the current ticket number in redis is '.$existingNumber.' 
                and current ticket in db is '.$latestTicket.' where there are no missing tickets
                and the user '.$request->userId.' sent the request');

                if ($selectedTicketNumber == 0) 
                {
                    $ticketNumber = 1;

                } elseif ($selectedTicketNumber == $existingNumber) 
                {

                    $checkSelectedTicketCount = DB::table('generated_tickets')
                        ->whereDate('created_at', $todaysDate)
                        ->where(['status' => 1, 'ticket_number' => $selectedTicketNumber, 'is_reset'=>0,'is_cancelled' => 0])
                        ->count();

                        // \Log::info($checkSelectedTicketCount.' , '.$selectedTicketNumber.' , '.$existingNumber);

                    if($checkSelectedTicketCount>0)
                    {
                        $success = $this->reserveSpecificTicket($redis, $todaysDate, $$existingNumber, $lockToken);
                        if (!$success) {
                            // Fall back to normal incrementing
                            $ticketNumber = $existingNumber;

                            $redis->set("tickets:counter:$todaysDate", $ticketNumber);

                            \Log::info($ticketNumber." assigned for user ".$request->userId." and has not been issued today.
                            Missing tickets not found.Selected ticket is not present in db.selected ticket is equal to current ticket in redis");
                            
                        } else {

                            $ticketNumber = $existingNumber +1;

                            $redis->set("tickets:counter:$todaysDate", $ticketNumber);

                           \Log::info($ticketNumber." assigned for user ".$request->userId." and ".$existingNumber." had been issued earlier.
                            Missing tickets not found.Selected ticket is present in db.selected ticket is equal to current ticket in redis");
                        }

                        
                    } else {
                        
                        // $ticketNumber = $selectedTicketNumber;

                        $success = $this->reserveSpecificTicket($redis, $todaysDate, $selectedTicketNumber, $lockToken);
                        if (!$success) {
                            // Fall back to normal incrementing
                            $ticketNumber = $selectedTicketNumber;

                            $redis->set("tickets:counter:$todaysDate", $ticketNumber);

                            \Log::info($selectedTicketNumber.' has not been assigned for today.'.$selectedTicketNumber.' assigned to user '.$request->userId.'.Missing tickets not found');
                            
                        } else {

                            $ticketNumber = $existingNumber;

                            \Log::info($selectedTicketNumber.' has been assigned for today.'.$selectedTicketNumber.' assigned to user '.$request->userId.'.Missing tickets not found');
                        }
                    }

                    $redis->set("tickets:counter:$todaysDate", $ticketNumber);
                    
                    // \Log::info($selectedTicketNumber.' is the selected Ticket Number and it equals the current ticket number in redis '.$existingNumber);

                } elseif ($selectedTicketNumber !== $existingNumber && $selectedTicketNumber !== 0) 
                {
                    // $latestTicket = DB::table('generated_tickets')
                    // ->whereDate('created_at', $todaysDate)
                    // ->max('ticket_number');

                    // $ticketNumber = $latestTicket + 1;

                    if($selectedTicketNumber > $existingNumber && $latestTicket == $existingNumber)
                    {
                        $success = $this->reserveSpecificTicket($redis, $todaysDate, $selectedTicketNumber, $lockToken);
                        if (!$success) {
                            // Fall back to normal incrementing
                            $ticketNumber = $selectedTicketNumber;

                            $redis->set("tickets:counter:$todaysDate", $ticketNumber);

                            \Log::info($selectedTicketNumber.' has not been assigned for today.'.$selectedTicketNumber.' assigned to user '.$request->userId.'.Missing tickets not found');
                            
                        } else {

                            $ticketNumber = $existingNumber+1;

                            $redis->set("tickets:counter:$todaysDate", $ticketNumber);

                            \Log::info($selectedTicketNumber.' has been assigned for today.'.$selectedTicketNumber.' assigned to user '.$request->userId.'.Missing tickets not found');
                        }

                    } else if (($selectedTicketNumber < $existingNumber && $existingNumber > $latestTicket) || 
                    ($selectedTicketNumber < $existingNumber && $existingNumber == $latestTicket))
                    {
                        // $ticketNumber = $existingNumber;
                        $success = $this->reserveSpecificTicket($redis, $todaysDate, $selectedTicketNumber, $lockToken);
                        if (!$success) 
                        {
                            $ticketNumber = $existingNumber;

                            $redis->set("tickets:counter:$todaysDate", $ticketNumber);

                            \Log::info($ticketNumber.' has not been assigned for today.'.$ticketNumber.' assigned to user '.$request->userId.'.Missing tickets not found');
                            
                        } else {

                            $ticketNumber = $existingNumber+1;

                            $redis->set("tickets:counter:$todaysDate", $ticketNumber);

                            \Log::info($selectedTicketNumber.' has been assigned for today.'.$selectedTicketNumber.' assigned to user '.$request->userId.'.Missing tickets not found');
                        }
                    } 
                    // else if ($selectedTicketNumber < $existingNumber && $existingNumber == $latestTicket)
                    // {
                    //     $ticketNumber = $existingNumber;
                    // }

                    // $redis->set("tickets:counter:$todaysDate", $ticketNumber);

                    // \Log::info($ticketNumber.' assigned for user '.$request->userId.' at .Missing tickets not found. Selected ticket not present in db.selected ticket is not equal to current ticket in redis');

                    // \Log::info($selectedTicketNumber.' is the selected Ticket Number and it not equal to the current ticket number in redis '.$existingNumber);

                    // if (in_array($selectedTicketNumber, $todaysTickets)) 
                    // {
                    //     $ticketNumber = $latestTicket + 1;

                    //     // \Log::info($selectedTicketNumber.' ticket exists in table db,next ticket will be '.$ticketNumber.' where the selected Ticket Number and it not equal to the current ticket number in redis '.$existingNumber);

                    // } else {
                    //     $ticketNumber = $selectedTicketNumber;

                    //     // \Log::info($selectedTicketNumber.' ticket doesnt exist in table db , next ticket will be '.$ticketNumber.' where the selected Ticket Number and it not equal to the current ticket number in redis '.$existingNumber);
                    // }
                }

                 

                //  $newTicket = $redis->get("tickets:counter:$todaysDate");

                // \Log::info($ticketNumber.' for user '.$request->userId.'.Existing ticket was '.$existingNumber.'.New ticket will be '.$newTicket);
            } else {

                \Log::info('selected ticket is'.$selectedTicketNumber.' and current ticket is '.$existingNumber);

                $ticketNumber = $selectedTicketNumber;

                $redis->set("tickets:counter:$todaysDate", $ticketNumber);
            }

            // \Log::info($log_details);
            // $ticketTime = $redis->hget('tickets:counter:$todaysDate', $ticketNumber);

            // \Log::info('Ticket issued at '.$ticketTime);
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

                // \Log::info('Lock key token '.$lockToken.' released');
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

    // public function isTicketIssued($date, $ticketNumber)
    // {
    //     $lockToken = uniqid();
    //     $result = Redis::eval(
    //         self::CHECK_AND_ISSUE_LUA,
    //         3, // Number of keys
    //         "tickets:counter:$date",       // KEYS[1]
    //         "tickets:issued:$date",        // KEYS[2]
    //         "tickets:max_numbers",         // KEYS[3]
    //         $ticketNumber,                 // ARGV[1]
    //         $lockToken,                    // ARGV[2]
    //         $date                          // ARGV[3]
    //     );
        
    //     return (bool)$result;
    // }

    // protected function handleMultipleTickets($request, $redis, $todaysDate,$lockKey)
    // {
    //     $userIdsArray = json_decode($request->userIdsObject);
    //     $randomNumberId = mt_rand(1, 1000000000);
    //     $tickets = [];
    //     $ticketNumbers = [];
    //     $projectedReturnTime=[];

    //     $userIdsCount=count($userIdsArray);

    //         $lockToken = $this->acquireLock($this->redis);
        
            
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
    //             Lock token is '.$lockToken.'
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
    //                     $redis->del($lockKey);

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
    //                     Lock token is '.$lockToken.'
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
    //                         Lock token is '.$lockToken.'
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
    //                         Lock token is '.$lockToken.'
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
    //                         Lock token is '.$lockToken.'
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
    //                         Lock token is '.$lockToken.'
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
    //                 Lock token is '.$lockToken.'
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

    //         // $lock_key = ', Am the lock multiple'.$lockKey;

    //         if (isset($lockKey)) $redis->del($lockKey);

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

    protected function getNextTicketNumber($redis, $todaysDate, $lockToken)
    {
        try {
            return $redis->eval(
                self::TICKET_NUMBER_LUA,
                1, // Number of keys
                "tickets:counter:$todaysDate", // KEYS[1]
                $todaysDate, // ARGV[1]
                $lockToken // ARGV[2]
            );
        } catch (\Exception $e) {
            \Log::error("Lua script failed: " . $e->getMessage());
            
            // Fallback to original PHP implementation
            if (!$redis->exists("tickets:counter:$todaysDate")) {
                $maxNumber = DB::table('generated_tickets')
                    ->whereDate('created_at', $todaysDate)
                    ->where(['is_active'=>1,'status'=>1,'is_cancelled'=>0,'is_reset'=>0])
                    ->max('ticket_number') ?? 0;
                $redis->set("tickets:counter:$todaysDate", $maxNumber);
            }
        
            $nextNumber = $redis->incr("tickets:counter:$todaysDate");

            $exists = DB::table('generated_tickets')
                ->whereDate('created_at', $todaysDate)
                ->where(['is_active'=>1,'status'=>1,'is_cancelled'=>0,'is_reset'=>0,'ticket_number'=>$nextNumber])
                ->exists();

            if ($exists) {
                $nextAvailable = DB::table('generated_tickets')
                    ->whereDate('created_at', $todaysDate)
                    ->where(['is_active'=>1,'status'=>1,'is_cancelled'=>0,'is_reset'=>0])
                    ->max('ticket_number') + 1;

                \Log::info('Duplicate detected for ticket '.$nextNumber.': '.$nextAvailable.' is the next ticket in Queue. Lock token is '.$lockToken);
                    
                $redis->set("tickets:counter:$todaysDate", $nextAvailable);
                return $nextAvailable;
            }

            return $nextNumber;
        }
    }

    public function reserveSpecificTicket($redis, $date, $ticketNumber, $lockToken)
    {
        return $redis->eval(
            self::CHECK_AND_ISSUE_LUA,
            3,
            "tickets:counter:$date",
            "tickets:issued:$date",
            "tickets:max_numbers",
            $ticketNumber,
            $lockToken,
            $date
        );
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
        // \Log::info("$type processed in $seconds seconds and $microseconds microseconds");
    }
    
}