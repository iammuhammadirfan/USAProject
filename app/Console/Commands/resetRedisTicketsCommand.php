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

class resetRedisTicketsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-redis-tickets-command';

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
        $value = 0;

        $redis = Redis::connection();
        $todaysDate = now()->format('Y-m-d');
        $ticketNumberKey = "tickets:counter:$todaysDate";
    
        $redis->set($ticketNumberKey, $value);
    
        generatedTicket::where(['is_reset'=>0])->update(array('is_reset' => 1));
    
        $ticketsAnalyticsData=generatedTicket::tickets_analytics();
    
        event(new TicketsAnalytics($ticketsAnalyticsData));
    
        $ticketsTableReloadData=generatedTicket::reload_tickets();
    
        event(new ReloadTables($ticketsTableReloadData));
    }
}