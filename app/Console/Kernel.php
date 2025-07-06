<?php

namespace App\Console;

use DB;
use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\generatedTicket;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */

    protected $commands = [
        Commands\resetRedisTicketsCommand::class,
    ];


    protected function schedule(Schedule $schedule): void
    {

                //get login day or date
            $loginDayDate=DB::table('login_days_time')
                ->where(['status'=>1,'is_active'=>1])
                ->select('day','date','start_time','end_time')
                ->first();

            $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));

            $extractedCurrentTime= $dateObj->format("H:i");

            $todaysDate=$dateObj->format("Y-m-d");
            
            $dayOfTheWeek=strtolower(date('l', strtotime($todaysDate)));

            $rawStartTime=str_replace('.', ':', $loginDayDate->start_time);

            $startTimeFormat = Carbon::createFromFormat('g:i A', $rawStartTime)->format('H:i');

            $day=$loginDayDate->day;

            $date=$loginDayDate->date;

            if($extractedCurrentTime == '23:55')
            {
                $schedule->command('app:update-reset-date-command')
                ->when(function () use($todaysDate) {
                    return now()->is($todaysDate);
                })
                ->at('23:55');
            }
            
            
            
            // if(($date !== null && $date == $todaysDate) || ($day !== null && $day == $dayOfTheWeek))
            // {
            //     if($extractedCurrentTime == '23:55')
            //     {
            //         $schedule->command('app:update-reset-date-command')
            //         ->when(function () use($todaysDate) {
            //             return now()->is($todaysDate);
            //         })
            //         ->at('23:55');
            //     }
            // }

            // $schedule->command('app:update-reset-date-command')


            // $schedule->command('app:reset-redis-tickets-command');


            

        $rawStartTime=str_replace('.', ':', $loginDayDate->start_time);

        $rawEndTime=str_replace('.', ':', $loginDayDate->end_time);

        $startTimeFormat = Carbon::createFromFormat('g:i A', $rawStartTime)->format('H:i');

        $endTimeFormat = Carbon::createFromFormat('g:i A', $rawEndTime)->format('H:i');

        $start_time=$startTimeFormat.':00';

        $end_time=$endTimeFormat.':00';

        $today = Carbon::now();

        if($day == $dayOfTheWeek || $date == $todaysDate)
        {
            $todayDateFormat = $today->format('Y-m-d');

            $loginUsersDateTime=$todayDateFormat . ' ' . $start_time;

            $redisResetTime = date('Y-m-d H:i:s', strtotime($loginUsersDateTime) - (5 * 60));

            $resetTime = Carbon::parse($redisResetTime)->format('H:i');

            if($redisResetTime == $extractedCurrentTime)
            {
                $schedule->command('app:reset-redis-tickets-command')
                ->when(function () use($todaysDate) {
                    return now()->is($todaysDate);
                })
                ->at($resetTime);
            }

        }
                        

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
