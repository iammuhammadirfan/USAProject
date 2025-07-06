<?php

namespace App\Http\Controllers\User;

use DateTime;
use Session;
use DateTimeZone;
use Carbon\Carbon;
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
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;
use App\Services\TicketService;
use App\Events\ReloadTables;
use App\Events\TicketUpdated;

class TicketServiceController extends Controller
{

    protected $newTicketsArray = [];

    public function get_noshows_tickets(Request $request){
        $notCheckedInTicketsData=DB::table('generated_tickets')
            ->leftJoin('users','generated_tickets.user_id','=','users.id')
            ->where('generated_tickets.status',1)
            ->where('generated_tickets.is_active',1)
            ->where('generated_tickets.is_cancelled',0)
            ->where('generated_tickets.checked_in',0)
            ->where('generated_tickets.is_reset',0)
            // ->whereDate('generated_tickets.created_at', Carbon::today())
            ->select(
                'generated_tickets.id',
                'generated_tickets.ticket_number',
                'generated_tickets.created_at',
                'generated_tickets.is_first',
				'generated_tickets.checked_in',
                'generated_tickets.status')
            ->selectRaw('users.first_name as firstName')
            ->selectRaw('users.last_name as lastName')
            ->selectRaw('users.case_number as caseNumber')
            ->selectRaw('users.id as userId')
            ->orderBy('generated_tickets.ticket_number','asc')
            ->get();
    

            $notCheckedInTicketsData = DataTables::of($notCheckedInTicketsData)
    
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
    
                ->rawColumns(['fname','lname','ticket','case_number','created'])
                ->make(true);
    
                return $notCheckedInTicketsData;
    
    }

    public function overview_dashboard_copy(){

        $ticketsData=DB::table('generated_tickets')
            ->where('generated_tickets.status','=',1)
            ->where('generated_tickets.is_active','=',1)
            // ->whereDate('created_at', Carbon::today())
            ->where('generated_tickets.is_cancelled','=',0)
            ->where('generated_tickets.is_reset','=',0);
    
        $notCheckedInTicketsCount=count(DB::table('generated_tickets')
            // ->whereDate('created_at', Carbon::today())
            ->where('generated_tickets.status','=',1)
            ->where('generated_tickets.is_active','=',1)
            ->where('generated_tickets.is_cancelled','=',0)
            ->where('generated_tickets.is_reset','=',0)
            ->where('generated_tickets.checked_in','=',0)->get());
    
            $allTicketsCount=count($ticketsData->get());
    
            $checkedInTicketsCount=count($ticketsData->where('generated_tickets.checked_in','=',1)->get());
    
            // generatedTicket::update_ticket_if_dates_difference();
    
        return view('admin.overview_dashboard_copy',compact('allTicketsCount','checkedInTicketsCount','notCheckedInTicketsCount'));
    }

    // manage ticket options
    public function get_ticket_options(Request $request)
    {
        // $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));

        $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));

        $todaysDate=$dateObj->format("Y-m-d");

        $parsedDate=Carbon::parse($todaysDate);

        $user_id=Auth::user()->id;

        // check if use has a ticket already for todays date
        $ticketChecker=DB::table('generated_tickets')
            ->where(['user_id'=>$user_id,'is_active'=>1,'status'=>1,'is_cancelled'=>0])
            ->whereDate('generated_tickets.created_at',$parsedDate)
            ->first();

        if($ticketChecker)
        {
            $isApplicable=1;
        } else {
            $isApplicable=0;
        }

        return view('user.ticket_options',compact('isApplicable'));
    }

    public function get_ticket_served()
    {
        $ticketServed=generatedTicket::ticket_served();

        return $ticketServed;
    }

public function one_ticket_details($id)
{
    $ticketDetails=DB::table('generated_tickets')
    ->leftJoin('users','generated_tickets.user_id','=','users.id')
    ->select('generated_tickets.id','generated_tickets.ticket_number','generated_tickets.projected_return_time','generated_tickets.created_at','generated_tickets.status')
    ->selectRaw('users.first_name as firstName')
    ->selectRaw('users.last_name as lastName')
    ->selectRaw('users.case_number as caseNumber')
    ->where(['generated_tickets.status'=>1,'generated_tickets.is_cancelled'=>0,'generated_tickets.id'=>$id])
    ->first();

    if($ticketDetails)
    {
        if(Auth::user()->role == '2')
        {
            return view('user.one_ticket_details',compact('ticketDetails'));
        } else {
            return view('admin.tgog_generated_s_ticket_details',compact('ticketDetails'));
        }
        
    } else {
        abort(404);
    }

    
}

public function generate_ticket_pdf($id)
{
    $ticketDetails=DB::table('generated_tickets')
    ->leftJoin('users','generated_tickets.user_id','=','users.id')
    ->select('generated_tickets.id','generated_tickets.ticket_number','generated_tickets.created_at','generated_tickets.status','generated_tickets.projected_return_time')
    ->selectRaw('users.first_name as firstName')
    ->selectRaw('users.last_name as lastName')
    ->selectRaw('users.case_number as caseNumber')
    ->where('generated_tickets.status','=',1)
    ->where('generated_tickets.is_cancelled','=',0)
    ->where('generated_tickets.id','=',$id)
    ->first();

    if($ticketDetails)
    {
        return view('user.one_ticket_details',compact('ticketDetails'));
    } else {
        return abort(404);
    } 
}


public function view_ticket_size_pdf($id,$type)
{
    $ticketDetails=DB::table('generated_tickets')
    ->leftJoin('users','generated_tickets.user_id','=','users.id')
    ->select('generated_tickets.id','generated_tickets.ticket_number','generated_tickets.created_at','generated_tickets.status','generated_tickets.projected_return_time')
    ->selectRaw('users.first_name as firstName')
    ->selectRaw('users.last_name as lastName')
    ->selectRaw('users.case_number as caseNumber')
    ->where(['generated_tickets.status'=>1,'generated_tickets.is_cancelled'=>0,'generated_tickets.id'=>$id])
    ->first();

    if($ticketDetails == null)
    {
        return abort(404);
    }

    if (in_array(Auth::user()->role, [1, 3]))
    {
        DB::table('activity_log')->insertGetId([
            'staff_id' => Auth::user()->id,
            'ticket_id' => $id,
            'activity_id' => 5,
            'created_at' => Carbon::now()
        ]);
    }

    $data = [
        'ticketDetails'=>$ticketDetails
    ];

    
    $ticketpdf=app()->make(PDF::class);

    if($type == 'card')
    {
        $html = view('user.card_ticket_details_pdf', $data)->render();
        $ticketpdf->setPaper(array(0, 0,672,576), 'landscape');
        $ticketpdf->loadHTML($html);

    } else if ($type == 'letter')
    {
        $html = view('user.letter_ticket_details_pdf', $data)->render();
        $ticketpdf->setPaper(array(0, 0,1056,816), 'letter');
        $ticketpdf->loadHTML($html); 
    } 

    return $ticketpdf->stream();
}
 


public function select_multiple_ticket_details($id)
{
    $userDetails=DB::table('users')
    ->select('users.id','users.first_name','users.last_name','users.proxy','users.case_number','users.role','users.status')
    ->where('users.id','=',$id)
    ->first();
	
	// $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));

    $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));

    $todaysDate=$dateObj->format("Y-m-d");

    $parsedDate=Carbon::parse($todaysDate);

    // check if use has a ticket already for todays date
    $ticketChecker=DB::table('generated_tickets')
        ->where(['user_id'=>$id,'is_active'=>1,'status'=>1,'is_cancelled'=>0])
        ->whereDate('generated_tickets.created_at',$parsedDate)
        ->first();

    if($ticketChecker)
    {
        $isApplicable=1;
    } else {
        $isApplicable=0;
    }

    return view('user.select_multiple_tickets_details',compact('userDetails','isApplicable'));
}



public function verify_single_case_number(Request $request)
{
    // $userIdsArray=json_decode($request->userIdsObject);

    // check if case number exists in the table
    $case_number_data=User::where('case_number',$request->caseNumber)
    ->select('id','first_name','last_name','proxy','case_number')
    ->first();

    // check if case number has a ticket already
        // $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));
        $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));

        $todaysDate=$dateObj->format("Y-m-d");
        $parsedDate=Carbon::parse($todaysDate);

        

    if($case_number_data)
    {
        // check if user has a ticket already for todays date
        $case_number_ticket=DB::table('generated_tickets')
            ->where(['user_id'=>$case_number_data->id,'is_active'=>1,'status'=>1,'is_cancelled'=>0])
            ->whereDate('generated_tickets.created_at',$parsedDate)
            ->first();
        
        if($case_number_ticket)
        {
            $message="The User has already been assigned a ticket for today.";

            return response()->json([
                'status'=>450,
                'message'=>$message
            ]); 
        } else {
            return response()->json([
                'status'=>201,
                'data'=>$case_number_data
            ]); 
        }
    } else {

        $message="Invalid Case Number. Please correct and try again.";

        return response()->json([
            'status'=>415,
            'message'=>$message
        ]);
    }
}
public function check_user_ticket(Request $request)
{
	// $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));
    $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));

    $todaysDate=$dateObj->format("Y-m-d");
	
	$firstUserTodaysTicket=generatedTicket::whereDate('created_at', $todaysDate)
    ->where([
        'status' => 1, 
        'user_id' => $request->userId, 
        'is_reset'=>0,
        'is_cancelled' => 0
    ])->count();
			
	if($firstUserTodaysTicket > 0)
	{
		return response()->json([
			'status' => 500,
			'message' => 'The User has already been issued a ticket for today'
		]);
	} else {
		return response()->json([
			'status' => 200,
		]);
	}		
			
}

// check case number existence and if proxy is allowed
public function check_case_number(Request $request)
{
    $userIdsArray=json_decode($request->userIdsObject);

    // check if case number exists in the table
    $case_number_data=User::where('case_number',$request->caseNumber)
    ->select('id','first_name','last_name','proxy','case_number')
    ->first();

    // check if case number has a ticket already
        // $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));

        $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));
        $todaysDate=$dateObj->format("Y-m-d");
        $parsedDate=Carbon::parse($todaysDate);

        

    if($case_number_data)
    {
        // check if user has a ticket already for todays date
        $case_number_ticket=DB::table('generated_tickets')
            ->where(['user_id'=>$case_number_data->id,'is_active'=>1,'status'=>1,'is_cancelled'=>0])
            ->whereDate('generated_tickets.created_at',$parsedDate)
            ->first();

        $userIdString = (string) $case_number_data->id;

        if(in_array($userIdString, $userIdsArray))
        {
            $message="The User has already been selected.";

            return response()->json([
                'status'=>430,
                'message'=>$message
            ]);
        }
        
        if($case_number_ticket)
        {
            $message="The User has already been assigned a ticket for today.";

            return response()->json([
                'status'=>450,
                'message'=>$message
            ]); 
        }

        // check is proxy is yes
        if($case_number_data->proxy == 'Yes')
        {
            return response()->json([
                'status'=>201,
                'data'=>$case_number_data
            ]); 
        } else if ($case_number_data->proxy == 'No')
        {
            $message="No Proxy Available. Check case number or remove.";

            return response()->json([
                'status'=>404,
                'message'=>$message
            ]);
        }
    } else {

        $message="Invalid Case Number. Please correct and try again.";

        return response()->json([
            'status'=>415,
            'message'=>$message
        ]);
    }
}

public function verify_admin_casenumber(Request $request)
{
    $userIdsArray=json_decode($request->userIdsObject);

    // check if case number exists in the table
    $case_number_data=User::where('case_number',$request->caseNumber)
    ->select('id','first_name','last_name','proxy','case_number')
    ->first();

    // check if case number has a ticket already
        // $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));
        $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));
        $todaysDate=$dateObj->format("Y-m-d");
        $parsedDate=Carbon::parse($todaysDate);
    
        if($case_number_data)
        {
            // check if user has a ticket already for todays date
            $case_number_ticket=DB::table('generated_tickets')
            ->where(['user_id'=>$case_number_data->id,'is_active'=>1,'status'=>1,'is_cancelled'=>0])
            ->whereDate('generated_tickets.created_at',$parsedDate)
            ->first();
			
			if($case_number_ticket)
			{
				$message="The User has already been assigned a ticket for today.";

				return response()->json([
					'status'=>450,
					'message'=>$message
				]); 
			}
    
            $userIdString = (string) $case_number_data->id;
    
            if(in_array($userIdString, $userIdsArray))
            {
                $message="The User has already been selected.";
    
                return response()->json([
                    'status'=>430,
                    'message'=>$message
                ]);
            } else {
                return response()->json([
                    'status'=>201,
                    'data'=>$case_number_data
                ]);
            }
        } else {
    
            $message="Invalid Case Number. Please correct and try again.";
    
            return response()->json([
                'status'=>415,
                'message'=>$message
            ]);
        }
}

public function cancel_one_ticket_details(Request $request)
{
    $data=$request->all();

    $ticket=generatedTicket::find($request->ticketId);

    if($ticket)
    {
        $ticket->update([
            $ticket->is_active=0,
            $ticket->is_cancelled=Auth::user()->id
        ]);

        if (in_array(Auth::user()->role, [1, 3]))
        {
            DB::table('activity_log')->insertGetId([
                'staff_id' => Auth::user()->id,
                'ticket_id' => $request->ticketId,
                'activity_id' => 4,
                'created_at' => Carbon::now()
            ]);
        }

        Redis::rpush(
            "tickets:cancelled:" . date('Y-m-d', strtotime($ticket->created_at)),
            $ticket->ticket_number
        );

        $ticketsData=DB::table('generated_tickets')
        ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'is_reset'=>0])
        ->whereDate('created_at', Carbon::today());

    $notCheckedInTicketsCount=count(DB::table('generated_tickets')
        ->whereDate('created_at', Carbon::today())
        ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'is_reset'=>0,'checked_in'=>0])->get());

        $allTicketsCount=count($ticketsData->get());

        $checkedInTicketsCount=count($ticketsData->where('generated_tickets.checked_in','=',1)->get());

        $ticketsAnalytics=array();

        $ticketsAnalytics=[
            'all_tickets_count'=>$allTicketsCount,
            'tickets_served_count'=>$checkedInTicketsCount,
            'remaining_tickets_count'=>$notCheckedInTicketsCount
        ];

        event(new TicketsAnalytics($ticketsAnalytics));

        $cancelledTicketsData=generatedTicket::remove_cancelled_tickets($request->ticketId);

        event(new CancelledTickets($cancelledTicketsData));
    
        return response()->json([
            'status'=>200
        ]);
    } else {
        return response()->json([
            'status'=>404,
            'message'=>'Ticket Not Available'
        ]);
    }
    
}

public function manage_tickets_generation(Request $request)
{
    $service = new TicketService(); 
    
    try {
        $response = $service->generateTickets($request);
        
        // Ensure we always return a JSON response
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            return $response;
        }
        
        return response()->json([
            'status' => 200,
            'message' => 'Ticket generated successfully',
            'data' => $response
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => $e->getMessage()
        ], 500);
    }
}

public function reset_redis_ticket(Request $request)
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

    return response()->json([
        'status'=>200
    ]);
    
}

public function current_redis_ticket(Request $request)
{ 
    $todaysDate = now()->format('Y-m-d');
    $ticketNumberKey = "tickets:counter:$todaysDate";
    Redis::connection();

    // dd((int)Redis::get($ticketNumberKey));die();
    
    return (int)Redis::get($ticketNumberKey);
    
}


public function multiple_users_ticket_details($id)
{
    $user = User::where('id', '=', Auth::user()->id)
                ->first();

    //Auth::login($user);
    
    $ticketDetails=DB::table('generated_tickets')
    ->leftJoin('users','generated_tickets.user_id','=','users.id')
    ->select('generated_tickets.id','generated_tickets.ticket_number','generated_tickets.created_at','generated_tickets.status')
    ->selectRaw('users.first_name as firstName')
    ->selectRaw('users.last_name as lastName')
    ->selectRaw('users.case_number as caseNumber')
    ->where('generated_tickets.status','=',1)
    ->where('generated_tickets.is_cancelled','=',0)
    ->where('generated_tickets.multiple_id','=',$id)
    ->orderBy('generated_tickets.ticket_number','asc')
    ->get();

    $generatedTicketDetails=DB::table('generated_tickets')
    ->select('multiple_id','created_at','status','projected_return_time',)
    ->where(['status'=>1,'is_cancelled'=>0,'multiple_id'=>$id])
    ->orderBy('ticket_number','asc')
    ->first();

    if(count($ticketDetails)>0)
    {
        if($user->role == '2')
        {
            return view('user.multiple_users_ticket_details',compact('ticketDetails','generatedTicketDetails'));
        } else {
            return view('admin.tgog_generated_m_ticket_details',compact('ticketDetails','generatedTicketDetails'));
        }
    } else {
        return abort(404);
    }

    
}

public function view_multiple_users_ticket_size_pdf($id,$type)
{
    $ticketDetails=DB::table('generated_tickets')
    ->leftJoin('users','generated_tickets.user_id','=','users.id')
    ->select('generated_tickets.id','generated_tickets.ticket_number','generated_tickets.created_at','generated_tickets.status')
    ->selectRaw('users.first_name as firstName')
    ->selectRaw('users.last_name as lastName')
    ->selectRaw('users.case_number as caseNumber')
    ->where('generated_tickets.status','=',1)
    ->where('generated_tickets.is_cancelled','=',0)
    ->where('generated_tickets.multiple_id','=',$id)
    ->orderBy('generated_tickets.ticket_number','asc')
    ->get();

    $generatedTicketDetails=DB::table('generated_tickets')
    ->select('multiple_id','created_at','status','projected_return_time','id')
    ->where(['status'=>1,'is_cancelled'=>0,'multiple_id'=>$id])
    ->orderBy('ticket_number','desc')
    ->first();

    DB::table('activity_log')->insertGetId([
        'staff_id' => Auth::user()->id,
        'ticket_id' => $generatedTicketDetails->id,
        'activity_id' => 5,
        'created_at' => Carbon::now()
    ]);

    $data = [
        'ticketDetails'=>$ticketDetails,
        'generatedTicketDetails'=>$generatedTicketDetails
    ];

    
    $ticketpdf=app()->make(PDF::class);

    if($type == 'card')
    {
        $html = view('user.card_multiple_ticket_details_pdf', $data)->render();
        $ticketpdf->setPaper(array(0, 0,288,384), 'landscape');
        $ticketpdf->loadHTML($html);

    } else if ($type == 'letter')
    {
        $html = view('user.letter_multiple_ticket_details_pdf', $data)->render();
        $ticketpdf->setPaper(array(0, 0,1056,816), 'letter');
        $ticketpdf->loadHTML($html); 
    }
    

    return $ticketpdf->stream();
}

public function cancel_multiple_tickets_details(Request $request)
{
    $ticketDetail=DB::table('generated_tickets')
    ->select('multiple_id','created_at','status','id')
    ->where(['multiple_id'=>$request->ticketId])
    ->orderBy('ticket_number','desc')
    ->first();

    if($ticketDetail)
    {
        $tickets=generatedTicket::where('multiple_id',$request->ticketId);
        $tickets->update([
            'is_active' => 0,
            'is_cancelled' => Auth::user()->id
        ]);

        if (in_array(Auth::user()->role, [1, 3]))
        {
            DB::table('activity_log')->insertGetId([
                'staff_id' => Auth::user()->id,
                'ticket_id' =>$ticketDetail->id,
                'activity_id' => 4,
                'created_at' => Carbon::now()
            ]);
        }

        $m_tickets = DB::table('generated_tickets')->where('multiple_id',$request->ticketId)->select('created_at','ticket_number')->get()->toArray();

        Redis::pipeline(function ($pipe) use ($m_tickets) {
            foreach ($m_tickets as $ticket) {
                $pipe->rpush(
                    "tickets:cancelled:" . date('Y-m-d', strtotime($ticket->created_at)),
                    $ticket->ticket_number
                );
            }
        });
        
        $cancelledTicketsData=generatedTicket::remove_cancelled_tickets($request->ticketId);

        event(new CancelledTickets($cancelledTicketsData));

        return response()->json([
            'status'=>200
        ]);
    } else {
        return response()->json([
            'status'=>404,
            'message'=>'Tickets Not Available'
        ]);
    }
    
}

public function get_user_details(Request $request)
{
    $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));
    $todaysDate=$dateObj->format("Y-m-d");
    $parsedDate=Carbon::parse($todaysDate);

    $userCaseNumber = preg_replace('/^00/', '', $request->case_number);

    if(strlen($userCaseNumber) == 5 && $userCaseNumber[0] == '0')
    {
        $rawCaseNumber=preg_replace('/0/', 'C', $userCaseNumber, 1);
        
    }

    if(strlen($userCaseNumber) == 6 && $userCaseNumber[0] == '0' && substr($userCaseNumber, 0, 2) === "00")
    {
        $rawCaseNumber=preg_replace('/^00/', 'C', $userCaseNumber, 1);
        
    } else if (strlen($userCaseNumber) == 6 && $userCaseNumber[0] == '0' && substr($userCaseNumber, 0, 1) === "0")
    {
        $rawCaseNumber=preg_replace('/0/', 'C', $userCaseNumber, 1);
    }

    if(strlen($userCaseNumber) == 6 && $userCaseNumber[0] !== '0')
    {
        $rawCaseNumber='C' . $userCaseNumber;
    }
    
    // check if a user exist and has a ticket for today

    $barcode_data=DB::table('users')
    ->where('case_number', 'LIKE', '%' . $rawCaseNumber)
    ->where(['status'=>1])
    ->select('id','first_name','last_name','proxy','case_number','date_of_birth')
    ->first();

    // check if a usr has a ticket for today
    if($barcode_data !== null)
    {
        $ticket_data=DB::table('generated_tickets')
            ->where(['user_id'=>$barcode_data->id,'is_active'=>1,'status'=>1,'is_cancelled'=>0])
            ->whereDate('created_at',$parsedDate)
            ->count();
    }

    if($barcode_data == null)
    {
        return response()->json([
			'status' => 404,
			'message' => 'Case number not found'
		]);

    } else if($barcode_data !== null && $ticket_data == 0)
    {
        return response()->json([
			'status' => 200,
			'data' => $barcode_data
		]);

    } else if($barcode_data !== null && $ticket_data > 0)
    {
        return response()->json([
			'status' => 500,
			'message' => 'User already has a ticket for today'
		]);

    }

}

public function get_users_details(Request $request)
{
    $userCaseNumber = preg_replace('/^00/', '', $request->case_number);

    if(strlen($userCaseNumber) == 5 && $userCaseNumber[0] == '0')
    {
        $rawCaseNumber=preg_replace('/0/', 'C', $userCaseNumber, 1);
        
    }

    if(strlen($userCaseNumber) == 6 && $userCaseNumber[0] == '0' && substr($userCaseNumber, 0, 2) === "00")
    {
        $rawCaseNumber=preg_replace('/^00/', 'C', $userCaseNumber, 1);
        
    } else if (strlen($userCaseNumber) == 6 && $userCaseNumber[0] == '0' && substr($userCaseNumber, 0, 1) === "0")
    {
        $rawCaseNumber=preg_replace('/0/', 'C', $userCaseNumber, 1);
    }

    if(strlen($userCaseNumber) == 6 && $userCaseNumber[0] !== '0')
    {
        $rawCaseNumber='C' . $userCaseNumber;
    }

    // $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));
    $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));
    $todaysDate=$dateObj->format("Y-m-d");
    $parsedDate=Carbon::parse($todaysDate);
    

    // check if a user exist and has a ticket for today and also if they are an admin or a normal user

    $barcode_data=DB::table('users')
    ->where('case_number', 'LIKE', '%' . $rawCaseNumber)
    ->where(['status'=>1])
    ->select('id','first_name','last_name','proxy','case_number','date_of_birth','role')
    ->first();

    // check if a user has a ticket for today
    if($barcode_data !== null && $barcode_data->role == 2)
    {
        $ticket_data=DB::table('generated_tickets')
            ->where(['user_id'=>$barcode_data->id,'is_active'=>1,'status'=>1,'is_cancelled'=>0])
            ->whereDate('created_at',$parsedDate)
            ->count();
    } else {
        $ticket_data=0;
    }

    if($barcode_data == null)
    {
        return response()->json([
			'status' => 404,
			'message' => 'Case number not found'
		]);

    }

    // validate admin
    if($barcode_data !== null && $ticket_data == 0)
    {
        return response()->json([
			'status' => 200,
			'data' => $barcode_data
		]);

    }
    
    // if($barcode_data !== null && $ticket_data == 0 && $barcode_data->role == 2 && $barcode_data->proxy == 'Yes')
    // {
    //     return response()->json([
	// 		'status' => 200,
	// 		'data' => $barcode_data
	// 	]);

    // }
    
    if($barcode_data !== null && $ticket_data > 0)
    {
        return response()->json([
			'status' => 500,
			'message' => 'User already has a ticket for today'
		]);

    }
}

public function get_ticket_details(Request $request)
{

    // $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));
    $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));
    $todaysDate=$dateObj->format("Y-m-d");
    $parsedDate=Carbon::parse($todaysDate);
    

    $userCaseNumber = preg_replace('/^00/', '', $request->case_number);

    if(strlen($userCaseNumber) == 5 && $userCaseNumber[0] == '0')
    {
        $rawCaseNumber=preg_replace('/0/', 'C', $userCaseNumber, 1);
        
    }

    if(strlen($userCaseNumber) == 6 && $userCaseNumber[0] == '0' && substr($userCaseNumber, 0, 2) === "00")
    {
        $rawCaseNumber=preg_replace('/^00/', 'C', $userCaseNumber, 1);
        
    } else if (strlen($userCaseNumber) == 6 && $userCaseNumber[0] == '0' && substr($userCaseNumber, 0, 1) === "0")
    {
        $rawCaseNumber=preg_replace('/0/', 'C', $userCaseNumber, 1);
    }

    if(strlen($userCaseNumber) == 6 && $userCaseNumber[0] !== '0')
    {
        $rawCaseNumber='C' . $userCaseNumber;
    }

    // check if a user exist and has a ticket for today

    $barcode_data=DB::table('users')
    ->where('case_number', 'LIKE', '%' . $rawCaseNumber)
    ->where(['status'=>1])
    ->select('id','case_number')
    ->first();
    
    // check if a user has a ticket for today
    if($barcode_data !== null)
    {
        $ticket_data=DB::table('generated_tickets as tickets')
        ->leftJoin('users as user','tickets.user_id','=','user.id')
        ->where([
            'tickets.user_id'=>$barcode_data->id,
            'tickets.is_active'=>1,
            'tickets.status'=>1,
            'tickets.is_cancelled'=>0,
            'user.case_number'=>$barcode_data->case_number,
            'user.status'=>1
        ])
        ->whereDate('tickets.created_at',$parsedDate)
        ->select(
            'tickets.ticket_number',
            'tickets.id',
            'tickets.checked_in',
            'tickets.user_id',
            'tickets.multiple_id',
            'user.first_name',
            'user.last_name',
            'user.case_number',
            'user.date_of_birth'
        )
        ->first();

        if($ticket_data == null)
        {
            return response()->json([
                'status' => 404,
                'message' => 'Ticket Not Available'
            ]);
        }

        if($ticket_data->multiple_id == null)
        {
            return response()->json([
                'status' => 200,
                'data' => $ticket_data
            ]);
        } else {

            $tickets_data=DB::table('generated_tickets as tickets')
            ->leftJoin('users as user','tickets.user_id','=','user.id')
            ->where([
                'tickets.is_active'=>1,
                'tickets.status'=>1,
                'tickets.is_cancelled'=>0,
                'tickets.multiple_id'=>$ticket_data->multiple_id,
                'user.status'=>1
            ])
            ->whereDate('tickets.created_at',$parsedDate)
            ->select(
                'tickets.ticket_number',
                'tickets.checked_in',
                'user.first_name',
                'user.last_name',
                'user.date_of_birth',
                'user.case_number'
            )
            ->get();

            return response()->json([
                'status' => 201,
                'data' => $tickets_data,
                'user_id' => $ticket_data->user_id,
                'ticket_id' => $ticket_data->id,
                'ticket_status' => $ticket_data->checked_in,
                'user_case_number' => $ticket_data->case_number
            ]);
        }
    } else {
        return response()->json([
			'status' => 404,
			'message' => 'No ticket issued for user'
		]);
    }
        // $ticket_data=DB::table('generated_tickets as tickets')
        // ->leftJoin('users as user','tickets.user_id','=','user.id')
        // ->where([
        //     'tickets.user_id'=>$barcode_data->id,
        //     'tickets.is_active'=>1,
        //     'tickets.status'=>1,
        //     'tickets.is_cancelled'=>0,
        //     'user.case_number'=>$final_casenumber,
        //     'user.status'=>1
        // ])
        // ->whereDate('tickets.created_at',$parsedDate)
        // ->select(
        //     'tickets.ticket_number',
        //     'tickets.id',
        //     'tickets.checked_in',
        //     'tickets.user_id','user.first_name','user.last_name','user.case_number','user.date_of_birth'
        // )
        // ->first();

    // if($ticket_data == null)
    // {
        

    // } else 
    // {
    //     return response()->json([
	// 		'status' => 200,
	// 		'data' => $ticket_data
	// 	]);

    // }

}

public function manage_ticket_limit(Request $request)
{
    if($request->ticketLimitVal == '1')
    {
        $data=$request->all();

        $rules = [
            'maxTickets' => 'required|numeric'
        ];

        $custommessages = [
            'maxTickets.required' => 'Enter number of maximum tickets',
            'maxTickets.numeric' => 'Tickets should be a number',
        ];

        $validator = Validator::make($data, $rules, $custommessages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 405,
                'message' => $validator->errors()
            ]);
        } else {
            
            DB::table('tickets_management')->update(['ticket_limit'=>$request->maxTickets,'ticket_limit_status'=>1]);
    
            return response()->json([
                'status'=>200,
                'message'=>'Number of maximum tickets has been set to '.$request->maxTickets
            ]);
        }
    } else {
        DB::table('tickets_management')->update(['ticket_limit'=>'0','ticket_limit_status'=>0]);
    
        return response()->json([
            'status'=>201,
            'message'=>'Maximum tickets set to 0'
        ]);
    }
    
}

public function manage_distribution_times(Request $request)
{
    $data=$request->all();

    $rules = [
        'start_time' => 'required',
        'end_time' => 'required|after:start_time'
    ];

    $custommessages = [
        'start_time.required' => 'Start time field is required',
        'end_time.required' => 'End time field is required',
        'end_time.after' => 'End time field should be greater than start time'
    ];

    $validator = Validator::make($data, $rules, $custommessages);

    if ($validator->fails()) {
        return response()->json([
            'status' => 405,
            'message' => $validator->errors()
        ]);
    } else {
        DB::table('tickets_management')->update(['d_start_time'=>$data['start_time'],'d_end_time'=>$data['end_time']]);
        
        $message="Distribution Times Updated Successfully";

        return response()->json([
            'status'=>200,
            'message'=>$message
        ]);
    }
}


}
