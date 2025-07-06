<?php

namespace App\Models;

use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\ticketManagement;
use App\Events\TicketsAnalytics;
use App\Events\ReloadTables;
use App\Events\ReloadTicketsTable;
use App\Events\UncheckedInTickets;
use App\Events\VolunteerGroupHomesUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Ticket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class generatedTicket extends Model
{
    use HasFactory;

    protected $table = 'generated_tickets';
    protected $fillable = ['user_id','multiple_id','is_bot','generated_by','ticket_number','status','date','is_active','projected_return_time','checked_in','is_served','is_extra','is_first','created_at','updated_at'];


    public static function ticket_served()
    {
        $currentTicket=ticketManagement::first();
        if($currentTicket)
        {
            return response()->json([
                'ticket_number'=>$currentTicket->ticket_number
            ]);

        } else {
            return response()->json([
                'ticket_number'=>0
            ]);

        }
    }

    public static function tickets_analytics(){

        $ticketsData=DB::table('generated_tickets')
            ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'is_reset'=>0]);
    
        $ticketBeingServed = DB::table('tickets_management')
        ->pluck('ticket_number')->first();

        $notCheckedInTicketsCount=count(DB::table('generated_tickets')
            ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'is_reset'=>0,'checked_in'=>0])->get());
    
            $allTicketsCount=count($ticketsData->get());
    
            $checkedInTicketsCount=count($ticketsData->where('generated_tickets.checked_in','=',1)->get());

            $total_volunteer_grouphomes_signups=DB::table('volunteer_group_homes_tickets')->where(['status'=>1,'is_reset'=>0])->sum('amount');
            
            $total_volunteer_grouphomes_served=DB::table('volunteer_group_homes_tickets')->where(['status'=>1,'is_served'=>1,'is_reset'=>0])->sum('amount');

            $total_volunteer_grouphomes_unserved=DB::table('volunteer_group_homes_tickets')->where(['status'=>1,'is_served'=>0,'is_reset'=>0])->sum('amount');
    
            if($ticketBeingServed == 0)
            {
                $currentNoShows = 0;
                // DB::table('generated_tickets')
                // ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'is_reset'=>0,'checked_in'=>0])
                // ->count();
        
                $currentTicketsRemaining = $allTicketsCount;
            } else {
                $currentNoShows = DB::table('generated_tickets')
                ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'is_reset'=>0,'checked_in'=>0])
                ->where('ticket_number','<=',$ticketBeingServed)
                ->count();

                if($ticketBeingServed > $allTicketsCount)
                {
                    $currentTicketsRemaining = 0;
                } else {
                    $currentTicketsRemaining = DB::table('generated_tickets')
                    ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'is_reset'=>0,'checked_in'=>0])
                    ->where('ticket_number','>',$ticketBeingServed)
                    ->count();
                }
            }

            $ticketsAnalytics=array();
    
            $ticketsAnalytics=[
                'all_tickets_count'=>$allTicketsCount,
                'totalCurrentNoShows'=>$currentNoShows,
                'currentTicketsRemaining'=>$currentTicketsRemaining,
                'tickets_served_count'=>$checkedInTicketsCount,
                // 'remaining_tickets_count'=>$notCheckedInTicketsCount,
                'total_volunteer_grouphomes_served'=>$total_volunteer_grouphomes_served,
                'total_volunteer_grouphomes_signups'=>$total_volunteer_grouphomes_signups,
                'total_volunteer_grouphomes_unserved'=>$total_volunteer_grouphomes_unserved,
                'total_overall_signups'=>($total_volunteer_grouphomes_signups)+($allTicketsCount),
                'total_overall_served'=>($total_volunteer_grouphomes_served)+($checkedInTicketsCount),
                'total_overall_unserved'=>($total_volunteer_grouphomes_unserved)+($notCheckedInTicketsCount)
            ];
    
            return $ticketsAnalytics;
    }

    public static function not_checkedin_tickets($id){

        $checkedInStatusTicketData=DB::table('generated_tickets')
            ->leftJoin('users','generated_tickets.user_id','=','users.id')
            ->where('generated_tickets.id',$id)
            ->select(
                'generated_tickets.ticket_number',
                'generated_tickets.projected_return_time',
                'generated_tickets.created_at',
                'generated_tickets.checked_in')
            ->selectRaw('users.first_name as firstName')
            ->selectRaw('users.last_name as lastName')
            ->selectRaw('users.case_number as caseNumber')
            ->orderBy('generated_tickets.ticket_number','asc')
            ->first();
    
            return $checkedInStatusTicketData;
    }

    public static function get_new_tickets($id)
    {
        $addedMultipleTicketsData=DB::table('generated_tickets')
            ->leftJoin('users as ticketGeneratorUser','generated_tickets.user_id','=','ticketGeneratorUser.id')
			->leftJoin('users as generatedByRole','generated_tickets.generated_by','=','generatedByRole.id')
            ->where('generated_tickets.multiple_id', $id)
			->where('generated_tickets.status',1)
			->where('generated_tickets.is_active',1)
			->where('generated_tickets.is_cancelled',0)
			->where('generated_tickets.is_reset',0)
            ->select(
                'generated_tickets.id',
				'generated_tickets.ticket_number',
				'generated_tickets.generated_by',
				'generated_tickets.created_at',
				'generated_tickets.checked_in',
				'generated_tickets.is_extra',
				'generated_tickets.is_first',
                'generated_tickets.is_bot', 
				'generated_tickets.multiple_id',
				'generated_tickets.status')
             ->selectRaw('ticketGeneratorUser.first_name as firstName')
			->selectRaw('ticketGeneratorUser.last_name as lastName')
			->selectRaw('ticketGeneratorUser.case_number as caseNumber')
			->selectRaw('ticketGeneratorUser.id as userId')
			->selectRaw('ticketGeneratorUser.role as userRole')
			->selectRaw('generatedByRole.role as ticketGeneratorRole')
            ->selectRaw('generatedByRole.first_name as ticketGeneratorFname')
            ->selectRaw('generatedByRole.last_name as ticketGeneratorLname')
			->orderBy('generated_tickets.ticket_number','ASC')
			->get();

            if(count($addedMultipleTicketsData)>0)
            {
                $availableTicketsData=$addedMultipleTicketsData;

                return $availableTicketsData;

            } else {
                $availableTicketsData=DB::table('generated_tickets')
					->leftJoin('users as ticketGeneratorUser','generated_tickets.user_id','=','ticketGeneratorUser.id')
					->leftJoin('users as generatedByRole','generated_tickets.generated_by','=','generatedByRole.id')
					->where('generated_tickets.id', $id)
					->where('generated_tickets.status',1)
					->where('generated_tickets.is_active',1)
					->where('generated_tickets.is_cancelled',0)
					->where('generated_tickets.is_reset',0)
					->select(
						'generated_tickets.id',
						'generated_tickets.ticket_number',
						'generated_tickets.generated_by',
						'generated_tickets.created_at',
						'generated_tickets.checked_in',
						'generated_tickets.is_extra',
						'generated_tickets.is_first',
						'generated_tickets.multiple_id',
                        'generated_tickets.is_bot',
						'generated_tickets.status')
					 ->selectRaw('ticketGeneratorUser.first_name as firstName')
					->selectRaw('ticketGeneratorUser.last_name as lastName')
					->selectRaw('ticketGeneratorUser.case_number as caseNumber')
					->selectRaw('ticketGeneratorUser.id as userId')
					->selectRaw('ticketGeneratorUser.role as userRole')
					->selectRaw('generatedByRole.role as ticketGeneratorRole')
					->orderBy('generated_tickets.ticket_number','ASC')
					->get();

                return $availableTicketsData;
            }
    }

    public static function remove_cancelled_tickets($id)
    {
        //check if the multiple id exists
        // if the multiple id doesnt exists get the id for the ticket
        $cancelledTickets=DB::table('generated_tickets')
            ->where('multiple_id',$id)
            ->pluck('ticket_number')
            ->toArray();

        if($cancelledTickets)
        {
            $cancelledTicketsData=$cancelledTickets;
        } else {
            $cancelledTicketsData=DB::table('generated_tickets')
            ->where('id',$id)
            ->pluck('ticket_number')
            ->toArray();
        }
    
        return $cancelledTicketsData;
    }

    public static function get_checkedin_client($id)
    {
        $availableTicketsData=DB::table('generated_tickets')
            ->leftJoin('users','generated_tickets.user_id','=','users.id')
            ->where('generated_tickets.status',0)
            ->where('generated_tickets.is_active',1)
            ->where('generated_tickets.is_cancelled',0)
            ->where('generated_tickets.is_reset',0)
            ->select(
                'generated_tickets.id',
                'generated_tickets.ticket_number',
                'generated_tickets.projected_return_time',
                'generated_tickets.created_at',
                'generated_tickets.checked_in',
                'generated_tickets.status')
            ->selectRaw('users.first_name as firstName')
            ->selectRaw('users.last_name as lastName')
            ->selectRaw('users.case_number as caseNumber')
            ->selectRaw('users.id as userId')
            ->orderBy('generated_tickets.ticket_number','asc')
            ->first();
    
            return $availableTicketsData;
    }

    // public static function create_single_ticket($id)
    // {
    //     $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));
    //     // $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));

    //     $todaysDate=$dateObj->format("Y-m-d");

        
    //     $getDeactivatedTicketNumber=DB::table('generated_tickets')
    //                                     ->where(['is_active'=>0,'is_cancelled'=>1])
    //                                     ->whereDate('created_at',$todaysDate)
    //                                     ->pluck('ticket_number')
    //                                     ->first();
                                        
    //     $latestSavedData=generatedTicket::
    //     select('ticket_number','created_at')
    //     ->whereDate('created_at',$todaysDate)
    //     ->orderBy('ticket_number', 'Desc')
    //     ->first();

    //     $ticket=new generatedTicket();
    //     $ticket->user_id=$id;
    //     $ticket->save();
        
    //     if($latestSavedData)
    //     {
    //         $latestSavedDate=$latestSavedData->created_at->format("Y-m-d");

    //         if($todaysDate == $latestSavedDate)
    //         {
    //             if($getDeactivatedTicketNumber)
    //             {
    //                 $ticketNumber=$getDeactivatedTicketNumber;

    //                 $TicketDetails=generatedTicket::where('is_active',0)->first();

    //                 $TicketDetails->update([
    //                     'is_active' => 1,
    //                     'is_cancelled' => 1
    //                 ]);
    //             } else {
    //                 $ticketNumber=$latestSavedData->ticket_number + 1;
    //             }
    //         } else {
    //             if($getDeactivatedTicketNumber)
    //             {
    //                 $ticketNumber=$getDeactivatedTicketNumber;

    //                 $TicketDetails=generatedTicket::where('is_active',0)->first();

    //                 $TicketDetails->update([
    //                     'is_active' => 1,
    //                     'is_cancelled' => 1
    //                 ]);
    //             } else {
    //                 $ticketNumber=$latestSavedData->ticket_number + 1;
    //             }
    //         }

    //     } else {
    //         $ticketNumber=1;
    //     }

    //     $getAllReturnTimes=DB::table('ticket_return_times')
    //     ->where(['is_active'=>1,'status'=>1])
    //     ->select('start_ticket','end_ticket','time',)
    //     ->get();

    //     $projectedReturnTime=0;
    //     foreach($getAllReturnTimes as $key=>$return_time)
    //     {
    //         if($ticketNumber >= $return_time->start_ticket && $ticketNumber <= $return_time->end_ticket)
    //         {
    //             $projectedReturnTime=$return_time->time;
    //         }
    //     }

    //     $savedTicketDetails=generatedTicket::find($ticket->id);

    //     $savedTicketDetails->update([
    //         'projected_return_time' => $projectedReturnTime,
    //         'ticket_number' => $ticketNumber,
    //         'is_first' => 1
    //     ]);

    //     $id=$ticket->id;

    //     $ticketsAnalyticsData=generatedTicket::tickets_analytics();

    //     event(new TicketsAnalytics($ticketsAnalyticsData));

    //     $newTicketsData=generatedTicket::get_new_tickets($id);

    //     event(new ReloadTicketsTable($newTicketsData));
        
    //     // $uncheckedTicketsData=generatedTicket::not_checkedin_tickets();

    //     // event(new UncheckedInTickets($uncheckedTicketsData));

    //     return response()->json([
    //         // 'status'=>200,
    //         'ticket_id'=>$savedTicketDetails->id
    //     ]);
    // }

    public static function update_unpicked_users_data()
    {
        $unpickedGoodiesUsersIds=DB::table('generated_tickets')
            ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'is_reset'=>0,'checked_in'=>0])
            ->pluck('user_id')->toArray();
    }

    public static function reload_tickets()
    {
        $tablesData=DB::table('generated_tickets')
        ->leftJoin('users as ticketGeneratorUser','generated_tickets.user_id','=','ticketGeneratorUser.id')
        ->leftJoin('users as generatedByRole','generated_tickets.generated_by','=','generatedByRole.id')
        ->where('generated_tickets.status',1)
        ->where('generated_tickets.is_active',1)
        ->where('generated_tickets.is_cancelled',0)
        ->where('generated_tickets.is_reset',0)
        ->select(
            'generated_tickets.id',
            'generated_tickets.ticket_number',
            'generated_tickets.generated_by',
            'generated_tickets.created_at',
            'generated_tickets.checked_in',
            'generated_tickets.is_extra',
            'generated_tickets.is_first',
            'generated_tickets.multiple_id',
            'generated_tickets.is_bot',
            'generated_tickets.status')
         ->selectRaw('ticketGeneratorUser.first_name as firstName')
        ->selectRaw('ticketGeneratorUser.last_name as lastName')
        ->selectRaw('ticketGeneratorUser.case_number as caseNumber')
        ->selectRaw('ticketGeneratorUser.id as userId')
        ->selectRaw('ticketGeneratorUser.role as userRole')
        ->selectRaw('generatedByRole.role as ticketGeneratorRole')
        ->orderBy('generated_tickets.ticket_number','ASC')
        ->get();

        return $tablesData;
    }
}
