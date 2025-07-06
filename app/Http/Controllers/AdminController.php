<?php

namespace App\Http\Controllers\Admin;

use Auth;
use DateTime;
use App\Helpers;
use DateTimeZone;
use Carbon\Carbon;
use Barryvdh\DomPDF\PDF;
Use App\Models\User;
use Illuminate\Support\Str;
use App\Events\TotalTickets;
use Illuminate\Http\Request;
use App\Events\TicketUpdated;
use App\Models\ticketManagement;
use App\Models\generatedTicket;
use App\Events\CancelledTickets;
use App\Events\RemainingTickets;
use App\Events\TicketsAnalytics;
use App\Events\UncheckedInTickets;
use App\Events\VolunteerGroupHomesUpdated;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function index(){

        $notificationMessages=DB::table('notification_messages')->where('status',1)->select('modal_no','message')->get();
        
        session()->put('notificationMessages', $notificationMessages);

        if (session()->has('notificationMessages'))
        {
            session()->forget('notificationMessages');
            session()->put('notificationMessages', $notificationMessages);

        } else {
            session()->put('notificationMessages', $notificationMessages);
        }

        return view('admin.index');
    }

public function view_recipients_screen(Request $request)
{
    return view('admin.view_recipients_screen');
}

public function overview_dashboard(){

    $ticketsData=DB::table('generated_tickets')
        ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'America/Vancouver'=>0]);

    $notCheckedInTicketsCount=count(DB::table('generated_tickets')
        // ->whereDate('created_at', Carbon::today())
        ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'America/Vancouver'=>0,'checked_in'=>0])->get());

        $allTicketsCount=count($ticketsData->get());

        $checkedInTicketsCount=count($ticketsData->where('generated_tickets.checked_in','=',1)->get());

        $volunteer_served_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>1,'status'=>1,'is_served'=>1,'America/Vancouver'=>0])->sum('amount');
        $group_homes_served_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>2,'status'=>1,'is_served'=>1,'America/Vancouver'=>0])->sum('amount');

        $volunteer_unserved_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>1,'status'=>1,'is_served'=>0,'America/Vancouver'=>0])->sum('amount');
        $group_homes_unserved_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>2,'status'=>1,'is_served'=>0,'America/Vancouver'=>0])->sum('amount');

        $totalVolunteerGroupHomeServed=$volunteer_served_count+$group_homes_served_count;

        $totalVolunteerGroupHomeunServed=$volunteer_unserved_count+$group_homes_unserved_count;

        $totalVolunteerGroupHomeSignups=$totalVolunteerGroupHomeServed+$totalVolunteerGroupHomeunServed;


    return view('admin.overview_dashboard',compact('allTicketsCount','checkedInTicketsCount','notCheckedInTicketsCount','totalVolunteerGroupHomeSignups','totalVolunteerGroupHomeunServed','totalVolunteerGroupHomeServed'));
}

public function general_dashboard(){

    $ticketsData=DB::table('generated_tickets')
        ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'America/Vancouver'=>0]);

    $notCheckedInTicketsCount=count(DB::table('generated_tickets')
        // ->whereDate('created_at', Carbon::today())
        ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'America/Vancouver'=>0,'checked_in'=>0])->get());

        $allTicketsCount=count($ticketsData->get());

        $checkedInTicketsCount=count($ticketsData->where('generated_tickets.checked_in','=',1)->get());

    return view('admin.general_dashboard',compact('allTicketsCount','checkedInTicketsCount','notCheckedInTicketsCount'));
}


// get display tickets
public function get_tickets(Request $request){
    $availableTicketsData=DB::table('generated_tickets')
        ->leftJoin('users as ticketGeneratorUser','generated_tickets.user_id','=','ticketGeneratorUser.id')
		 ->leftJoin('users as generatedByRole','generated_tickets.generated_by','=','generatedByRole.id')
        // ->whereDate('generated_tickets.created_at', Carbon::today())
        ->where('generated_tickets.status',1)
        ->where('generated_tickets.is_active',1)
        ->where('generated_tickets.is_cancelled',0)
        ->where('generated_tickets.America/Vancouver',0)
        ->whereNotNull('generated_tickets.ticket_number')
        ->select(
            'generated_tickets.id',
            'generated_tickets.ticket_number',
            //'generated_tickets.projected_return_time',
            'generated_tickets.created_at',
            'generated_tickets.checked_in',
            'generated_tickets.is_extra',
            'generated_tickets.is_first',
			'generated_tickets.multiple_id',
			'generated_tickets.generated_by',
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
        
        $availableTicketsData = $availableTicketsData->unique('ticket_number')->values();

        $availableTicketsData = DataTables::of($availableTicketsData)

            ->addColumn('ticket', function ($row) {
                return $row->ticket_number;
            })
            ->addColumn('fname', function ($row) {
                return 
                '<span>' . $row->firstName .'</span';
            })
            ->addColumn('lname', function ($row) {
                return 
                '<span>' . $row->lastName .'</span';
            })
            ->addColumn('case_number', function ($row) {
                return 
                '<span>' . $row->caseNumber .'</span';
            })
            ->addColumn('created', function ($row) {
                return 
                '<span>' . date('h:i:s A', strtotime($row->created_at)) .'</span';
            })
            ->addColumn('checked_in', function ($row) {

                if($row->checked_in == 1)
                {
                    $btnColor='btn-success';
                    $btnText='Yes';

                } else {
                    $btnColor='btn-danger';
                    $btnText='No';
                }
                
                return  '<a id="checkInButton'.$row->id.'" class="btn btn-sm text-white  '.$btnColor.' rounded-5 check-in-btn" ticket-id="'.$row->id.'">'.$btnText.'</a>';                     
            })
			->addColumn('created_by', function ($row) {
				
				if ($row->is_first == 1 && $row->is_extra == 0 && $row->generated_by !== null && $row->multiple_id !== null && $row->ticketGeneratorRole == 1 && $row->is_bot == 0)
                {
					$roleName='TGOG';
					
                } else if ($row->is_first == 0 && $row->is_extra == 1 && $row->generated_by !== null && $row->multiple_id !== null && $row->ticketGeneratorRole == 1 && $row->is_bot == 0)
				{
                    $roleName='';
					
                } else if ($row->is_first == 1 && $row->is_extra == 0 && $row->generated_by !== null && $row->multiple_id == null && $row->ticketGeneratorRole == 1 && $row->is_bot == 0)
				{
                    $roleName='TGOG';
					
                } else if ($row->is_first == 1 && $row->is_extra == 0 && $row->generated_by == null && $row->multiple_id == null && $row->ticketGeneratorRole == null && $row->is_bot == 0)
				{
					$roleName='Recipient';
					
				} else if ($row->is_first == 1 && $row->is_extra == 0 && $row->generated_by == null && $row->multiple_id == null && $row->ticketGeneratorRole == null && $row->is_bot == 1)
				{
					$roleName='Bot';
					
				} else if ($row->is_first == 1 && $row->is_extra == 0 && $row->generated_by !== null && $row->multiple_id !== null && $row->ticketGeneratorRole == 2 && $row->is_bot == 0)
				{
					$roleName='Recipient';

				} else if ($row->is_first == 1 && $row->is_extra == 0 && $row->generated_by !== null && $row->multiple_id !== null && $row->ticketGeneratorRole == 2 && $row->is_bot == 1)
				{
					$roleName='Bot';

				} else if($row->is_first == 0 && $row->is_extra == 1 && $row->generated_by !== null && $row->multiple_id !== null && $row->ticketGeneratorRole == 2 && $row->is_bot == 0)
                {
					$roleName='';
					
                }
                
                return  '<span>' . $roleName .'</span';                     
            })
			
			->addColumn('action', function ($row) {
				
				if($row->is_first == 1 && $row->multiple_id !== null)
                {
					$printLink='<a href="/view-multiple-users-ticket-size-pdf/'.$row->multiple_id.'/card/" target="_blank" class="text-dark">Print</a>'; 
					$displayStyle='d-block';
                } else if($row->is_first == 1 && $row->multiple_id == null)
				{
                    $printLink='<a href="/view-ticket-size-pdf/'.$row->id.'/card/" target="_blank" class="text-dark">Print</a>';
					$displayStyle='d-block';
                } else if ($row->is_first == 0 && $row->multiple_id !== null)
				{
					$printLink = ''; 
					$displayStyle='d-none';
				}
				
                return '<div class="bg-warning datatable-dropdown">
                        <a class="btn btn-warning btn-sm btn-md btn-lg rounded text-dark withdraw-ticket" ticket-id='.$row->id.'>Remove</a>
                        <div class="dropdown '.$displayStyle.'">
                            <button class="btn dropdown">
                                <i class="fa-solid fa-caret-down"></i>
                            </button>
                            <div class="dropdown-content">'.$printLink.'
                            </div>
                        </div>
                    </div>';
            })

            ->rawColumns(['fname','lname','ticket','case_number','created','checked_in','created_by','action'])
            ->make(true);

			//dd($availableTicketsData);die();
            return $availableTicketsData;
}

public function volunteer_group_signups(Request $request)
{
    $volunteer_signups_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>1,'status'=>1,'America/Vancouver'=>0])->sum('amount');
    $group_homes_signups_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>2,'status'=>1,'America/Vancouver'=>0])->sum('amount');

    $volunteer_served_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>1,'status'=>1,'is_served'=>1,'America/Vancouver'=>0])->sum('amount');
    $group_homes_served_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>2,'status'=>1,'is_served'=>1,'America/Vancouver'=>0])->sum('amount');

    $volunteer_unserved_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>1,'status'=>1,'is_served'=>0,'America/Vancouver'=>0])->sum('amount');
    $group_homes_unserved_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>2,'status'=>1,'is_served'=>0,'America/Vancouver'=>0])->sum('amount');

    return view('admin.view_volunteer_group_signups',compact('volunteer_signups_count','group_homes_signups_count','volunteer_served_count','group_homes_served_count','volunteer_unserved_count','group_homes_unserved_count'));
}

public function volunteer_signups(Request $request)
{
    $volunteerSignups=DB::table('volunteer_group_homes_tickets')
        // ->whereDate('created_at', Carbon::today())
        ->where(['status'=>1,'is_type'=>1,'America/Vancouver'=>0])
        ->select(
            'id',
            'ticket_number',
            'name',
            'amount',
            'is_served')
        ->orderBy('id','ASC')
        ->get();

        $volunteerSignups = DataTables::of($volunteerSignups)

            ->addColumn('ticket_number', function ($row) {
                return $row->ticket_number;
            })
            ->addColumn('name', function ($row) {
                return 
                '<span>' . $row->name .'</span';
            })
            ->addColumn('amount', function ($row) {
                return 
                '<span>' . $row->amount .'</span';
            })
            ->addColumn('served', function ($row) {

                if($row->is_served == 1)
                {
                    $btnColor='btn-success';
                    $btnText='Yes';

                } else {
                    $btnColor='btn-danger';
                    $btnText='No';
                }
                
                return  '<a id="isServedButton'.$row->id.'" class="btn btn-sm text-white  '.$btnColor.' rounded-5 is-served-btn" ticket-id="'.$row->id.'">'.$btnText.'</a>';                     
            })

            ->addColumn('action', function ($row) {

                return '<div class="bg-warning datatable-dropdown">
                    <a class="btn btn btn-sm  text-dark update-volunteer-group-details" volunteer-group-id='.$row->id.' href="#">Edit</a>
                    <div class="dropdown">
                        <button class="btn dropdown">
                            <i class="fa-solid fa-caret-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <a class="dropdown-item text-dark delete-volunteer-group-details" volunteer-group-id='.$row->id.' href="#">Delete</a>
                        </div>
                    </div>
                </div>';
            })

            ->rawColumns(['name','amount','ticket_number','served','action'])
            ->make(true);

			//dd($volunteerSignups);die();
            return $volunteerSignups;
}

public function group_signups(Request $request)
{
    $groupHomeSignupsData=DB::table('volunteer_group_homes_tickets')
        // ->whereDate('created_at', Carbon::today())
        ->where(['status'=>1,'is_type'=>2,'America/Vancouver'=>0])
        ->select(
            'id',
            'ticket_number',
            'name',
            'amount',
            'is_served')
        ->orderBy('id','ASC')
        ->get();

        $groupHomeSignupsData = DataTables::of($groupHomeSignupsData)

            ->addColumn('ticket_number', function ($row) {
                return $row->ticket_number;
            })
            ->addColumn('name', function ($row) {
                return 
                '<span>' . $row->name .'</span';
            })
            ->addColumn('amount', function ($row) {
                return 
                '<span>' . $row->amount .'</span';
            })
            ->addColumn('served', function ($row) {

                if($row->is_served == 1)
                {
                    $btnColor='btn-success';
                    $btnText='Yes';

                } else {
                    $btnColor='btn-danger';
                    $btnText='No';
                }
                
                return  '<a id="isServedButton'.$row->id.'" class="btn btn-sm text-white  '.$btnColor.' rounded-5 is-served-btn" ticket-id="'.$row->id.'">'.$btnText.'</a>';                     
            })
            ->addColumn('action', function ($row) {

                return '<div class="bg-warning datatable-dropdown">
                    <a class="btn btn btn-sm  text-dark update-volunteer-group-details"  volunteer-group-id='.$row->id.' href="#">Edit</a>
                    <div class="dropdown">
                        <button class="btn dropdown">
                            <i class="fa-solid fa-caret-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <a class="dropdown-item text-dark delete-volunteer-group-details" volunteer-group-id='.$row->id.' href="#">Delete</a>
                        </div>
                    </div>
                </div>';
            })

            ->rawColumns(['name','amount','ticket_number','served','action'])
            ->make(true);

			//dd($groupHomeSignupsData);die();
            return $groupHomeSignupsData;
}

public function get_volunteer_group_signup_details($id)
{
    $details=DB::table('volunteer_group_homes_tickets')
    ->where(['id'=>$id,'status'=>1])
    ->select('amount','name')
    ->first();

    if($details)
    {
        return response()->json([
            'status'=>200,
            'data'=>$details
        ]);
    } else {
        return response()->json([
            'status'=>404,
            'message'=>'Details Not Found'
        ]);
    }

}

public function manage_volunteer_group_signup(Request $request)
{
    $data=$request->all();

    $rules = [
        'user_name'=>'required',
        'amount'=>'required',
    ];

    $custommessages = [
        'user_name.required' => 'Name is required',
        'amount.required' => 'Amount is required',
    ];

    $validator = Validator::make($data, $rules, $custommessages);

    if ($validator->fails()) {
        return response()->json([
            'status' => 405,
            'message' => $validator->errors()
        ]);
    } else {

        // $dateObject= new DateTime("now", new DateTimeZone("America/Vancouver"));

        $dateObject= new DateTime("now", new DateTimeZone("America/Vancouver"));

        $todaysDate=$dateObject->format("Y-m-d");

        $date_created=Carbon::now()->toDateTimeString();

        $previousVolunteerTicketToday=DB::table('volunteer_group_homes_tickets')
            ->where(['status'=>1,'is_type'=>1])
            ->whereDate('created_at', Carbon::today())
            ->orderBy('ticket_number','desc')
            ->select('ticket_number')
            ->first();

        $previousGroupHomeTicketToday=DB::table('volunteer_group_homes_tickets')
            ->where(['status'=>1,'is_type'=>2])
            ->whereDate('created_at', Carbon::today())
            ->orderBy('ticket_number','desc')
            ->select('ticket_number')
            ->first();

        // dd($previousVolunteerTicketToday,$previousGroupHomeTicketToday,$data['type_id']);die();

        if($previousGroupHomeTicketToday == null && $data['type_id']=="2")
        {
            $ticket_number=1;
        } else if($previousGroupHomeTicketToday !== null && $data['type_id']=="2"){
            $ticket_number=($previousGroupHomeTicketToday->ticket_number) + 1;
        }

        if($previousVolunteerTicketToday == null && $data['type_id']=="1")
        {
            $ticket_number=1;
        } else if($previousVolunteerTicketToday !== null && $data['type_id']=="1")
        {
            $ticket_number=($previousVolunteerTicketToday->ticket_number) + 1;
        }

        if($data['volunteer_group_id'] == null)
        {
            DB::table('volunteer_group_homes_tickets')->insert([
                'name' => $data['user_name'], 
                'amount' => $data['amount'],
                'is_type' => $data['type_id'],
                'is_served' => 1,
                'ticket_number' => $ticket_number,
                'created_at' => $date_created,
                'updated_at' => $date_created
            ]);

            $msg='Sign up has been registered successfully.';
        } else {
            DB::table('volunteer_group_homes_tickets')
            ->where('id',$data['volunteer_group_id'])
            ->update([
                'name' => $data['user_name'], 
                'amount' => $data['amount'],
                'updated_at' => $date_created
            ]);

            $msg='Sign up has been updated successfully.';
        }

        $volunteer_signups_count=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>1,'status'=>1])->sum('amount');

        $volunteer_signups_served=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>1,'status'=>1,'is_served'=>1])->sum('amount');

        $volunteer_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>1,'status'=>1,'is_served'=>0])->sum('amount');

        

        $group_homes_signups_count=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>2,'status'=>1])->sum('amount');

        $group_homes_signups_served=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>2,'status'=>1,'is_served'=>1])->sum('amount');

        $group_homes_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>2,'status'=>1,'is_served'=>0])->sum('amount');


        $volunteer_group_homes_data=
        [
            'volunteer_signups_count'=>$volunteer_signups_count,
            'volunteer_signups_served'=>$volunteer_signups_served,
            'volunteer_signups_unserved'=>$volunteer_signups_unserved,
            'group_homes_signups_count'=>$group_homes_signups_count,
            'group_homes_signups_served'=>$group_homes_signups_served,
            'group_homes_signups_unserved'=>$group_homes_signups_unserved
        ];

        event(new VolunteerGroupHomesUpdated($volunteer_group_homes_data));

        return response()->json([
            'status' => 200,
            'message' => $msg,
            // 'volunteer_signups_count' => $volunteer_signups_count,
            // 'volunteer_signups_served' => $volunteer_signups_served,
            // 'volunteer_signups_unserved' => $volunteer_signups_unserved,
            // 'group_homes_signups_count' => $group_homes_signups_count,
            // 'group_homes_signups_served' => $group_homes_signups_served,
            // 'group_homes_signups_unserved' => $group_homes_signups_unserved
        ]);
        // return redirect()->route("user.dashboard");
    }
}

public function delete_volunteergroup_signup(Request $request)
{
    $signup_id=$request->signUpId;

    $selectedSignup=DB::table('volunteer_group_homes_tickets')->where(['id'=>$signup_id])->first();

    if($selectedSignup)
    {
        DB::table('volunteer_group_homes_tickets')->where(['id'=>$signup_id])->update
        ([
            'status'=>0
        ]);

        $ticketsAnalyticsData=generatedTicket::tickets_analytics();

        event(new TicketsAnalytics($ticketsAnalyticsData));

        $volunteer_signups_count=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>1,'status'=>1])->sum('amount');

        $volunteer_signups_served=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>1,'status'=>1,'is_served'=>1])->sum('amount');

        $volunteer_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>1,'status'=>1,'is_served'=>0])->sum('amount');

        

        $group_homes_signups_count=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>2,'status'=>1])->sum('amount');

        $group_homes_signups_served=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>2,'status'=>1,'is_served'=>1])->sum('amount');

        $group_homes_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>2,'status'=>1,'is_served'=>0])->sum('amount');


        $volunteer_group_homes_data=
        [
            'volunteer_signups_count'=>$volunteer_signups_count,
            'volunteer_signups_served'=>$volunteer_signups_served,
            'volunteer_signups_unserved'=>$volunteer_signups_unserved,
            'group_homes_signups_count'=>$group_homes_signups_count,
            'group_homes_signups_served'=>$group_homes_signups_served,
            'group_homes_signups_unserved'=>$group_homes_signups_unserved
        ];

        event(new VolunteerGroupHomesUpdated($volunteer_group_homes_data));

        // $volunteer_signups_count=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>1,'status'=>1])->sum('amount');

        // $volunteer_signups_served=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>1,'status'=>1,'is_served'=>1])->sum('amount');

        // $volunteer_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>1,'status'=>1,'is_served'=>0])->sum('amount');

        

        // $group_homes_signups_count=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>2,'status'=>1])->sum('amount');

        // $group_homes_signups_served=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>2,'status'=>1,'is_served'=>1])->sum('amount');

        // $group_homes_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>2,'status'=>1,'is_served'=>0])->sum('amount');

        return response()->json([
            'status' => 200,
            'message' => 'Sign up has been deleted successfully.',
            // 'volunteer_signups_count' => $volunteer_signups_count,
            // 'volunteer_signups_served' => $volunteer_signups_served,
            // 'volunteer_signups_unserved' => $volunteer_signups_unserved,
            // 'group_homes_signups_count' => $group_homes_signups_count,
            // 'group_homes_signups_served' => $group_homes_signups_served,
            // 'group_homes_signups_unserved' => $group_homes_signups_unserved
        ]);

        return response()->json([
            'status'=>200
        ]);
    } else {
        return response()->json([
            'status'=>404,
            'message'=>'Details not Found'
        ]);
    }
}

public function served_in_status(Request $request)
{
    $ticket_id=$request->ticketId;

    $selectedTicket=DB::table('volunteer_group_homes_tickets')->where(['id'=>$ticket_id])->first();
    
    if($selectedTicket->is_served == 1)
    {
        DB::table('volunteer_group_homes_tickets')->where(['id'=>$ticket_id])->update
        ([
            'is_served'=>0
        ]);

        $ticketsAnalyticsData=generatedTicket::tickets_analytics();

        event(new TicketsAnalytics($ticketsAnalyticsData));

        $volunteer_signups_count=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>1,'status'=>1])->sum('amount');

        $volunteer_signups_served=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>1,'status'=>1,'is_served'=>1])->sum('amount');

        $volunteer_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>1,'status'=>1,'is_served'=>0])->sum('amount');

        

        $group_homes_signups_count=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>2,'status'=>1])->sum('amount');

        $group_homes_signups_served=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>2,'status'=>1,'is_served'=>1])->sum('amount');

        $group_homes_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>2,'status'=>1,'is_served'=>0])->sum('amount');


        $volunteer_group_homes_data=
        [
            'volunteer_signups_count'=>$volunteer_signups_count,
            'volunteer_signups_served'=>$volunteer_signups_served,
            'volunteer_signups_unserved'=>$volunteer_signups_unserved,
            'group_homes_signups_count'=>$group_homes_signups_count,
            'group_homes_signups_served'=>$group_homes_signups_served,
            'group_homes_signups_unserved'=>$group_homes_signups_unserved
        ];

        event(new VolunteerGroupHomesUpdated($volunteer_group_homes_data));

        return response()->json([
            'id'=>$ticket_id,
            'is_type'=>$selectedTicket->is_type,
            'amount'=>$selectedTicket->amount
        ]);
    } else if($selectedTicket->is_served == 0)
    {
        DB::table('volunteer_group_homes_tickets')->where(['id'=>$ticket_id])->update
        ([
            'is_served'=>1
        ]);

        $ticketsAnalyticsData=generatedTicket::tickets_analytics();

        event(new TicketsAnalytics($ticketsAnalyticsData));

        $volunteer_signups_count=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>1,'status'=>1])->sum('amount');

        $volunteer_signups_served=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>1,'status'=>1,'is_served'=>1])->sum('amount');

        $volunteer_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>1,'status'=>1,'is_served'=>0])->sum('amount');

        

        $group_homes_signups_count=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>2,'status'=>1])->sum('amount');

        $group_homes_signups_served=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>2,'status'=>1,'is_served'=>1])->sum('amount');

        $group_homes_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['America/Vancouver'=>0,'is_type'=>2,'status'=>1,'is_served'=>0])->sum('amount');


        $volunteer_group_homes_data=
        [
            'volunteer_signups_count'=>$volunteer_signups_count,
            'volunteer_signups_served'=>$volunteer_signups_served,
            'volunteer_signups_unserved'=>$volunteer_signups_unserved,
            'group_homes_signups_count'=>$group_homes_signups_count,
            'group_homes_signups_served'=>$group_homes_signups_served,
            'group_homes_signups_unserved'=>$group_homes_signups_unserved
        ];

        event(new VolunteerGroupHomesUpdated($volunteer_group_homes_data));

        return response()->json([
            'id'=>$ticket_id,
            'is_type'=>$selectedTicket->is_type,
            'amount'=>$selectedTicket->amount
        ]);
    }
}

public function view_signups_table_pdf()
{
    // $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));

    $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));

    $todaysDate=$dateObj->format('m/d/Y h:i:s');

    $currentTicketsData=DB::table('generated_tickets')
        ->leftJoin('users','generated_tickets.user_id','=','users.id')
        // ->whereDate('generated_tickets.created_at', Carbon::today())
        ->where('generated_tickets.status',1)
        ->where('generated_tickets.is_active',1)
        ->where('generated_tickets.is_cancelled',0)
        ->where('generated_tickets.America/Vancouver',0)
        ->select(
            'generated_tickets.id',
            'generated_tickets.ticket_number',
            'generated_tickets.is_extra',
            'generated_tickets.created_at',
            'generated_tickets.is_first',
            'generated_tickets.checked_in',
            'generated_tickets.status')
        ->selectRaw('users.first_name as firstName')
        ->selectRaw('users.last_name as lastName')
        ->selectRaw('users.case_number as caseNumber')
        ->selectRaw('users.id as userId')
        ->orderBy('generated_tickets.ticket_number','ASC')
        ->get();

    $data = [
        'currentTicketsData'=>$currentTicketsData,
        'todaysDate'=>$todaysDate
    ];

    
    $currentTicketsPdf=app()->make(PDF::class);

    $html = view('admin.view_signups_table_pdf', $data)->render();
    $currentTicketsPdf->setPaper(array(0, 0,816,1056), 'portrait');
    $currentTicketsPdf->loadHTML($html); 
    

    return $currentTicketsPdf->stream();
}

public function delete_ticket(Request $request)
{
    $ticket_id=$request->ticketId;

    $selectedTicket=generatedTicket::where(['id'=>$ticket_id])->first();

    if($selectedTicket)
    {
        $selectedTicket->update
        ([
            'status'=>0
        ]);

        $ticketsAnalyticsData=generatedTicket::tickets_analytics();

        event(new TicketsAnalytics($ticketsAnalyticsData));

        // $newTicketsData=generatedTicket::get_new_tickets($id);

        // event(new ReloadTicketsTable($newTicketsData));

        if($selectedTicket->checked_in == 0)
        {
            $cancelledTicketsData=generatedTicket::remove_cancelled_tickets($ticket_id);

            event(new CancelledTickets($cancelledTicketsData));
        }

        return response()->json([
            'status'=>200
        ]);
    } else {
        return response()->json([
            'status'=>404,
            'message'=>'Details not Found'
        ]);
    }
}

// public function check_in_status(Request $request)
// {
//     $ticket_id=$request->checkedInTicketId;

//     $selectedTicket=generatedTicket::where(['id'=>$ticket_id])->first();

//     // check if the ticket belongs to a admin
//     $userId=$selectedTicket->user_id;
//     $ticketsId=$selectedTicket->multiple_id;

//     // $user_role=DB::table('users')->where('id',$userId)->select('role')->first();

//     if($selectedTicket->checked_in == 1)
//     {
//         $selectedTicket->update
//         ([
//             'checked_in'=>0
//         ]);

//         $checkedInTicket=$selectedTicket->id;

//         $ticketsAnalyticsData=generatedTicket::tickets_analytics();

//         event(new TicketsAnalytics($ticketsAnalyticsData));
        
//         $uncheckedTicketsData=generatedTicket::not_checkedin_tickets($checkedInTicket);

//         event(new UncheckedInTickets($uncheckedTicketsData));

//         return response()->json([
//             'is_status'=>0
//         ]);
//     } else if($selectedTicket->checked_in == 0)
//     {
//         if($selectedTicket->is_first == 1 &&  $selectedTicket->multiple_id == $ticketsId)
//         {
//             generatedTicket::where(['multiple_id'=>$ticketsId])->update(['checked_in'=>1]);

//             $ticketsIdsArray=generatedTicket::where(['multiple_id'=>$ticketsId])->pluck('id')->toArray();

//             $checkedInTicket=$selectedTicket->id;

//             $ticketsAnalyticsData=generatedTicket::tickets_analytics();

//             event(new TicketsAnalytics($ticketsAnalyticsData));
            
//             $uncheckedTicketsData=generatedTicket::not_checkedin_tickets($checkedInTicket);

//             event(new UncheckedInTickets($uncheckedTicketsData));

//             return response()->json([
//                 'is_status'=>1,
//                 'ticketsIds'=>$ticketsIdsArray
//             ]);
            
//         } else {
//             $selectedTicket->update
//             ([
//                 'checked_in'=>1
//             ]);

//             $checkedInTicket=$selectedTicket->id;

//             $ticketsAnalyticsData=generatedTicket::tickets_analytics();

//             event(new TicketsAnalytics($ticketsAnalyticsData));
            
//             $uncheckedTicketsData=generatedTicket::not_checkedin_tickets($checkedInTicket);

//             event(new UncheckedInTickets($uncheckedTicketsData));

//             return response()->json([
//                 'is_status'=>1,
//                 'ticketsIds'=>null
//             ]);
//         }
//     }
// }
public function check_in_barcode_scan(Request $request)
{
    $ticket_id=$request->checkedInTicketId;

    $selectedTicket=generatedTicket::where(['id'=>$ticket_id])->whereDate('created_at', Carbon::today())->first();

    $selectedTicket->update
    ([
        'checked_in'=>1
    ]);

    $checkedInTicket=$selectedTicket->id;

    $ticketsAnalyticsData=generatedTicket::tickets_analytics();

    event(new TicketsAnalytics($ticketsAnalyticsData));
    
    $uncheckedTicketsData=generatedTicket::not_checkedin_tickets($checkedInTicket);

    event(new UncheckedInTickets($uncheckedTicketsData));

    return response()->json([
        'is_status'=>1,
        'ticketsIds'=>null
    ]);
}

public function check_in_status(Request $request)
{
    try {
        // Validate request
        if (!$request->has('checkedInTicketId') || empty($request->checkedInTicketId)) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket ID is required',
                'is_status' => null,
                'ticketsIds' => null
            ], 400);
        }

        $ticket_id = $request->checkedInTicketId;

        // Find the ticket with proper error handling
        $selectedTicket = generatedTicket::where(['id' => $ticket_id])
            ->whereDate('created_at', Carbon::today())
            ->first();

        if (!$selectedTicket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found or not from today',
                'is_status' => null,
                'ticketsIds' => null
            ], 404);
        }

        $ticketsId = $selectedTicket->multiple_id;

        // Handle checked in tickets (status = 1)
        if ($selectedTicket->checked_in == 1) {
            return $this->handleCheckedInTicket($selectedTicket, $ticketsId);
        } 
        // Handle unchecked tickets (status = 0)
        else if ($selectedTicket->checked_in == 0) {
            return $this->handleUncheckedTicket($selectedTicket, $ticketsId);
        }
        // Handle invalid status
        else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid ticket status',
                'is_status' => null,
                'ticketsIds' => null
            ], 400);
        }

    } catch (\Exception $e) {
        \Log::error('Check in status error: ' . $e->getMessage(), [
            'ticket_id' => $request->checkedInTicketId ?? 'not provided',
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'An error occurred while processing the request',
            'is_status' => null,
            'ticketsIds' => null
        ], 500);
    }
}

public function reset_current_ticket(Request $request)
{

    DB::table('tickets_management')->update(['ticket_number'=>0,'updated_at'=>Carbon::now()->toDateTimeString()]);
    
    return response()->json([
        'status'=>200
    ]);
}

// public function reset_tickets(Request $request)
// {

//     generatedTicket::where(['America/Vancouver'=>0])->update(array('America/Vancouver' => 1));
    
//     return response()->json([
//         'status'=>200
//     ]);
// }

Public function number_control()
{
    //generatedTicket::update_ticket_if_dates_difference();
    
    return view('admin.number_control');
}

public function get_ticket_served()
{
    $ticketServed=generatedTicket::ticket_served();

    return $ticketServed;
}

public function adjust_served_ticket(Request $request)
{
    $update_ticket=(int)$request->adjusted_value + (int)$request->current_ticket;

    $all_active_tickets = generatedTicket::whereDate('created_at', Carbon::today())
    ->where('is_cancelled',0)
    ->where('status',1)
    ->where('America/Vancouver',0)
    ->pluck('ticket_number')
    ->toArray();

    $currentTicket=ticketManagement::first();

    if($update_ticket <= 0 || $update_ticket < 1)
    {
        if($currentTicket)
        {
            $currentTicket->update
            ([
                'ticket_number'=>0
            ]);
        } else {
            $ticket=new ticketManagement();
            $ticket->ticket_number=$update_ticket;
            $ticket->save();
        }

        $count=0;

        $ticketsAnalyticsData=generatedTicket::tickets_analytics();

        event(new TicketUpdated($count));

        event(new TicketsAnalytics($ticketsAnalyticsData));

        // $notCheckedInTicketsData=generatedTicket::not_checkedin_tickets();

        // event(new UncheckedInTickets($notCheckedInTicketsData));
        
        return response()->json([
            'status'=>200,
            'ticket_number'=>0
        ]);
    }

    if(in_array((string)$update_ticket, $all_active_tickets))
    {
        $selectedTicket=generatedTicket::whereDate('created_at', Carbon::today())
        ->where('is_cancelled',0)
        ->where('ticket_number',$update_ticket)
        ->where('America/Vancouver',0)
        ->where('status',1)
        ->first();

        $selectedTicket->update
        ([
            'is_served'=>1
        ]);

        if($currentTicket)
        {
            $currentTicket->update
            ([
                'ticket_number'=>$update_ticket
            ]);
        } else {
            $ticket=new ticketManagement();
            $ticket->ticket_number=$update_ticket;
            $ticket->save();
        }

        $count=$update_ticket;

        event(new TicketUpdated($count));

        $ticketsAnalyticsData=generatedTicket::tickets_analytics();

        event(new TicketsAnalytics($ticketsAnalyticsData));

        // $notCheckedInTicketsData=generatedTicket::not_checkedin_tickets();

        // event(new UncheckedInTickets($notCheckedInTicketsData));

        return response()->json([
            'status'=>200,
            'ticket_number'=>$update_ticket
        ]);
    } else {
        if($currentTicket)
        {
            $currentTicket->update
            ([
                'ticket_number'=>$update_ticket
            ]);
        } else {
            $ticket=new ticketManagement();
            $ticket->ticket_number=$update_ticket;
            $ticket->save();
        }

        $count=$update_ticket;

        event(new TicketUpdated($count));

        $ticketsAnalyticsData=generatedTicket::tickets_analytics();

        event(new TicketsAnalytics($ticketsAnalyticsData));

        // $notCheckedInTicketsData=generatedTicket::not_checkedin_tickets();

        // event(new UncheckedInTickets($notCheckedInTicketsData));

        return response()->json([
            'status'=>200,
            'ticket_number'=>$update_ticket
        ]);
    }
}


    public function assignCaseNumber(Request $request){

        $user = User::where('case_number', $request->case_number)
        ->where('last_name', $request->last_name)
        ->first();
        if($user){
            return response()->json($user);
        } else {
            return response()->json(['message' => 'The login information is incorrect, please correct and try again'], 400);
        }
        
    }
    
    public function register(Request $request)
    {
        $data=$request->all();

        $rules = [
            'last_name'=>'required',
            'first_name'=>'required',
            'date_of_birth'=>'required',
            // 'idcard_issue_date'=>'required',
            'case_number'=>'required',
            'proxy'=>'required|in:yes,no'
        ];

        $custommessages = [
            'first_name.required' => 'First name value is required',
            'last_name.required' => 'Last name value is required',
            'date_of_birth.required' => 'Select Date of Birth',
            // 'idcard_issue_date.required' => 'Select Date of IdCard Issuing',
            'case_number.required' => 'Case number value is required',
            'proxy.in' => 'Select if proxy is yes or no'
        ];

        $validator = Validator::make($data, $rules, $custommessages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 405,
                'message' => $validator->errors()
            ]);
        } else {

            $user=User::where('case_number',$request->case_number)
             ->first();

            if($user)
            {
                return response()->json([
                    'status' => 500,
                    'message' => 'The Case Number is registered for another user'
                ]);
            }

            if($data['id_card'] !== null)
			{
				if ($request->hasFile('id_card')) {
                    $imagetmp = $request->file('id_card');
                    if ($imagetmp->isValid()) {
                        $extension = $imagetmp->getClientOriginalExtension();
                        $id_card = $request->case_number.'-'.rand(111,9999).'.'.$extension;
                        $dest = public_path('/images/id_cards/');
                        $imagetmp->move($dest, $id_card);
                    }
                }

				$id_card=$id_card;
			} else {
				$id_card='';
			}

            $date_of_birth=Carbon::parse($request->date_of_birth)->format('Y-m-d');
            $idcard_issuance_date=Carbon::parse($request->idcard_issue_date)->format('Y-m-d');

            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->date_of_birth = $date_of_birth;
            $user->case_number = $request->case_number;
            $user->proxy = $request->proxy;
            $user->is_active = $request->disable_login;
            $user->id_card = $id_card;
            $user->idcard_issued_date = $idcard_issuance_date;
            $user->save();
            
            // $lastTokenDate = now()->format('Y-m-d');
            // $tokenResetTime = now()->setTime(18, 0, 0); // 6 PM
            // $tokenIntervalTime = now()->setTime(13, 0, 0); // 1 PM
            // $currentTime = now();

            // $lastTokenDate = now()->format('Y-m-d');

            // if ($user->last_token_date === $lastTokenDate) {
            //     $newToken = $user->token;
            //     $nextTokenIntervalTime = $user->token_interval_time;
            // } else {
            //     $newToken = User::whereDate('last_token_date', now()->toDateString())->count() + 1;
            //     $minutesToAdd = ($newToken - 1) * 2; // Each token has 2 minutes interval
            //     $nextTokenIntervalTime = $tokenIntervalTime->addMinutes($minutesToAdd);
            // }

            // $user->token = $newToken;
            // $user->last_token_date = $lastTokenDate;
            // $user->token_reset_time = $currentTime->format('H:i:s');
            // $user->token_interval_time = $nextTokenIntervalTime;

            // Auth::login($user);

            return response()->json([
                'status' => 200,
                'message' => 'Recipient has been added successfully.',
				'user_id' => $user->id
            ]);
            // return redirect()->route("user.dashboard");
        }
    }

    // public function getRecipientNumber(Request $request){
       
    //     $user = User::where('case_number', $request->case_number)
    //     ->where('last_name', $request->last_name)
    //     ->first();
    //     if($user){
    //         return response()->json($user);
    //     } else {
    //         return response()->json(['message' => 'The login information is incorrect, please correct and try again'], 400);
    //     }
        
    // }

    // public function enableUserlogin(Request $request){
    //     User::query()->update(['is_active' => 1]);
    //     return back();
    // }

    // public function disableUserlogin(Request $request){
    //     User::query()->update(['is_active' => 0]);
    //     return back();
    // }
    
//   public function updateUserStatus($id)
//     {
//         $user = User::findOrFail($id);
//         $user->is_enabled = !$user->is_enabled; // Toggle the user's enable status

//         if ($user->save()) {
//             return response()->json(['status' => 'success', 'message' => 'User status updated successfully']);
//         } else {
//             return response()->json(['status' => 'error', 'message' => 'Failed to update user status']);
//         }
//     }
    
    public function admin_password_reset(Request $request,$id)
    {
       
        $user = User::findOrFail($id);
        $user->case_number = $request->casenumber;

        if ($user->save()) {
              return back()->with('success', 'Admin Password updated successfully.');
           
        } else {
              return back()->with('success', 'Failed to Admin Password  status.');
          
        }
    }
}
