<?php

namespace App\Services;

use DB;
use Auth;
use Session;
use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Events\TicketsAnalytics;

class LoginService
{
    // Cache keys for frequently accessed data
    private const CACHE_KEYS = [
        'TICKET_LIMIT_CHECKER' => 'ticket_limit_checker',
        'NOTIFICATION_MESSAGES' => 'notification_messages',
        'LOGIN_DAYS_TIME' => 'login_days_time',
        'TODAY_TICKETS_COUNT' => 'today_tickets_count',
        'TICKETS_ANALYTICS' => 'tickets_analytics',
        'RETURN_TIMES' => 'return_times',
    ];

    // Cache duration in minutes
    private const CACHE_DURATION = 5;

    /**
     * Get cached ticket limit checker data
     */
    public function getTicketLimitChecker()
    {
        return Cache::remember(self::CACHE_KEYS['TICKET_LIMIT_CHECKER'], self::CACHE_DURATION, function () {
            return DB::table('tickets_management')
                ->select('ticket_limit_status', 'ticket_limit', 'return_times_status')
                ->first();
        });
    }

    /**
     * Get cached notification messages
     */
    public function getNotificationMessages()
    {
        return Cache::remember(self::CACHE_KEYS['NOTIFICATION_MESSAGES'], self::CACHE_DURATION, function () {
            return \App\Models\notificationMessage::where('status', 1)->select('modal_no', 'message')->get();
        });
    }

    /**
     * Get cached login days time data
     */
    public function getLoginDaysTime()
    {
        return Cache::remember(self::CACHE_KEYS['LOGIN_DAYS_TIME'], self::CACHE_DURATION, function () {
            return DB::table('login_days_time')
                ->where(['status' => 1, 'is_active' => 1])
                ->select('day', 'date', 'start_time', 'end_time')
                ->first();
        });
    }

    /**
     * Get cached today's tickets count
     */
    public function getTodayTicketsCount()
    {
        return Cache::remember(self::CACHE_KEYS['TODAY_TICKETS_COUNT'], 1, function () {
            return DB::table('generated_tickets')
                ->where(['is_active' => 1, 'is_cancelled' => 0, 'status' => 1])
                ->whereDate('created_at', Carbon::today())
                ->count();
        });
    }

    /**
     * Get cached ticket analytics
     */
    public function getTicketsAnalytics()
    {
        return Cache::remember(self::CACHE_KEYS['TICKETS_ANALYTICS'], 1, function () {
            $ticketsData = DB::table('generated_tickets')
                ->whereDate('created_at', Carbon::today())
                ->where(['status' => 1, 'is_active' => 1, 'is_cancelled' => 0, 'is_reset' => 0]);

            $allTicketsCount = $ticketsData->count();
            $checkedInTicketsCount = $ticketsData->where('checked_in', 1)->count();
            $notCheckedInTicketsCount = $allTicketsCount - $checkedInTicketsCount;

            return [
                'all_tickets_count' => $allTicketsCount,
                'tickets_served_count' => $checkedInTicketsCount,
                'remaining_tickets_count' => $notCheckedInTicketsCount
            ];
        });
    }

    /**
     * Get cached return times
     */
    public function getReturnTimes()
    {
        return Cache::remember(self::CACHE_KEYS['RETURN_TIMES'], self::CACHE_DURATION, function () {
            return DB::table('ticket_return_times')
                ->where(['is_active' => 1, 'status' => 1])
                ->select('start_ticket', 'end_ticket', 'time')
                ->get();
        });
    }

    /**
     * Get cached day login times
     */
    public function getDayLoginTimes($dayOfTheWeek)
    {
        return Cache::remember("day_login_times_{$dayOfTheWeek}", self::CACHE_DURATION, function () use ($dayOfTheWeek) {
            return DB::table('login_days_time')
                ->where(['is_active' => 1, 'day' => $dayOfTheWeek])
                ->select('start_time', 'end_time')
                ->first();
        });
    }

    /**
     * Get cached login date times
     */
    public function getLoginDateTimes($todaysDate)
    {
        return Cache::remember("login_date_times_{$todaysDate}", self::CACHE_DURATION, function () use ($todaysDate) {
            return DB::table('login_days_time')
                ->where('status', 1)
                ->where(['is_active' => 1, 'date' => $todaysDate])
                ->select('start_time', 'end_time')
                ->first();
        });
    }

    /**
     * Validate user credentials
     */
    public function validateUser($lastName, $caseNumber)
    {
        return User::where(['last_name' => $lastName, 'case_number' => $caseNumber])
            ->select('id', 'last_name', 'case_number', 'role', 'is_active', 'is_warning', 'is_picked_warning')
            ->first();
    }

    /**
     * Check if user has existing ticket for today
     */
    public function getUserExistingTicket($userId, $todaysDate)
    {
        return DB::table('generated_tickets')
            ->where(['user_id' => $userId, 'is_active' => 1, 'status' => 1, 'is_cancelled' => 0])
            ->whereDate('created_at', Carbon::parse($todaysDate))
            ->select('id', 'multiple_id')
            ->first();
    }

    /**
     * Check if current time is within login hours
     */
    public function isWithinLoginHours($currentTime, $dayStartTime, $dayEndTime, $dateStartTime, $dateEndTime)
    {
        return ($currentTime >= $dayStartTime && $currentTime <= $dayEndTime) || 
               ($currentTime >= $dateStartTime && $currentTime <= $dateEndTime);
    }

    /**
     * Log activity asynchronously
     */
    public function logActivity($staffId, $activityId)
    {
        dispatch(function() use ($staffId, $activityId) {
            DB::table('activity_log')->insertGetId([
                'staff_id' => $staffId,
                'activity_id' => $activityId,
                'created_at' => Carbon::now()
            ]);
        })->afterResponse();
    }

    /**
     * Clear relevant caches when data changes
     */
    public function clearCaches()
    {
        Cache::forget(self::CACHE_KEYS['TICKET_LIMIT_CHECKER']);
        Cache::forget(self::CACHE_KEYS['NOTIFICATION_MESSAGES']);
        Cache::forget(self::CACHE_KEYS['LOGIN_DAYS_TIME']);
        Cache::forget(self::CACHE_KEYS['TODAY_TICKETS_COUNT']);
        Cache::forget(self::CACHE_KEYS['TICKETS_ANALYTICS']);
        Cache::forget(self::CACHE_KEYS['RETURN_TIMES']);
    }

    /**
     * Get current time in specified timezone
     */
    public function getCurrentTime($timezone = 'America/Vancouver')
    {
        $dateObj = new DateTime("now", new DateTimeZone($timezone));
        return [
            'time' => $dateObj->format("H:i"),
            'date' => $dateObj->format("Y-m-d"),
            'dayOfWeek' => strtolower(date('l', strtotime($dateObj->format("Y-m-d"))))
        ];
    }

    /**
     * Process ticket analytics event
     */
    public function processTicketAnalytics()
    {
        $ticketsAnalytics = $this->getTicketsAnalytics();
        event(new TicketsAnalytics($ticketsAnalytics));
        return $ticketsAnalytics;
    }
} 