<?php

namespace App\Http\Controllers;

use DateTime;
use DateTimeZone;
use App\Models\generatedTicket;
use App\Http\Controllers\Controller;
use App\Models\notificationMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ticketManagementController extends Controller
{
    public function get_ticket_served()
    {
        $ticketServed=generatedTicket::ticket_served();

        return $ticketServed;
    }

    public function number_served()
    {
        $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));

        $todaysDate=$dateObj->format("M d, Y");

        $formattedDate=$dateObj->format("Y-m-d");

        $parsedDate=Carbon::parse($formattedDate);

        if(Auth::check())
        {
            $userId=Auth::user()->id;
        } else {
            $userId=0;
        }

        $notificationMessages=notificationMessage::where('status',1)->select('modal_no','message')->get();
        
        session()->put('notificationMessages', $notificationMessages);

        $ticketChecker=DB::table('generated_tickets')
        ->where(['user_id'=>$userId,'is_active'=>1,'status'=>1,'is_cancelled'=>0])
        ->whereDate('created_at',$parsedDate)
        ->pluck('ticket_number')
        ->first();

        $currentTicketServed=DB::table('tickets_management')
        ->pluck('ticket_number')
        ->first();

        $ticketNumber=$ticketChecker;

        $extractedCurrentTime= $dateObj->format("H:i");
        
        $dayOfTheWeek=strtolower(date('l', strtotime($todaysDate)));

        $loginDayDate=DB::table('login_days_time')
        ->where(['status'=>1,'is_active'=>1])
        ->select('day','date','start_time','end_time')
        ->first();

        $distributionStartEndTimes=DB::table('tickets_management')
        ->select('d_start_time','d_end_time')
        ->first();

        $day=$loginDayDate->day;

        $date=$loginDayDate->date;

        $d_start_time=$distributionStartEndTimes->d_start_time;

        $d_end_time=$distributionStartEndTimes->d_end_time;

        $today = Carbon::now();

        $distributionDateStartTime = null;

        $distributionDateEndTime = null;

        $nextWeekDistributionStartTime=null;

        // dd($loginDayDate->day,$dayOfTheWeek,$todaysDate);die();

        if($day == $dayOfTheWeek || $loginDayDate->date == $todaysDate)
        {
            $distributionDateStartTime=$today->format('M d, Y') . ' ' . $d_start_time;

            $distributionDateEndTime=$today->format('M d, Y') . ' ' . $d_end_time;
            

            if($day == $dayOfTheWeek)
            {
                $nextDayAtStartTime = $today->next($day)->format('M d, Y') . ' ' . $d_start_time;

                $nextWeekDistributionStartTime = Carbon::parse($nextDayAtStartTime)->toDateTimeString();

            } else {

                $nextWeekDistributionStartTime = '';
            }

        } else {
            // dd('its not friday');die();
            // $previous_issue_date='1970-01-01 00:00:00';

                // $nextDayAtStartTime = $today->next($day)->format('M d, Y') . ' ' . $start_time;

                // $nextDayAtEndTime = $today->next($day)->format('M d, Y') . ' ' . $end_time;

                // $nextDistributionDate = $today->next($day)->format('M d, Y');


                // $nextDistributionAtStartTime = $nextDistributionDate. ' ' . $d_start_time;

                // $nextDistributionAtEndTime = $nextDistributionDate. ' ' . $d_end_time;

                // // $login_date_start_time = Carbon::parse($nextDayAtStartTime)->toDateTimeString();

                // $distributionDateStartTime = Carbon::parse($nextDistributionAtStartTime)->toDateTimeString();

                // $distributionDateEndTime = Carbon::parse($nextDistributionAtEndTime)->toDateTimeString();

                // $nextWeekDistributionStartTime='';

                if($date !== null)
                {

                        $nextDistributionDate = Carbon::parse($date)->format('M d, Y');

                        $nextDistributionAtStartTime = $nextDistributionDate. ' ' . $d_start_time;

                        $nextDistributionAtEndTime = $nextDistributionDate. ' ' . $d_end_time;

                        // $login_date_start_time = Carbon::parse($nextDayAtStartTime)->toDateTimeString();

                        $distributionDateStartTime = Carbon::parse($nextDistributionAtStartTime)->toDateTimeString();

                        $distributionDateEndTime = Carbon::parse($nextDistributionAtEndTime)->toDateTimeString();

                        $nextDistributionDate='';
                    
                } else {
                    $nextDistributionDate = $today->next($day)->format('M d, Y');


                    $nextDistributionAtStartTime = $nextDistributionDate. ' ' . $d_start_time;

                    $nextDistributionAtEndTime = $nextDistributionDate. ' ' . $d_end_time;

                    // $login_date_start_time = Carbon::parse($nextDayAtStartTime)->toDateTimeString();

                    $distributionDateStartTime = Carbon::parse($nextDistributionAtStartTime)->toDateTimeString();

                    $distributionDateEndTime = Carbon::parse($nextDistributionAtEndTime)->toDateTimeString();

                    $nextWeekDistributionStartTime='';
                }
        }

        // dd($distributionDateStartTime,$distributionDateEndTime);die();

        return view('user.now_serving', compact('todaysDate','currentTicketServed','ticketNumber','distributionDateStartTime','distributionDateEndTime','nextWeekDistributionStartTime'));
    }
}
