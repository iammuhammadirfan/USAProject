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

class TicketService
{
    protected $redis;
    
    const TICKET_LUA = <<<'LUA'
        local counterKey = KEYS[1]      -- "tickets:counter:date"
        local issuedKey = KEYS[2]       -- "tickets:issued:date"
        local maxNumbersKey = KEYS[3]   -- "tickets:max_numbers"
        local today = ARGV[1]           -- Date string
        local lockToken = ARGV[2]       -- Unique identifier
        local proposedNumber = tonumber(ARGV[3]) or nil -- Optional specific number

        -- Try to use proposed number if provided
        if proposedNumber then
            if redis.call('HEXISTS', issuedKey, proposedNumber) == 0 then
                redis.call('HSET', issuedKey, proposedNumber, lockToken)
                -- Update counter if needed
                local current = tonumber(redis.call('GET', counterKey)) or 0
                if proposedNumber > current then
                    redis.call('SET', counterKey, proposedNumber)
                    redis.call('HSET', maxNumbersKey, today, proposedNumber)
                end
                return proposedNumber
            end
            -- If proposed number was taken, fall through to increment
        end

        -- Standard increment approach
        local nextNumber = redis.call('INCR', counterKey)
        redis.call('HSET', maxNumbersKey, today, nextNumber)
        
        -- Verify uniqueness
        if redis.call('HEXISTS', issuedKey, nextNumber) == 1 then
            -- If duplicate, get max issued and increment past it
            local maxIssued = tonumber(redis.call('HGET', maxNumbersKey, today)) or nextNumber
            nextNumber = maxIssued + 1
            redis.call('SET', counterKey, nextNumber)
            redis.call('HSET', maxNumbersKey, today, nextNumber)
        end
        
        -- Mark as issued
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

        try {
            DB::beginTransaction();

            if ($request->input('submission_type') == 1) {
                $result = $this->handleMultipleTickets($request, $todaysDate);
            } else {
                $result = $this->handleSingleTicket($request, $todaysDate);
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
                'message' => 'Ticket generation failed'
            ]);
        }
    }

    protected function handleSingleTicket($request, $todaysDate)
    {
        // Check for existing ticket for this user
        if ($this->userHasTicketToday($request->userId, $todaysDate)) {
            return [
                'error' => true,
                'status' => 500,
                'message' => 'User already has a ticket for today'
            ];
        }

        // Try to use cancelled ticket first
        $cancelledTicket = $this->getCancelledTicket($todaysDate);
        $ticketNumber = $this->generateTicketNumber(
            $todaysDate, 
            $cancelledTicket ? $cancelledTicket->ticket_number : null
        );

        if ($cancelledTicket) {
            $this->reactivateCancelledTicket($cancelledTicket->id);
        }

        return $this->createTicket($request->userId, $ticketNumber);
    }

    protected function handleMultipleTickets($request, $todaysDate)
    {
        $userIdsArray = array_unique(json_decode($request->userIdsObject));
        $randomNumberId = mt_rand(1, 1000000000);
        $ticketNumbers = $this->reserveTicketRange($todaysDate, count($userIdsArray));

        $tickets = [];
        foreach ($userIdsArray as $key => $userId) {
            if ($this->userHasTicketToday($userId, $todaysDate)) {
                throw new \Exception("User $userId already has a ticket");
            }

            $tickets[] = [
                'user_id' => $userId,
                'is_first' => $key == 0 ? 1 : 0,
                'is_extra' => $key == 0 ? 0 : 1,
                'multiple_id' => $randomNumberId,
                'ticket_number' => $ticketNumbers[$key],
                'generated_by' => Auth::id(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }

        DB::table('generated_tickets')->insert($tickets);
        $this->broadcastNewTickets($randomNumberId);

        return ['id' => $randomNumberId];
    }

    protected function generateTicketNumber($date, $proposedNumber = null)
    {
        $lockToken = uniqid();
        
        return $this->redis->eval(
            self::TICKET_LUA,
            3,
            "tickets:counter:$date",
            "tickets:issued:$date",
            "tickets:max_numbers",
            $date,
            $lockToken,
            $proposedNumber
        );
    }

    protected function reserveTicketRange($date, $count)
    {
        $numbers = [];
        $firstNumber = $this->generateTicketNumber($date);
        
        for ($i = 0; $i < $count; $i++) {
            $numbers[] = $firstNumber + $i;
        }
        
        // Update counter to last number
        $this->redis->set("tickets:counter:$date", end($numbers));
        
        return $numbers;
    }

    protected function createTicket($userId, $ticketNumber)
    {
        $ticketId = DB::table('generated_tickets')->insertGetId([
            'user_id' => $userId,
            'is_first' => 1,
            'ticket_number' => $ticketNumber,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $this->broadcastNewTickets($ticketId);
        return ['ticket_id' => $ticketId];
    }

    protected function broadcastNewTickets($identifier)
    {
        event(new ReloadTicketsTable(generatedTicket::get_new_tickets($identifier)));
    }

    protected function userHasTicketToday($userId, $date)
    {
        return DB::table('generated_tickets')
            ->whereDate('created_at', $date)
            ->where([
                'status' => 1, 
                'user_id' => $userId, 
                'is_reset' => 0,
                'is_cancelled' => 0
            ])->exists();
    }

    protected function getCancelledTicket($date)
    {
        return DB::table('generated_tickets')
            ->whereDate('created_at', $date)
            ->where(['is_active' => 0, 'status' => 1])
            ->where('is_cancelled', '!=', 0)
            ->first();
    }

    protected function reactivateCancelledTicket($ticketId)
    {
        DB::table('generated_tickets')
            ->where('id', $ticketId)
            ->update([
                'is_active' => 1,
                'is_cancelled' => 1
            ]);
    }

    protected function exceedsTicketLimit($date)
    {
        $loggedinUserRole = Auth::user()->role;

        if ($loggedinUserRole == 2) {
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
        $maxTicketsNotificationMsg = Session::get('notificationMessages')
            ->firstWhere('modal_no', 12);

        return response()->json([
            'status' => 550,
            'message' => $maxTicketsNotificationMsg->message
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