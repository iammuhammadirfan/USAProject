<?php

namespace App\Console\Commands;

use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\User;
use App\Models\generatedTicket;
use App\Models\ticketManagement;
use App\Events\TicketsAnalytics;
use App\Events\ReloadTables;
use App\Events\TicketUpdated;
use App\Events\ReloadTicketsTable;
use App\Events\UncheckedInTickets;
use App\Events\VolunteerGroupHomesUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class updateResetDateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-reset-date-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('update reset date for todays tickets');

        Log::info('reset todays tickets to default');

        $todaysDate = now()->format('Y-m-d');

        $ticketNumberKey = "tickets:counter:$todaysDate";

        Redis::connection();
        Redis::set($ticketNumberKey, 0);
        
        DB::table('tickets_management')
                ->where('id',1)
                ->update([
                    'ticket_reset_date' => null,
                    'ticket_number' => 0
                ]);

        DB::table('volunteer_group_homes_tickets')->update([
            'is_reset' => 1
        ]);

        $unpickedGoodiesUsersIds=DB::table('generated_tickets')
            ->where(['is_active'=>1,'is_cancelled'=>0,'is_reset'=>0,'checked_in'=>0])
            ->pluck('user_id')->toArray();

        DB::table('activity_log')
        ->where('is_reset', 0)
        ->update([
            'is_reset' => 1,
            'updated_at' => now()
        ]);

        // $today_activities=DB::table('activity_log')
        //     ->where(['is_reset'=>0])
        //     ->pluck('id')->toArray();
            
        // foreach($today_activities as $activityIdKey => $activityId)
        // {
        //     DB::table('activity_log')
        //     ->where('id',$activityId)
        //     ->update([
        //         'is_reset'=>1
        //     ]);
        // }

        $dateObject= new DateTime("now", new DateTimeZone("America/Vancouver"));

        $todaysDate=$dateObject->format("Y-m-d");

        $date_created=Carbon::now()->toDateTimeString();

        foreach($unpickedGoodiesUsersIds as $userIdKey => $userId)
        {
            $user_details = User::find($unpickedGoodiesUsersIds[$userIdKey]);

            if($user_details->is_picked_warning == '0' && $user_details->role == '2')
            {
                $user_details->update([
                    'is_picked_warning'=>1
                ]);
            } else if ($user_details->is_picked_warning == '1')
            {
                $user_details->update([
                    'is_picked_warning'=>2,
                    'is_active'=>0
                ]);

                DB::table('ticket_users_management')->insert([
                    'user_id'=>$unpickedGoodiesUsersIds[$userIdKey],
                    'date'=>$todaysDate,
                    'created_at'=>$date_created,
                    'updated_at'=>$date_created
                ]);
            }
        }

        generatedTicket::where(['is_reset'=>0])->update(array('is_reset' => 1));

        $ticketsAnalyticsData=generatedTicket::tickets_analytics();

        event(new TicketsAnalytics($ticketsAnalyticsData));

        $ticketsTableReloadData=generatedTicket::reload_tickets();

        event(new ReloadTables($ticketsTableReloadData));

        $count=0;

        event(new TicketUpdated($count));
    }
}
