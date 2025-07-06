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


public function update_staff_password()
{
    return view('admin.tgog_password_update');
}



public function general_dashboard(){

    $ticketBeingServed = DB::table('tickets_management')
    ->pluck('ticket_number')->first();

    $ticketsData=DB::table('generated_tickets')
        ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'is_reset'=>0]);

    $notCheckedInTicketsCount=count(DB::table('generated_tickets')
        // ->whereDate('created_at', Carbon::today())
        ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'is_reset'=>0,'checked_in'=>0])->get());

        $allTicketsCount=count($ticketsData->get());

        $checkedInTicketsCount=count($ticketsData->where('generated_tickets.checked_in','=',1)->get());

        if($ticketBeingServed == 0)
        {
            $currentNoShows = 0;
    
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

    return view('admin.general_dashboard',compact('allTicketsCount','currentNoShows','currentTicketsRemaining','checkedInTicketsCount','notCheckedInTicketsCount'));
}

public function admin_activity_log(Request $request)
{
    if ($request->ajax()) {
        
        $activityLogData=DB::table('activity_log')
        ->leftJoin('users as staffName','activity_log.staff_id','=','staffName.id')
        ->leftJoin('users as userName','activity_log.user_id','=','userName.id')
        ->leftJoin('volunteer_group_homes_users as vghUser','activity_log.v_g_home_userId','=','vghUser.id')
        ->leftJoin('volunteer_group_homes_tickets as vghTicket','activity_log.v_g_home_id','=','vghTicket.id')
        ->leftJoin('volunteer_group_homes_users as vghuUserSignUps','vghuUserSignUps.id','=','vghTicket.user_id')
        ->leftJoin('generated_tickets as tickets','activity_log.ticket_id','=','tickets.id')
        ->select(
            'activity_log.created_at',
            'activity_log.activity_id',
            'tickets.ticket_number')
        ->selectRaw('staffName.first_name as sFname')
        ->selectRaw('staffName.last_name as sLname')
        ->selectRaw('userName.first_name as uFname')
        ->selectRaw('userName.last_name as uLname')
        ->selectRaw('userName.case_number as uCnumber')
        ->selectRaw('vghUser.first_name as vghuFname')
        ->selectRaw('vghUser.last_name as vghuLname')
        ->selectRaw('vghuUserSignUps.first_name as vghSPFname')
        ->selectRaw('vghuUserSignUps.last_name as vghSPLname')
        ->where('activity_log.staff_id',Auth::user()->id);

        if($request->activity_date !== null)
        {
            $activity_date=Carbon::parse($request->activity_date)->format('Y-m-d');
            
            $activityLogData->whereDate('activity_log.created_at',  $activity_date);
        } else {
            $activityLogData->where('activity_log.is_reset',0);
        }

        $activityLogData->orderBy('activity_log.id','ASC')->get();

        
    
        $activityLogData = DataTables::of($activityLogData)

        ->addColumn('date', function ($row) {
            return '<span>'.Carbon::parse($row->created_at)->format('m-d-Y').'</span>';
        })
        ->addColumn('time', function ($row) {
            return '<span>' . date('h:i:s A', strtotime($row->created_at)) .'</span';
        })
        ->addColumn('activity_id', function ($row) 
        {
             if ($row->activity_id == 1)
            {
                $activity='Created Single Ticket #'.$row->ticket_number.' - '.$row->uCnumber.', '.$row->uFname.' '.$row->uLname;
                
            } else if ($row->activity_id == 2)
            {
                $activity='Created Multiple Ticket #'.$row->ticket_number.' - '.$row->uCnumber.', '.$row->uFname.' '.$row->uLname;
            } else if ($row->activity_id == 3)
            {
                $activity='Deleted Ticket #'.$row->ticket_number;
            } else if ($row->activity_id == 4)
            {
                $activity='Cancelled Ticket #'.$row->ticket_number;
            } else if ($row->activity_id == 5)
            {
                $activity='Printed Ticket #'.$row->ticket_number;
            } else if ($row->activity_id == 6)
            {
                $activity='Checked in Ticket #'.$row->ticket_number.' to Yes';
            } else if ($row->activity_id == 7)
            {
                $activity='Checked in Ticket #'.$row->ticket_number.' to No';
            } else if ($row->activity_id == 8)
            {
                $activity='Logged in';
            } else if ($row->activity_id == 9)
            {
                $activity='Logged Off';
            } else if ($row->activity_id == 10)
            {
                $activity='Uploaded ID Card for - '.$row->uCnumber.', '.$row->uFname.' '.$row->uLname;
            } else if ($row->activity_id == 11)
            {
                $activity='Printed ID Card for - '.$row->uCnumber.', '.$row->uFname.' '.$row->uLname;
            } else if ($row->activity_id == 12)
            {
                $activity='Registered new User for - '.$row->uCnumber.', '.$row->uFname.' '.$row->uLname;
            } else if ($row->activity_id == 13)
            {
                $activity='Updated details user for - '.$row->uCnumber.', '.$row->uFname.' '.$row->uLname;
            } else if ($row->activity_id == 14)
            {
                $activity='Registered new Volunteer/Group Home User '.$row->vghuFname.' '.$row->vghuLname;
            } else if ($row->activity_id == 15)
            {
                $activity='Updated user details for '.$row->vghuFname.' '.$row->vghuLname;
            } else if ($row->activity_id == 16)
            {
                $activity='Deleted Volunteer/Group Home User '.$row->vghuFname.' '.$row->vghuLname;
            } else if ($row->activity_id == 17)
            {
                $activity='Signed Up  for Volunteer - '.$row->vghSPFname.' '.$row->vghSPLname;
            } else if ($row->activity_id == 18)
            {
                $activity='Signed Up  for Group Home - '.$row->vghSPFname.' '.$row->vghSPLname;
            } else if ($row->activity_id == 19)
            {
                $activity='Updated Details  for Volunteer - '.$row->vghSPFname.' '.$row->vghSPLname;
            } else if ($row->activity_id == 20)
            {
                $activity='Updated Details  for Group Home - '.$row->vghSPFname.' '.$row->vghSPLname;
            } else if ($row->activity_id == 21)
            {
                $activity='Deleted details for Volunteer - '.$row->vghSPFname.' '.$row->vghSPLname;
            } else if ($row->activity_id == 22)
            {
                $activity='Deleted details for Group Home - '.$row->vghSPFname.' '.$row->vghSPLname;
            }

            return '<span>' . $activity .'</span';
        })
        

        ->rawColumns(['date','time','activity_id'])
        ->make(true);

        return $activityLogData;
    }

    return view('admin.tgog_admin_activity_log');
}


public function staff_activity_log(Request $request)
{
    if ($request->ajax()) {
        
        $activityLogData=DB::table('activity_log')
        ->leftJoin('users as staffName','activity_log.staff_id','=','staffName.id')
        ->leftJoin('users as userName','activity_log.user_id','=','userName.id')
        ->leftJoin('volunteer_group_homes_users as vghUser','activity_log.v_g_home_userId','=','vghUser.id')
        ->leftJoin('volunteer_group_homes_tickets as vghTicket','activity_log.v_g_home_id','=','vghTicket.id')
        ->leftJoin('volunteer_group_homes_users as vghuUserSignUps','vghuUserSignUps.id','=','vghTicket.user_id')
        ->leftJoin('generated_tickets as tickets','activity_log.ticket_id','=','tickets.id')
        ->select(
            'activity_log.created_at',
            'activity_log.activity_id',
            'tickets.ticket_number')
        ->selectRaw('staffName.first_name as sFname')
        ->selectRaw('staffName.last_name as sLname')
        ->selectRaw('userName.first_name as uFname')
        ->selectRaw('userName.last_name as uLname')
        ->selectRaw('userName.case_number as uCnumber')
        ->selectRaw('vghUser.first_name as vghuFname')
        ->selectRaw('vghUser.last_name as vghuLname')
        ->selectRaw('vghuUserSignUps.first_name as vghSPFname')
        ->selectRaw('vghuUserSignUps.last_name as vghSPLname');
    
        if($request->staff_id !== null)
        {
            $activityLogData->where('activity_log.staff_id','like','%'.$request->staff_id.'%');
        }
        
        if($request->activity_date !== null)
        {
            $activity_date=Carbon::parse($request->activity_date)->format('Y-m-d');
            
            $activityLogData->whereDate('activity_log.created_at',  $activity_date);
        }  else {
            $activityLogData->where('activity_log.is_reset',0);
        }

        $activityLogData->orderBy('activity_log.id','ASC')->get();

    
        $activityLogData = DataTables::of($activityLogData)

        ->addColumn('date', function ($row) {
            return '<span>'.Carbon::parse($row->created_at)->format('m-d-Y').'</span>';
        })
        ->addColumn('time', function ($row) {
            return '<span>' . date('h:i:s A', strtotime($row->created_at)) .'</span';
        })
        ->addColumn('name', function ($row) {
            return  
            '<span>'.strtoupper(substr($row->sFname, 0, 1)).' '.$row->sLname.'</span';
        })
        ->addColumn('activity_id', function ($row) 
        {
            if ($row->activity_id == 1)
            {
                $activity='Created Single Ticket #'.$row->ticket_number.' - '.$row->uCnumber.', '.$row->uFname.' '.$row->uLname;
                
            } else if ($row->activity_id == 2)
            {
                $activity='Created Multiple Ticket #'.$row->ticket_number.' - '.$row->uCnumber.', '.$row->uFname.' '.$row->uLname;
            } else if ($row->activity_id == 3)
            {
                $activity='Deleted Ticket #'.$row->ticket_number;
            } else if ($row->activity_id == 4)
            {
                $activity='Cancelled Ticket #'.$row->ticket_number;
            } else if ($row->activity_id == 5)
            {
                $activity='Printed Ticket #'.$row->ticket_number;
            } else if ($row->activity_id == 6)
            {
                $activity='Checked in ticket #'.$row->ticket_number.' to Yes';
            } else if ($row->activity_id == 7)
            {
                $activity='Checked in ticket #'.$row->ticket_number.' to No';
            } else if ($row->activity_id == 8)
            {
                $activity='Logged in';
            } else if ($row->activity_id == 9)
            {
                $activity='Logged Off';
            } else if ($row->activity_id == 10)
            {
                $activity='Uploaded ID Card for - '.$row->uCnumber.', '.$row->uFname.' '.$row->uLname;
            } else if ($row->activity_id == 11)
            {
                $activity='Printed ID Card for - '.$row->uCnumber.', '.$row->uFname.' '.$row->uLname;
            } else if ($row->activity_id == 12)
            {
                $activity='Registered new User for - '.$row->uCnumber.', '.$row->uFname.' '.$row->uLname;
            } else if ($row->activity_id == 13)
            {
                $activity='Updated details user for - '.$row->uCnumber.', '.$row->uFname.' '.$row->uLname;
            } else if ($row->activity_id == 14)
            {
                $activity='Registered new Volunteer/Group Home User - '.$row->vghuFname.' '.$row->vghuLname;
            } else if ($row->activity_id == 15)
            {
                $activity='Updated user details for - '.$row->vghuFname.' '.$row->vghuLname;
            } else if ($row->activity_id == 16)
            {
                $activity='Deleted Volunteer/Group Home User - '.$row->vghuFname.' '.$row->vghuLname;
            } else if ($row->activity_id == 17)
            {
                $activity='Signed Up  for Volunteer - '.$row->vghSPFname.' '.$row->vghSPLname;
            } else if ($row->activity_id == 18)
            {
                $activity='Signed Up  for Group Home - '.$row->vghSPFname.' '.$row->vghSPLname;
            } else if ($row->activity_id == 19)
            {
                $activity='Updated Details  for Volunteer - '.$row->vghSPFname.' '.$row->vghSPLname;
            } else if ($row->activity_id == 20)
            {
                $activity='Updated Details  for Group Home - '.$row->vghSPFname.' '.$row->vghSPLname;
            } else if ($row->activity_id == 21)
            {
                $activity='Deleted details for Volunteer - '.$row->vghSPFname.' '.$row->vghSPLname;
            } else if ($row->activity_id == 22)
            {
                $activity='Deleted details for Group Home - '.$row->vghSPFname.' '.$row->vghSPLname;
            }

            return '<span>' . $activity .'</span';
        })
        

        ->rawColumns(['date','time','name','activity_id'])
        ->make(true);

        return $activityLogData;
    }

    return view('admin.tgog_activity_log');
}

public function staffs(Request $request)
{
    $staffs = DB::table('users')
    ->where(['is_active'=>1,'role'=>3])
    ->select('first_name','last_name','id')
    ->get();

    if ($request->ajax()) {
        $staffs = DataTables::of($staffs)
            ->addColumn('f_name', function ($row) {
                return $row->first_name;
            })
            ->addColumn('l_name', function ($row) {
                return $row->last_name;
            })
            ->addColumn('user_name', function ($row) {
                return '<span>' . $activity .'</span';
            })

            ->addColumn('action', function ($row) {

                return
                '<div class="group">
                    <a class="btn btn-sm btn-info text-white m-1 demote" user-id='.$row->id.'>Demote</a>
                </div>';
            })
            ->rawColumns(['f_name','l_name','user_name','action'])
            ->make(true);

        return $staffs;
    }

    return view('admin.tgog_staffs');
}


// get display tickets
public function get_tickets(Request $request){
    $availableTicketsData=DB::table('generated_tickets')
        ->leftJoin('users as ticketGeneratorUser','generated_tickets.user_id','=','ticketGeneratorUser.id')
		->leftJoin('users as generatedByRole','generated_tickets.generated_by','=','generatedByRole.id')
        // ->leftJoin('users as createdByUser','generated_tickets.generated_by','=','generatedByRole.id')
        ->where(['generated_tickets.status'=>1,'generated_tickets.is_active'=>1,'generated_tickets.is_cancelled'=>0,'generated_tickets.is_reset'=>0])
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
        ->selectRaw('generatedByRole.first_name as ticketGeneratorFname')
        ->selectRaw('generatedByRole.last_name as ticketGeneratorLname')
        ->orderBy('generated_tickets.ticket_number','ASC')
        ->get();

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
				

                $isTGOG = ($row->ticketGeneratorRole == 3 || $row->ticketGeneratorRole == 1);
                $isRecipientRole = ($row->ticketGeneratorRole == 2);
                $hasGenerator = ($row->generated_by !== null);
                $hasMultipleId = ($row->multiple_id !== null);
                $isFirst = ($row->is_first == 1);
                $isExtra = ($row->is_extra == 1);
                $isBot = ($row->is_bot == 1);

                // Determine role based on conditions
                if ($isBot) {
                    $roleName = 'Bot';
                } elseif (!$hasGenerator && !$hasMultipleId && $row->ticketGeneratorRole === null) {
                    $roleName = $isFirst ? 'Recipient' : '';
                } elseif ($isTGOG && $isFirst && !$isExtra && !$isBot) 
                {
                    if (session()->has('staffsAdmins')) 
                    {
                        $staffNames = session('staffsAdmins');

                        $staffIds = session('staffsIds');

                        $staff_name = ''.$row->ticketGeneratorFname.' '.$row->ticketGeneratorLname.'';

                        $result = array_filter($staffNames, function($name) use ($staff_name) {
                            return strtolower(trim($name)) === $staff_name;
                        });

                        $staffKey = array_search($staff_name, $staffNames);

                        $staffID = $staffIds[$staffKey];

                        // \Log::info([
                        //     'Staff IDS are ' => $staffIds,
                        // ]);

                        // \Log::info([
                        //     'Staff names are ' => $staffNames,
                        // ]);

                        $count = count($result);

                        if ($count > 1) {

                            if($staffID == $row->generated_by)
                            {
                                $roleName = ''.strtoupper(substr($row->ticketGeneratorFname, 0, 1)).'1'.$row->ticketGeneratorLname.'1';
                            } else {
                                $roleName = ''.strtoupper(substr($row->ticketGeneratorFname, 0, 1)).''.$row->ticketGeneratorLname.'';
                            }

                            
                        } else {
                            $roleName = ''.strtoupper(substr($row->ticketGeneratorFname, 0, 1)).''.$row->ticketGeneratorLname.'';
                        }
                    } 

                    // $roleName = ''.strtoupper(substr($row->ticketGeneratorFname, 0, 1)).''.$row->ticketGeneratorLname.'';
                    
                } elseif ($isRecipientRole && $isFirst && !$isExtra && !$isBot) {
                    $roleName = 'Recipient';
                } else {
                    $roleName = ''; // Default case
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

public function volunteer_group_home_users(Request $request)
{
    $groupHomeUsersData=DB::table('volunteer_group_homes_users')
        ->where(['is_active'=>1])
        ->select(
            'id',
            'first_name',
            'last_name')
        ->orderBy('id','ASC')
        ->get();

        $groupHomeUsersData = DataTables::of($groupHomeUsersData)

            ->addColumn('first_name', function ($row) {
                return $row->first_name;
            })
            ->addColumn('last_name', function ($row) {
                return $row->last_name;
            })
            ->addColumn('action', function ($row) {

                return '<div class="bg-warning datatable-dropdown">
                    <a class="btn btn btn-sm  text-dark update-volunteer-group-user" button-type="edit-user" first-name='.$row->first_name.' last-name='.$row->last_name.'  user-id='.$row->id.' href="#">Edit</a>
                    <div class="dropdown">
                        <button class="btn dropdown">
                            <i class="fa-solid fa-caret-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <a class="dropdown-item text-dark delete-volunteer-group-user" user-id='.$row->id.' href="#">Delete</a>
                        </div>
                    </div>
                </div>';
            })

            ->rawColumns(['first_name','last_name','action'])
            ->make(true);

			//dd($groupHomeUsersData);die();
            return $groupHomeUsersData;
}


public function update_admin_staff_password(Request $request)
{

    $date_created=Carbon::now()->toDateTimeString();

    $user_count= DB::table('users')->where([
        'id' => $request->userID, 
        'is_active' => 1
    ])->count();

    if($user_count>0)
    {
        DB::table('users')
        ->where('id',$request->userID)
        ->update([
            'case_number' => $request->newPwd,
            'updated_at' => $date_created
        ]);

        $msg = 'Password Updated Successfully';

        return response()->json([
            'status' => 200,
            'message' => $msg
        ]);

    } else {
        $msg = 'User Details not Found';

        return response()->json([
            'status' => 404,
            'message' => $msg
        ]);
    }
}

public function manage_volunteer_group_home_users(Request $request)
{
    $data=$request->all();

    $date_created=Carbon::now()->toDateTimeString();

    $rules = [
        'first_name'=>'required',
        'last_name'=>'required',
    ];

    $custommessages = [
        'first_name.required' => 'First Name is required',
        'last_name.required' => 'Last Name is required',
    ];

    $validator = Validator::make($data, $rules, $custommessages);

    if ($validator->fails()) {
        return response()->json([
            'status' => 405,
            'message' => $validator->errors()
        ]);
    } else {

        $user_count= DB::table('volunteer_group_homes_users')->where([
            'first_name' => $data['first_name'], 
            'last_name' => $data['last_name'],
            'is_active' => 1
        ])->count();

        if($user_count>0)
        {
            $msg='User first name and last name exist for another user.';
            return response()->json([
                'status' => 500,
                'message' => $msg
            ]);

        } else {
            if($data['user_id'] == null)
            {
                $userId=DB::table('volunteer_group_homes_users')->insertGetId([
                    'first_name' => $data['first_name'], 
                    'last_name' => $data['last_name'],
                    'created_at' => $date_created,
                    'updated_at' => $date_created
                ]);

                $msg='User registered successfully.';

                DB::table('activity_log')->insertGetId([
                    'staff_id' => Auth::user()->id,
                    'v_g_home_userId' => $userId,
                    'activity_id' => 14,
                    'created_at' => Carbon::now()
                ]);

                return response()->json([
                    'status' => 200,
                    'id'=>$userId,
                    'message' => $msg
                ]);
                
            } else {
                DB::table('volunteer_group_homes_users')
                ->where('id',$data['user_id'])
                ->update([
                    'first_name' => $data['first_name'], 
                    'last_name' => $data['last_name'],
                    'updated_at' => $date_created
                ]);

                DB::table('activity_log')->insertGetId([
                    'staff_id' => Auth::user()->id,
                    'v_g_home_userId' => $data['user_id'],
                    'activity_id' => 15,
                    'created_at' => Carbon::now()
                ]);

                $msg='User updated successfully.';

                // $new_volunteer_signups_count=DB::table('volunteer_group_homes_users')->where(['is_reset'=>0,'is_type'=>1,'status'=>1])->sum('amount');

                // event(new VolunteerGroupHomesUpdated($volunteer_group_homes_data));

                return response()->json([
                    'status' => 200,
                    'message' => $msg
                ]);
            }
        }
    }
}

public function delete_volunteer_group_user(Request $request)
{
    $user_id=$request->userId;

    $selectedUser=DB::table('volunteer_group_homes_users')->where(['id'=>$user_id])->first();

    if($selectedUser)
    {
        DB::table('volunteer_group_homes_users')->where(['id'=>$user_id])->update
        ([
            'is_active'=>0
        ]);

        DB::table('activity_log')->insertGetId([
            'staff_id' => Auth::user()->id,
            'v_g_home_userId' => $user_id,
            'activity_id' => 16,
            'created_at' => Carbon::now()
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'User has been deleted successfully.'
        ]);
    } else {
        return response()->json([
            'status'=>404,
            'message'=>'Details not Found'
        ]);
    }
}





public function volunteer_signups(Request $request)
{
    $volunteerSignups=DB::table('volunteer_group_homes_tickets')
        ->leftJoin('volunteer_group_homes_users','volunteer_group_homes_tickets.user_id','=','volunteer_group_homes_users.id')
        ->where(['status'=>1,'is_type'=>1,'is_reset'=>0])
        ->select(
            'volunteer_group_homes_tickets.id',
            'volunteer_group_homes_tickets.ticket_number',
            'volunteer_group_homes_users.first_name',
            'volunteer_group_homes_users.last_name',
            'volunteer_group_homes_tickets.amount',
            'volunteer_group_homes_tickets.is_served')
        ->orderBy('id','ASC')
        ->get();

        $volunteerSignups = DataTables::of($volunteerSignups)

            ->addColumn('ticket_number', function ($row) {
                return $row->ticket_number;
            })
            ->addColumn('name', function ($row) {
                return 
                '<span>' . $row->first_name .' ' . $row->last_name .'</span';
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
        ->leftJoin('volunteer_group_homes_users','volunteer_group_homes_tickets.user_id','=','volunteer_group_homes_users.id')
        ->where(['status'=>1,'is_type'=>2,'is_reset'=>0])
        ->select(
            'volunteer_group_homes_tickets.id',
            'volunteer_group_homes_tickets.ticket_number',
            'volunteer_group_homes_users.first_name',
            'volunteer_group_homes_users.last_name',
            'volunteer_group_homes_tickets.amount',
            'volunteer_group_homes_tickets.is_served')
        ->orderBy('id','ASC')
        ->get();

        $groupHomeSignupsData = DataTables::of($groupHomeSignupsData)

            ->addColumn('ticket_number', function ($row) {
                return $row->ticket_number;
            })
            ->addColumn('name', function ($row) {
                return 
                '<span>' . $row->first_name .' ' . $row->last_name .'</span';
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




public function get_users_without_tickets()
{
    // get all users ids with tickets
    $usersWithTickets = DB::table('volunteer_group_homes_tickets')
    ->whereDate('volunteer_group_homes_tickets.created_at',Carbon::today())
    ->where(['volunteer_group_homes_tickets.status'=>1,'volunteer_group_homes_tickets.is_reset'=>0])
    ->pluck('user_id');

    $selectedUsers = DB::table('volunteer_group_homes_users')
    ->whereNotIn('id', $usersWithTickets)
    ->where(['is_active'=>1])
    ->select('id','first_name','last_name')
    ->get();

    return response()->json([
        'users_ids'=>$selectedUsers
    ]);
}

public function get_volunteer_group_signup_details($id)
{
    $details=DB::table('volunteer_group_homes_tickets')
    ->leftJoin('volunteer_group_homes_users','volunteer_group_homes_tickets.user_id','=','volunteer_group_homes_users.id')
    ->where(['volunteer_group_homes_tickets.id'=>$id,'volunteer_group_homes_tickets.status'=>1])
    ->select('volunteer_group_homes_tickets.id','volunteer_group_homes_tickets.amount','volunteer_group_homes_users.first_name','volunteer_group_homes_users.last_name','volunteer_group_homes_users.id as userID')
    ->first();

    $usersWithTickets = DB::table('volunteer_group_homes_tickets')
    ->whereDate('volunteer_group_homes_tickets.created_at',Carbon::today())
    ->where(['volunteer_group_homes_tickets.status'=>1,'volunteer_group_homes_tickets.is_reset'=>0])
    ->pluck('user_id');

    $selectedUsers = DB::table('volunteer_group_homes_users')->whereNotIn('id', $usersWithTickets)
    ->where(['is_active'=>1])
    ->select('id','first_name','last_name')
    ->get();

    if($details)
    {
        return response()->json([
            'status'=>200,
            'data'=>$details,
            'users_ids'=>$selectedUsers
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
    $data['user_name']= json_decode($request->user_name);

    $data['amount']= $request->amount;

    if($request->volunteer_group_id == null)
    {
        $rules = [
            'user_name'=>'required',
            'amount'=>'required',
        ];
    
        $custommessages = [
            'user_name.required' => 'Select User',
            'amount.required' => 'Amount is required',
        ];
    } else {
        $rules = [
            'amount'=>'required',
        ];
    
        $custommessages = [
            'amount.required' => 'Amount is required',
        ];
    }

    

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

        if($previousGroupHomeTicketToday == null && $request->type_id=="2")
        {
            $ticket_number=1;
        } else if($previousGroupHomeTicketToday !== null && $request->type_id=="2"){
            $ticket_number=($previousGroupHomeTicketToday->ticket_number) + 1;
        }

        if($previousVolunteerTicketToday == null && $request->type_id=="1")
        {
            $ticket_number=1;
        } else if($previousVolunteerTicketToday !== null && $request->type_id=="1")
        {
            $ticket_number=($previousVolunteerTicketToday->ticket_number) + 1;
        }

        if($request->volunteer_group_id == null)
        {
            $id=DB::table('volunteer_group_homes_tickets')->insertGetId([
                'user_id' => $data['user_name'], 
                'amount' => $data['amount'],
                'is_type' => $request->type_id,
                'is_served' => 1,
                'ticket_number' => $ticket_number,
                'created_at' => $date_created,
                'updated_at' => $date_created
            ]);

            if($request->type_id=="1")
            {
                DB::table('activity_log')->insertGetId([
                    'staff_id' => Auth::user()->id,
                    'v_g_home_id' => $id,
                    'activity_id' => 17,
                    'created_at' => Carbon::now()
                ]);
            } else 
            {
                DB::table('activity_log')->insertGetId([
                    'staff_id' => Auth::user()->id,
                    'v_g_home_id' => $id,
                    'activity_id' => 18,
                    'created_at' => Carbon::now()
                ]);
            }

            $msg='Sign up has been registered successfully.';
        } else {
            DB::table('volunteer_group_homes_tickets')
            ->where('id',$request->volunteer_group_id)
            ->update([
                'amount' => $data['amount'],
                'user_id' => $data['user_name'],
                'updated_at' => $date_created
            ]);

            if($request->type_id=="1")
            {
                DB::table('activity_log')->insertGetId([
                    'staff_id' => Auth::user()->id,
                    'v_g_home_id' => $request->volunteer_group_id,
                    'activity_id' => 19,
                    'created_at' => Carbon::now()
                ]);
            } else 
            {
                DB::table('activity_log')->insertGetId([
                    'staff_id' => Auth::user()->id,
                    'v_g_home_id' => $request->volunteer_group_id,
                    'activity_id' => 20,
                    'created_at' => Carbon::now()
                ]);
            }

            $msg='Sign up has been updated successfully.';
        }

        $volunteer_signups_count=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>1,'status'=>1])->sum('amount');

        $volunteer_signups_served=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>1,'status'=>1,'is_served'=>1])->sum('amount');

        $volunteer_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>1,'status'=>1,'is_served'=>0])->sum('amount');

        

        $group_homes_signups_count=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>2,'status'=>1])->sum('amount');

        $group_homes_signups_served=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>2,'status'=>1,'is_served'=>1])->sum('amount');

        $group_homes_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>2,'status'=>1,'is_served'=>0])->sum('amount');


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

        $ticketsAnalyticsData=generatedTicket::tickets_analytics();

        event(new TicketsAnalytics($ticketsAnalyticsData));

        return response()->json([
            'status' => 200,
            'message' => $msg
        ]);
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

        if($selectedSignup->is_type=="1")
        {
            DB::table('activity_log')->insertGetId([
                'staff_id' => Auth::user()->id,
                'v_g_home_id' => $signup_id,
                'activity_id' => 21,
                'created_at' => Carbon::now()
            ]);
        } else 
        {
            DB::table('activity_log')->insertGetId([
                'staff_id' => Auth::user()->id,
                'v_g_home_id' => $signup_id,
                'activity_id' => 22,
                'created_at' => Carbon::now()
            ]);
        }

        $user=DB::table('volunteer_group_homes_users')->where(['id'=>$selectedSignup->user_id])->first();

        $ticketsAnalyticsData=generatedTicket::tickets_analytics();

        event(new TicketsAnalytics($ticketsAnalyticsData));

        $volunteer_signups_count=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>1,'status'=>1])->sum('amount');

        $volunteer_signups_served=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>1,'status'=>1,'is_served'=>1])->sum('amount');

        $volunteer_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>1,'status'=>1,'is_served'=>0])->sum('amount');

        

        $group_homes_signups_count=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>2,'status'=>1])->sum('amount');

        $group_homes_signups_served=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>2,'status'=>1,'is_served'=>1])->sum('amount');

        $group_homes_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>2,'status'=>1,'is_served'=>0])->sum('amount');


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

        // $volunteer_signups_count=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>1,'status'=>1])->sum('amount');

        // $volunteer_signups_served=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>1,'status'=>1,'is_served'=>1])->sum('amount');

        // $volunteer_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>1,'status'=>1,'is_served'=>0])->sum('amount');

        

        // $group_homes_signups_count=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>2,'status'=>1])->sum('amount');

        // $group_homes_signups_served=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>2,'status'=>1,'is_served'=>1])->sum('amount');

        // $group_homes_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>2,'status'=>1,'is_served'=>0])->sum('amount');

        return response()->json([
            'status' => 200,
            'message' => 'Sign up has been deleted successfully.',
            'user'=>$user
            // 'volunteer_signups_count' => $volunteer_signups_count,
            // 'volunteer_signups_served' => $volunteer_signups_served,
            // 'volunteer_signups_unserved' => $volunteer_signups_unserved,
            // 'group_homes_signups_count' => $group_homes_signups_count,
            // 'group_homes_signups_served' => $group_homes_signups_served,
            // 'group_homes_signups_unserved' => $group_homes_signups_unserved
        ]);

        // return response()->json([
        //     'status'=>200
        // ]);
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

        $volunteer_signups_count=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>1,'status'=>1])->sum('amount');

        $volunteer_signups_served=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>1,'status'=>1,'is_served'=>1])->sum('amount');

        $volunteer_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>1,'status'=>1,'is_served'=>0])->sum('amount');

        

        $group_homes_signups_count=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>2,'status'=>1])->sum('amount');

        $group_homes_signups_served=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>2,'status'=>1,'is_served'=>1])->sum('amount');

        $group_homes_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>2,'status'=>1,'is_served'=>0])->sum('amount');


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

        $volunteer_signups_count=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>1,'status'=>1])->sum('amount');

        $volunteer_signups_served=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>1,'status'=>1,'is_served'=>1])->sum('amount');

        $volunteer_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>1,'status'=>1,'is_served'=>0])->sum('amount');

        

        $group_homes_signups_count=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>2,'status'=>1])->sum('amount');

        $group_homes_signups_served=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>2,'status'=>1,'is_served'=>1])->sum('amount');

        $group_homes_signups_unserved=DB::table('volunteer_group_homes_tickets')->where(['is_reset'=>0,'is_type'=>2,'status'=>1,'is_served'=>0])->sum('amount');


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
    $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));

    $todaysDate=$dateObj->format('m/d/Y h:i:s');

    $currentTicketsData=DB::table('generated_tickets')
        ->leftJoin('users','generated_tickets.user_id','=','users.id')
        // ->whereDate('generated_tickets.created_at', Carbon::today())
        ->where(['generated_tickets.status'=>1,'generated_tickets.is_active'=>1,'generated_tickets.is_cancelled'=>0,'generated_tickets.is_reset'=>0])
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

        DB::table('activity_log')->insertGetId([
            'staff_id' => Auth::user()->id,
            'ticket_id' => $ticket_id,
            'activity_id' => 3,
            'created_at' => Carbon::now()
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
    $ticket_id=$request->checkedInTicketId;

    $selectedTicket=generatedTicket::where(['id'=>$ticket_id])->whereDate('created_at', Carbon::today())->first();

    // check if the ticket belongs to a admin
    $ticketsId=$selectedTicket->multiple_id;

    // dd($selectedTicket->checked_in,$selectedTicket->is_first,$selectedTicket->multiple_id,$ticketsId);die();
    
    if($selectedTicket->checked_in == 1)
    {
        DB::table('activity_log')->insertGetId([
            'staff_id' => Auth::user()->id,
            'ticket_id' => $ticket_id,
            'activity_id' => 7,
            'created_at' => Carbon::now()
        ]);

        if($selectedTicket->is_first == 1 && $selectedTicket->multiple_id == $ticketsId && $selectedTicket->multiple_id !== null)
        {

            generatedTicket::where(['multiple_id'=>$ticketsId])->update(['checked_in'=>0]);

            $ticketsIdsArray=generatedTicket::where(['multiple_id'=>$ticketsId])->pluck('id')->toArray();

            $checkedInTicket=$selectedTicket->id;

            $ticketsAnalyticsData=generatedTicket::tickets_analytics();

            event(new TicketsAnalytics($ticketsAnalyticsData));
            
            $uncheckedTicketsData=generatedTicket::not_checkedin_tickets($checkedInTicket);

            event(new UncheckedInTickets($uncheckedTicketsData));

            return response()->json([
                'is_status'=>0,
                'ticketsIds'=>$ticketsIdsArray
            ]);
            
        } 
        else if($selectedTicket->is_first == 1 && $selectedTicket->multiple_id == $ticketsId &&  $selectedTicket->multiple_id == null) {

            $selectedTicket->update
            ([
                'checked_in'=>0
            ]);

            $checkedInTicket=$selectedTicket->id;

            $ticketsAnalyticsData=generatedTicket::tickets_analytics();

            event(new TicketsAnalytics($ticketsAnalyticsData));
            
            $uncheckedTicketsData=generatedTicket::not_checkedin_tickets($checkedInTicket);

            event(new UncheckedInTickets($uncheckedTicketsData));

            return response()->json([
                'is_status'=>0,
                'ticketsIds'=>null
            ]);
        } 
        else if($selectedTicket->is_first == 0 && $selectedTicket->multiple_id == $ticketsId &&  $selectedTicket->multiple_id !== null) {

            $selectedTicket->update
            ([
                'checked_in'=>0
            ]);

            $checkedInTicket=$selectedTicket->id;

            $ticketsAnalyticsData=generatedTicket::tickets_analytics();

            event(new TicketsAnalytics($ticketsAnalyticsData));
            
            $uncheckedTicketsData=generatedTicket::not_checkedin_tickets($checkedInTicket);

            event(new UncheckedInTickets($uncheckedTicketsData));

            return response()->json([
                'is_status'=>0,
                'ticketsIds'=>null
            ]);
        }
    } else if($selectedTicket->checked_in == 0)
    {
        DB::table('activity_log')->insertGetId([
            'staff_id' => Auth::user()->id,
            'ticket_id' => $ticket_id,
            'activity_id' => 6,
            'created_at' => Carbon::now()
        ]);

        if($selectedTicket->is_first == 1 &&  $selectedTicket->multiple_id == $ticketsId && $selectedTicket->multiple_id !== null)
        {

            generatedTicket::where(['multiple_id'=>$ticketsId])->update(['checked_in'=>1]);

            $ticketsIdsArray=generatedTicket::where(['multiple_id'=>$ticketsId])->pluck('id')->toArray();

            $checkedInTicket=$selectedTicket->id;

            $ticketsAnalyticsData=generatedTicket::tickets_analytics();

            event(new TicketsAnalytics($ticketsAnalyticsData));
            
            $uncheckedTicketsData=generatedTicket::not_checkedin_tickets($checkedInTicket);

            event(new UncheckedInTickets($uncheckedTicketsData));

            return response()->json([
                'is_status'=>1,
                'ticketsIds'=>$ticketsIdsArray
            ]);
            
        } else if($selectedTicket->is_first == 1 && $selectedTicket->multiple_id !== $ticketsId &&  $selectedTicket->multiple_id == null) {

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
        } else if($selectedTicket->is_first == 1 && $selectedTicket->multiple_id == $ticketsId &&  $selectedTicket->multiple_id == null) {

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
        } else if($selectedTicket->is_first == 0 && $selectedTicket->multiple_id == $ticketsId &&  $selectedTicket->multiple_id !== null)
        {

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
    }
}

public function reset_current_ticket(Request $request)
{

    DB::table('tickets_management')->update(['ticket_number'=>0,'updated_at'=>Carbon::now()->toDateTimeString()]);
    
    return response()->json([
        'status'=>200
    ]);
}


Public function number_control()
{
    
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
    ->where(['is_cancelled'=>0,'status'=>1,'is_reset'=>0])
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
        ->where('is_reset',0)
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
                        $id_card = $request->case_number.' - '.rand(111,9999).'.'.$extension;
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

            DB::table('activity_log')->insertGetId([
				'staff_id' => Auth::user()->id,
				'user_id' => $user->id,
				'activity_id' => 12,
				'created_at' => Carbon::now()
			]);
            
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
