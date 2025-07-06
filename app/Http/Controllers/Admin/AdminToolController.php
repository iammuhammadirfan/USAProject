<?php

namespace App\Http\Controllers\Admin;

use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\Memo;
use App\Models\User;
use Illuminate\Support\Str;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use App\Models\loginDaysTime;
use App\Models\ticketReturnTime;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\notificationMessage;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class AdminToolController extends Controller
{
    Public function index(){

         $users = User::all();
         $status = $users->contains('is_active', 1) ? 'Enabled' : 'Disabled';

        //  get active login days
        $activeLoginDays=DB::table('login_days_time')->where('login_days_time.is_active',1)->pluck('login_days_time.day')->toArray();

        $activeDates=DB::table('login_days_time')->where('login_days_time.status',1)->pluck('login_days_time.date')->toArray();

        $checkUserLoginStatusArray=DB::table('users')
                                ->where('users.role',2)
                                ->where('users.is_enabled',1)
                                ->pluck('users.id')
                                ->toArray();

        if($checkUserLoginStatusArray)
        {
            $loginStatus=1;
        } else {
            $loginStatus=0;
        }

        return view('admin.admin_tool', compact('users','status','activeLoginDays','activeDates','loginStatus'));
    }

    Public function password_db_mgt(){

        return view('admin.tgog_password_db_mgt');
    }

    Public function memos(){

        return view('admin.tgog_memos');
    }

    Public function login_days(){
        return view('admin.tgog_login_days');
    }
    
    Public function notification_messages(){
        return view('admin.tgog_notification_msgs');
    }
    
    Public function ticket_limit(){
        return view('admin.tgog_ticket_limit');
    }
    
    Public function return_times(){
        return view('admin.tgog_return_times');
    }
    
    Public function distribution_times(){

        return view('admin.tgog_distribution_time');
    }

    Public function all_signups()
    {
        $staffsAdmins=DB::table('users')
        ->where('status',1)
        ->where('role','!=',2)
        ->select('id','first_name','last_name')
        ->get();

        $staffNames = [];

        $staffIds = [];

        foreach($staffsAdmins as $name)
        {
            $staffNames[]=$name->first_name.' '.$name->last_name;
            $staffIds[] = $name->id;
        }

        session()->forget('staffsAdmins');
        session()->forget('staffsIds');

        session()->put('staffsAdmins', $staffNames);

        session()->put('staffsIds', $staffIds);

        return view('admin.tgog_all_signups');
    }

    Public function no_shows()
    {
        return view('admin.tgog_no_shows');
    }

    Public function number_control()
    {
        return view('admin.tgog_number_control');
    }

    Public function admin_dashboard(Request $request)
    {

        $admin_memo = DB::table('memos')->where(['status'=>1,'is_type'=>0,'is_enabled'=>1])->pluck('message')->first();



        if($admin_memo)
        {
            $truncatedMemo=Str::limit(strip_tags($admin_memo), 1000);
        } else {
            $truncatedMemo='';
        }

        return view('admin.tgog_admin_dashboard',compact('truncatedMemo'));
    }

    public function overview_dashboard()
    {
        $ticketBeingServed = DB::table('tickets_management')
        ->pluck('ticket_number')->first();

        $ticketsData=DB::table('generated_tickets')
            ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'is_reset'=>0]);

        $notCheckedInTicketsCount=count(DB::table('generated_tickets')
        // ->whereDate('created_at', Carbon::today())
        ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'is_reset'=>0,'checked_in'=>0])->get());

        $allTicketsCount=count($ticketsData->get());

        $checkedInTicketsCount=count($ticketsData->where('generated_tickets.checked_in','=',1)->get());

        $volunteer_served_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>1,'status'=>1,'is_served'=>1,'is_reset'=>0])->sum('amount');
        $group_homes_served_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>2,'status'=>1,'is_served'=>1,'is_reset'=>0])->sum('amount');

        $volunteer_unserved_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>1,'status'=>1,'is_served'=>0,'is_reset'=>0])->sum('amount');
        $group_homes_unserved_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>2,'status'=>1,'is_served'=>0,'is_reset'=>0])->sum('amount');

        $totalVolunteerGroupHomeServed=$volunteer_served_count+$group_homes_served_count;

        $totalVolunteerGroupHomeunServed=$volunteer_unserved_count+$group_homes_unserved_count;

        $totalVolunteerGroupHomeSignups=$totalVolunteerGroupHomeServed+$totalVolunteerGroupHomeunServed;

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


        return view('admin.tgog_overview_dashboard',compact('allTicketsCount','currentNoShows','currentTicketsRemaining','checkedInTicketsCount','notCheckedInTicketsCount','totalVolunteerGroupHomeSignups','totalVolunteerGroupHomeunServed','totalVolunteerGroupHomeServed'));
    }

    public function tgog_ticket_signups_dashboard()
    {
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


        return view('admin.tgog_ticket_signups_dashboard',compact('allTicketsCount','currentNoShows','currentTicketsRemaining','checkedInTicketsCount','notCheckedInTicketsCount'));
    }

    public function volunteer_group_dashboard()
    {
        $ticketBeingServed = DB::table('tickets_management')
        ->pluck('ticket_number')->first();

        $ticketsData=DB::table('generated_tickets')
            ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'is_reset'=>0]);

        $notCheckedInTicketsCount=count(DB::table('generated_tickets')
        // ->whereDate('created_at', Carbon::today())
        ->where(['status'=>1,'is_active'=>1,'is_cancelled'=>0,'is_reset'=>0,'checked_in'=>0])->get());

        $allTicketsCount=count($ticketsData->get());

        $checkedInTicketsCount=count($ticketsData->where('generated_tickets.checked_in','=',1)->get());

        $volunteer_served_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>1,'status'=>1,'is_served'=>1,'is_reset'=>0])->sum('amount');
        $group_homes_served_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>2,'status'=>1,'is_served'=>1,'is_reset'=>0])->sum('amount');

        $volunteer_unserved_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>1,'status'=>1,'is_served'=>0,'is_reset'=>0])->sum('amount');
        $group_homes_unserved_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>2,'status'=>1,'is_served'=>0,'is_reset'=>0])->sum('amount');

        $totalVolunteerGroupHomeServed=$volunteer_served_count+$group_homes_served_count;

        $totalVolunteerGroupHomeunServed=$volunteer_unserved_count+$group_homes_unserved_count;

        $totalVolunteerGroupHomeSignups=$totalVolunteerGroupHomeServed+$totalVolunteerGroupHomeunServed;

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


        return view('admin.tgog_volunteer_group',compact('allTicketsCount','currentNoShows','currentTicketsRemaining','checkedInTicketsCount','notCheckedInTicketsCount','totalVolunteerGroupHomeSignups','totalVolunteerGroupHomeunServed','totalVolunteerGroupHomeServed'));
    }

    public function get_single_ticket()
    {
        return view('admin.tgog_get_single_tickets');
    }

    public function multiple_ticket_details($id)
    {
        $userDetails=DB::table('users')
        ->select('users.id','users.first_name','users.last_name','users.proxy','users.case_number','users.status','users.role')
        // ->where('users.status','=',0)
        ->where('users.id','=',$id)
        ->first();

        return view('admin.tgog_multiple_tickets_details',compact('userDetails'));
    }

    public function volunteer_signups(Request $request)
    {
        $volunteer_signups_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>1,'status'=>1,'is_reset'=>0])->sum('amount');
        $volunteer_served_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>1,'status'=>1,'is_served'=>1,'is_reset'=>0])->sum('amount');
        $volunteer_unserved_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>1,'status'=>1,'is_served'=>0,'is_reset'=>0])->sum('amount');
        // $group_homes_signups_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>2,'status'=>1,'is_reset'=>0])->sum('amount');

        
        // $group_homes_served_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>2,'status'=>1,'is_served'=>1,'is_reset'=>0])->sum('amount');

        
        // $group_homes_unserved_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>2,'status'=>1,'is_served'=>0,'is_reset'=>0])->sum('amount');

        return view('admin.tgog_volunteer_signups',compact('volunteer_signups_count','volunteer_served_count','volunteer_unserved_count'));
    }

    public function group_signups(Request $request)
    {
        $group_homes_served_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>2,'status'=>1,'is_served'=>1,'is_reset'=>0])->sum('amount');
        $group_homes_signups_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>2,'status'=>1,'is_reset'=>0])->sum('amount');
        $group_homes_unserved_count=DB::table('volunteer_group_homes_tickets')->where(['is_type'=>2,'status'=>1,'is_served'=>0,'is_reset'=>0])->sum('amount');

        return view('admin.tgog_group_signups',compact('group_homes_signups_count','group_homes_served_count','group_homes_unserved_count'));
    }


    Public function register_new_user(){

        return view('admin.tgog_register_user');
    }

    Public function search_users(Request $request){

        if ($request->ajax()) {
			
			if($request->fname == null && $request->lname == null && $request->case_number == null && $request->date_of_birth == null && $request->page_reload_checker == 1)
			{
				return response()->json([
					'status' => 500
				]);
			}
			
			$searchedUserResults=DB::table('users')
				->where('status','=',1)
				->select('first_name','last_name','date_of_birth','proxy','id','case_number','id_card');
		
			if($request->fname !== null)
			{
				$searchedUserResults->where('first_name','like','%'.$request->fname.'%');
			}
			
			if($request->lname !== null)
			{
				$searchedUserResults->where('last_name','like','%'.$request->lname.'%');
			}
			
			if($request->case_number !== null)
			{
				$searchedUserResults->where('case_number','like','%'.$request->case_number.'%');
			}
			
			if($request->date_of_birth !== null)
			{
				$dob=Carbon::parse($request->date_of_birth)->format('Y-m-d');
				
				$searchedUserResults->where('date_of_birth','=',$dob);
			}
			
			$searchedUserResults->orderBy('first_name','ASC')->get();
		
            $activeUsers = DataTables::of($searchedUserResults)

                ->addColumn('first_name', function ($row) {
                    return $row->first_name;
                })
				->addColumn('last_name', function ($row) {
                    return $row->last_name;
                })
				->addColumn('case_number', function ($row) {
                    return $row->case_number;
                })
				->addColumn('date_of_birth', function ($row) {
					$dob=Carbon::parse($row->date_of_birth)->format('d-m-Y');
					
                    return $dob;
                })
				->addColumn('proxy', function ($row) {
                    return $row->proxy;
                })
				->addColumn('action', function ($row) {

					if($row->id_card !== null)
					{
						$d_print="d-block";
					} else {
						$d_print="d-none";
					}
                    return '<div class="bg-warning datatable-dropdown">
                        <a class="btn btn btn-sm  text-dark update-user-details" user-id='.$row->id.' href="#">User Details</a>
                         <div class="dropdown">
                            <button class="btn dropdown">
                                <i class="fa-solid fa-caret-down"></i>
                            </button>
                            <div class="dropdown-content">
                                <a class="dropdown-item text-dark get-single-ticket" user-id='.$row->id.' href="#">Get Single Ticket</a>
                                <a class="dropdown-item text-dark get-multiple-tickets" user-id='.$row->id.'>Get Multiple Tickets</a>
								<a class="dropdown-item text-dark print-id-card '.$d_print.'" user-id='.$row->id.' href="#">Print ID Card</a>
                            </div>
                        </div>
                    </div>';
                })
                ->rawColumns(['first_name','last_name','case_number','date_of_birth'.'proxy','action'])
                ->make(true);

            return $activeUsers;
        }
		
        return view('admin.tgog_modify_users');
    }

    public function view_user(Request $request,$id)
	{
		$userSignUps = DB::table('generated_tickets')
		->select('created_at','ticket_number','multiple_id','is_cancelled','checked_in')
		->where(['user_id'=>$id,'is_active'=>1])
		->orderBy('created_at')
		->get();

		if($request->ajax())
		{
			$totalSignups = DataTables::of($userSignUps)
	
				->addColumn('date', function ($row) {
					return 
					'<span>' . date('d/m/Y h:i:s A', strtotime($row->created_at)) .'</span';
				})
				->addColumn('ticket_number', function ($row) {
					return 
					'<span>' . $row->ticket_number .'</span';
				})
				->addColumn('type', function ($row) {

					if($row->multiple_id == null)
					{
						$type='Single';
	
					} else {
						$type='Multiple';
					}

					return '<span>' . $type .'</span';
				})
				->addColumn('checked_status', function ($row) {

					if($row->is_cancelled == 0 && $row->checked_in == 1)
					{
						$checked_status='Checked In';
	
					} else {
						$checked_status='Not Checked In';
					}

					return '<span>' .$checked_status.'</span';
				})
				->addColumn('cancelled_status', function ($row) {

					if($row->is_cancelled == 1)
					{
						$cancelled_status='Cancelled';
	
					} else {
						$cancelled_status='Not Cancelled';
					}

					return '<span>' . $cancelled_status .'</span';
				})
				->rawColumns(['date','ticket_number','type','checked_status','cancelled_status'])
				->make(true);

				return $totalSignups;

		}

		return view('admin.tgog_view_user');
	}



    public function export() 
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }


    public function importUser(Request $request) 
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        Excel::import(new UsersImport, $request->file('file'));

        return back();
    }
    
    
   public function distributionStartTime(Request $request)
{
    $distribution = DB::table('distribution_start_times')->where('id', 1)->first();
    
    if($distribution)
    {
        DB::table('distribution_start_times')->where('id', 1)->update(['default_time' => $request->start_time]);
        
        return back()->with('success', 'Updated Distribution Start Time successfully.');
    }
    else
    {
        DB::table('distribution_start_times')->insert(['id' => 1, 'default_time' => $request->start_time]);
        
        return back()->with('success', 'Added Distribution Start Time successfully.');
    }
}

//  public function IntervalTime(Request $request)
// {
//     $distribution = DB::table('interval_times')->where('id', 1)->first();
    
//     if($distribution)
//     {
//         DB::table('interval_times')->where('id', 1)->update(['default_time' => $request->interval_time]);
        
//         return back()->with('success', 'Updated Interval Time successfully.');
//     }
//     else
//     {
//         DB::table('interval_times')->insert(['id' => 1, 'default_time' => $request->interval_time]);
        
//         return back()->with('success', 'Added Interval Time successfully.');
//     }
// }

     
    public function updateCheckInStatus($id)
{
    $user = User::findOrFail($id);
    $newStatus = $user->status == 1 ? 2 : 1; // If checked_in is 1, set to 2; if checked_in is 0, set to 1
    
    $affected = DB::table('users')
        ->where('id', $id)
        ->update(['status' => $newStatus]);

    if ($affected) {
        return response()->json(['status' => 'success', 'message' => 'Check-in status updated successfully']);
    } else {
        return response()->json(['status' => 'error', 'message' => 'Failed to update check-in status']);
    }
}

public function manage_user_login(Request $request)
{
    // Find users with role=2

    $users = User::where('role', 2)->get();

    if($request->status == 0)
    {
        $message="Login disabled Successfully";
        $isenabled=0;

    } else if ($request->status == 1)
    {
        $message="Login enabled Successfully";
        $isenabled=1;
    }

    foreach ($users as $user) {
        $user->is_enabled = $isenabled;
        $user->save();
    }

    return response()->json([
        'status'=>200,
        'message'=>$message,
        'is_enabled'=>$isenabled
    ]);
}

public function enableuser_login()
{
    // Find users with role=2
    $users = User::where('role', 2)->get();

    // Array to store messages
    $messages = [];

    // Update is_enabled status for each user and store the message
    foreach ($users as $user) {
        $user->is_enabled = 1;
        $user->save();
        $messages[] = 'Users are enabled ';
    }

    // Check if any users were affected
    $affectedCount = count($messages);

    if ($affectedCount > 0) {
        foreach ($messages as $message) {
            session()->flash('success', $message);
        }
    } else {
        session()->flash('error', 'No users found');
    }

    return back();
}

public function disableuser_login(){
    $users = User::where('role', 2)->get();

    // Array to store messages
    $messages = [];

    // Update is_enabled status for each user and store the message
    foreach ($users as $user) {
        $user->is_enabled = 2;
        $user->save();
        $messages[] = 'Users are Disabled ';
    }

    // Check if any users were affected
    $affectedCount = count($messages);

    if ($affectedCount > 0) {
        foreach ($messages as $message) {
            session()->flash('success', $message);
        }
    } else {
        session()->flash('error', 'No users found with role=2');
    }

    return back();
    
}

// public function storeData(Request $request)
// {
    
//     $que_number=$request->que_number;

// if ($que_number > 3) {
    
//    return back()->with('message', 'You can\'t store a value greater than 3.');

//     }
    
//     DB::table('queue_numbers')->updateOrInsert(
//         ['id' => 1], // Assuming the record ID is always 1
//         ['queue_number' => $que_number]
//     );
    
//                         return back()->with('message', 'Token value stored successfully.');

// }

// public function updateServingNumber(Request $request)
// {
   
//     $servingNumber = $request->input('servingNumber');
//     \DB::table('queue_numbers')->where('id', 1)->update(['queue_number' => $servingNumber]);
//     return response()->json(['success' => true]);
// }
// public function register_user(Request $request){
//     $last_name = $request->input('last_name');
//     $first_name = $request->input('first_name');
//     $date_of_birth = $request->input('date_of_birth');
//     $case_number = 'C' . Str::random(6); // Generate a random string of length 6 and prepend 'C'

//     DB::table('users')->insert([
//         'last_name' => $last_name,
//         'first_name' => $first_name,
//         'date_of_birth' => $date_of_birth,
//         'case_number' => $case_number,
//     ]);

//     // Flash a success message to the session
//     return redirect()->back()->with('success', 'User registered successfully. Case Number: ' . $case_number);
//}

// show all memos added by admin
public function getMemos(Request $request)
{
    $memos = Memo::where(['status'=>1])->orderBy('created_at','DESC');

    if ($request->ajax()) {
        $memos = DataTables::of($memos)
        
            // ->addColumn('title', function ($row) {
            //     return '<span>'.$row->title.'</span>';
            // })

            ->addColumn('message', function ($row) {
                $truncatedText=Str::limit(strip_tags($row->message), 100);
                
                return '<span>' . $truncatedText . '</span>';
            })
            ->addColumn('is_type', function ($row) {
                if($row->is_type == 0)
                {
                    $type="Admin";
                } else {
                    $type="Users";
                }
                return '<span>' . $type . '</span>';
            })
            ->addColumn('is_enabled', function ($row) {
                if($row->is_enabled == 0)
                {
                    $isEnabled="No";
                } else {
                    $isEnabled="Yes";
                }
                return '<span>' . $isEnabled . '</span>';
            })

            ->addColumn('action', function ($row) {
                if($row->is_enabled == 0)
                {
                    $isEnabledText="Enable";
                } else {
                    $isEnabledText="Disable";
                }

                return
                '<div class="group">
                    <a class="btn btn-sm btn-info text-white m-1 memo-status-btn" memo-id='.$row->id.' memo-isenabled='.$row->is_enabled.'>'.$isEnabledText.'</a>
                    <a class="btn btn-sm btn-info text-white m-1 edit-memo" memo-id='.$row->id.'>Edit</a>
                    <a class="btn btn-sm btn-info text-white m-1 view-memo" memo-id='.$row->id.'>View</a>
                </div>';
            })
            ->rawColumns(['message','is_type','is_enabled','action'])
            ->make(true);

        return $memos;
    }
}
public function enable_disable_memo(Request $request)
{
    $memo_id=$request->memo_id;

    // disable current enabled memo
    // $checkCurrentEnabledMemo=Memo::where(['is_enabled'=>1])->first();

    $selectedMemo=Memo::where(['id'=>$memo_id])->first();

    $isType = $selectedMemo->is_type;

    if($selectedMemo)
    {
        if($selectedMemo->is_enabled == 1)
        {
            Memo::where(['is_type'=>$isType])->update
            ([
                'is_enabled'=>0
            ]);

            $selectedMemo->update
            ([
                'is_enabled'=>0
            ]);

            return response()->json([
                'status'=>200,
                'msg'=>'Memo Disabled Successfully.'
            ]);
        } else {

            Memo::where(['is_type'=>$isType])->update
            ([
                'is_enabled'=>0
            ]);

            // $selectedMemo->update
            // ([
            //     'is_enabled'=>0
            // ]);

            $selectedMemo->update
            ([
                'is_enabled'=>1
            ]);

            return response()->json([
                'status'=>200,
                'msg'=>'Memo Enabled Successfully.'
            ]);
        }

    } else {
        $selectedMemo->update
        ([
            'is_enabled'=>1
        ]);

        return response()->json([
            'status'=>200,
            'msg'=>'Memo Enabled Successfully.'
        ]);
    }
}

public function get_memo_details($id)
{
    $memoDetails=Memo::find($id);

    if($memoDetails)
    {
        return response()->json([
            'status'=>200,
            'data'=>$memoDetails
        ]);
    } else {
        return response()->json([
            'status'=>404,
            'msg'=>'Details Not Found'
        ]);
    }
}

public function create_update_memo(Request $request)
{
    $data=$request->all();

    $rules = [
        'is_type' => 'required',
        'message' => 'required'
    ];

    $custommessages = [
        'message.required' => 'Message field is required',
        'is_type.required' => 'Memo intended to field is required',
    ];

    $validator = Validator::make($data, $rules, $custommessages);

    if ($validator->fails()) {
        return response()->json([
            'status' => 405,
            'message' => $validator->errors()
        ]);
    } else {

        // if($data['title'])
        // {
        //     $title=$data['title'];
        // } else {
        //     $title='';
        // }
        if($request->memo_id)
        {
            $memo=Memo::find($request->memo_id);

            $memo->update([
                $memo->is_type=$data['is_type'],
                $memo->message=$data['message']
            ]);
            
            $message="Memo Details Updated Successfully";

            return response()->json([
                'status'=>200,
                'message'=>$message
            ]);
        }else{

            $memocount=memo::where('message',$data['message'])->count();
            if($memocount>0){
                $message="The message exists in the records.Kindly modify it";
                return response()->json([
                    'status'=>400,
                    'message'=>$message
                ]);
            }else{

                $memo=new memo();
                $memo->is_type=$data['is_type'];
                $memo->message=$data['message'];
                $memo->save();

                $message="Memo Details Registered Successfully";

                return response()->json([
                    'status'=>200,
                    'message'=>$message
                ]);
            }
        }
    }
}

// update text messages
public function get_notificationmsgs(Request $request)
{
    $notificationmsgs = notificationMessage::where(['status'=>1])->orderBy('title','DESC');

    $notificationmsgs = DataTables::of($notificationmsgs)
        
    ->addColumn('title', function ($row) {
        return '<span>'.$row->title.'</span>';
    })

    ->addColumn('type', function ($row) {
        if($row->is_type == '1')
        {
            $type = 'Admins and Users';
        } else {
            $type = 'Admins only';
        }
        return '<span>'.$type.'</span>';
    })

    ->addColumn('message', function ($row) {
        $truncatedText=Str::limit(strip_tags($row->message), 100);
        
        return '<span>' . $truncatedText . '</span>';
    })

    ->addColumn('action', function ($row) {
        return
        '<div class="group">
            <a class="btn btn-sm btn-info text-white m-1 edit-notificationmsg" notificationmsg-id='.$row->id.'>Edit</a>
            <a class="btn btn-sm btn-info text-white m-1 view-notificationmsg" notificationmsg-id='.$row->id.'>View</a>
        </div>';
    })
    ->rawColumns(['title','type','message','action'])
    ->make(true);

    return $notificationmsgs;
}

public function get_notification_msgs_details($id)
{
    $notificationMsgDetails=notificationMessage::find($id);

    if($notificationMsgDetails)
    {
        return response()->json([
            'status'=>200,
            'data'=>$notificationMsgDetails
        ]);
    } else {
        return response()->json([
            'status'=>404,
            'msg'=>'Details Not Found'
        ]);
    }
}

public function update_notification_msgs(Request $request)
{
    $data=$request->all();

    $rules = [
        'message' => 'required'
    ];

    $custommessages = [
        'message.required' => 'Message field is required',
    ];

    $validator = Validator::make($data, $rules, $custommessages);

    if ($validator->fails()) {
        return response()->json([
            'status' => 405,
            'message' => $validator->errors()
        ]);
    } else {

        $notificationMsgCount=notificationMessage::where('message',$data['message'])->count();
        if($notificationMsgCount>0){
            $message="The message exists in the records.Kindly modify it";
            return response()->json([
                'status'=>400,
                'message'=>$message
            ]);
        }

        if($request->notification_msg_id)
        {
            $notificationMessage=notificationMessage::find($request->notification_msg_id);

            $notificationMessage->update([
                $notificationMessage->message=$data['message']
            ]);
            
            $message="Notification Message Details Updated Successfully";

            return response()->json([
                'status'=>200,
                'message'=>$message
            ]);
        }
    }
}

// manage login days
public function getLoginDays(Request $request)
{
    $logindays = DB::table('login_days_time')
    ->where(['is_active'=>1,'status'=>1])
    ->whereNotNull('day')
    ->select('day','start_time','end_time','id')
    ->orderBy('day','DESC')
    ->get();

    if ($request->ajax()) {
        $logindays = DataTables::of($logindays)
        
            ->addColumn('day', function ($row) {
                return '<span>'.ucfirst($row->day).'</span>';
            })

            ->addColumn('start_time', function ($row) {
                return '<span>' . $row->start_time . '</span>';
            })
            ->addColumn('end_time', function ($row) {
                return '<span>' . $row->end_time . '</span>';
            })

            ->addColumn('action', function ($row) {
                return
                '<div class="group">
                    <a class="btn btn-xs btn-info text-white m-1 edit-day" day-id='.$row->id.'>Edit</a>
                    
                </div>';
            })
            ->rawColumns(['day','start_time','end_time','action'])
            ->make(true);

            // <a class="btn btn-xs btn-info text-white m-1 delete-day" day-id='.$row->id.'>Delete</a>

        return $logindays;
    }
}

public function manage_ticket_return_times(Request $request)
{
    DB::table('tickets_management')->update(['return_times_status'=>$request->return_times_status]);
    
    if($request->return_times_status == 0)
    {
        $msg = 'Return times on tickets disabled successfully';
    } else {
        $msg = 'Return times on tickets enabled successfully';
    }
    return response()->json([
        'message'=>$msg
    ]);
}

public function create_update_login_day(Request $request)
{
    $data=$request->all();

    if($request->current_day_id !== null)
    {
        $rules = [
            'selected_day_id' => 'required',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time'
        ];
    
        $custommessages = [
            'selected_day_id.required' => 'Day field is required',
            'start_time.required' => 'Start time field is required',
            'end_time.required' => 'End time field is required',
            'end_time.after' => 'End time field should be greater than start time'
        ];
    } else {
        $rules = [
            'start_time' => 'required',
            'end_time' => 'required|after:start_time'
        ];
    
        $custommessages = [
            'start_time.required' => 'Start time field is required',
            'end_time.required' => 'End time field is required',
            'end_time.after' => 'End time field should be greater than start time'
        ];
    }
    

    $validator = Validator::make($data, $rules, $custommessages);

    if ($validator->fails()) {
        return response()->json([
            'status' => 405,
            'message' => $validator->errors()
        ]);
    } else {
        if($request->current_day_id)
        {
            $current_day=loginDaysTime::find($request->current_day_id);

            $current_day->update([
                $current_day->day=$request->selected_day_id,
                $current_day->start_time=$data['start_time'],
                $current_day->end_time=$data['end_time']
            ]);
            
            $message="Details Updated Successfully";

            return response()->json([
                'status'=>200,
                'message'=>$message
            ]);
        }else{

            loginDaysTime::where('is_active', '=', 1)->update(['is_active' => 0]);

                $loginday=new loginDaysTime();
                $loginday->day=$request->selected_day_id;
                $loginday->start_time=$data['start_time'];
                $loginday->end_time=$data['end_time'];
                $loginday->save();

                $message="Login Day Details Registered Successfully";

                return response()->json([
                    'status'=>200,
                    'message'=>$message,
                    'selected_day'=>$request->selected_day_id
                ]);
        }
    }
}

public function get_login_day_details($id)
{
    $loginDayDetails=loginDaysTime::find($id);

    if($loginDayDetails)
    {
        return response()->json([
            'status'=>200,
            'data'=>$loginDayDetails
        ]);
    } else {
        return response()->json([
            'status'=>404,
            'message'=>'Details Not Found'
        ]);
    }
}

public function delete_login_day(Request $request)
{
    $loginDayDetail=loginDaysTime::where('id',$request->current_day_id)->first();

    $deletedDay=$loginDayDetail->day;

    if($loginDayDetail)
    {
        $loginDayDetail->update([
            $loginDayDetail->is_active=0
        ]);

        $msg="Day Details Deleted Successfully.";

        return response()->json([
            'status'=>200,
            'message'=>$msg,
            'deletedDay'=>$deletedDay
        ]);
    } else {
        return response()->json([
            'status'=>404,
            'message'=>'Details Not Found'
        ]);
    }
}

// manage added dates
public function get_added_dates(Request $request)
{
    $added_dates = DB::table('login_days_time')
    ->where(['status'=>1,'is_active'=>1])
    ->whereNotNull('date')
    ->select('status','date','start_time','end_time','id')
    ->orderBy('date','Asc')
    ->get();

    // dd($added_dates);die();

    if ($request->ajax()) {
        $added_dates = DataTables::of($added_dates)
        
            ->addColumn('date', function ($row) {
                return '<span>'.Carbon::parse($row->date)->format('d-m-Y').'</span>';
            })

            ->addColumn('start_time', function ($row) {
                return '<span>' . $row->start_time . '</span>';
            })
            ->addColumn('end_time', function ($row) {
                return '<span>' . $row->end_time . '</span>';
            })

            ->addColumn('action', function ($row) {
                return
                '<div class="group">
                    <a class="btn btn-xs btn-info text-white m-1 edit-date" date-id='.$row->id.'>Edit</a>
                </div>';
            })
            ->rawColumns(['date','start_time','end_time','action'])
            ->make(true);

            // <a class="btn btn-xs btn-danger text-white m-1 delete-date" date-id='.$row->id.'>Edit</a>

        return $added_dates;
    }
}

public function create_update_added_date(Request $request)
{
    $data=$request->all();

    if($request->current_date_id == null)
    {
        $rules = [
            'date' => 'required',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time'
        ];
    
        $custommessages = [
            'date.required' => 'Date field is required',
            'start_time.required' => 'Start time field is required',
            'end_time.required' => 'End time field is required',
            'end_time.after' => 'End time field should be greater than start time'
        ];
    } else {
        $rules = [
            'start_time' => 'required',
            'end_time' => 'required|after:start_time'
        ];
    
        $custommessages = [
            'start_time.required' => 'Start time field is required',
            'end_time.required' => 'End time field is required',
            'end_time.after' => 'End time field should be greater than start time'
        ];
    }
    

    $validator = Validator::make($data, $rules, $custommessages);

    if ($validator->fails()) {
        return response()->json([
            'status' => 405,
            'message' => $validator->errors()
        ]);
    } else {
        if($request->current_date_id)
        {
            $current_date=loginDaysTime::find($request->current_date_id);

            $current_date->update([
                // $current_date->day=$request->selected_day_id,
                $current_date->start_time=$data['start_time'],
                $current_date->end_time=$data['end_time']
            ]);
            
            $message="Date Details Updated Successfully";

            return response()->json([
                'status'=>200,
                'message'=>$message
            ]);
        }else{
                loginDaysTime::where('is_active', '=', 1)->update(['is_active' => 0]);

                $addeddate=new loginDaysTime(); 
                $addeddate->date=Carbon::parse($request->date)->format('Y-m-d');
                $addeddate->start_time=$data['start_time'];
                $addeddate->end_time=$data['end_time'];
                $addeddate->is_active=1;
                $addeddate->save();

                $message="Date Details Registered Successfully";

                return response()->json([
                    'status'=>200,
                    'message'=>$message,
                    'date'=>$request->date
                ]);
        }
    }
}

public function get_added_date_details($id)
{
    $loginDayDetails=loginDaysTime::find($id);

    if($loginDayDetails)
    {
        return response()->json([
            'status'=>200,
            'data'=>$loginDayDetails
        ]);
    } else {
        return response()->json([
            'status'=>404,
            'message'=>'Details Not Found'
        ]);
    }
}

public function delete_added_date(Request $request)
{
    $loginDayDetail=loginDaysTime::where('id',$request->current_date_id)->first();

    if($loginDayDetail)
    {
        $loginDayDetail->update([
            $loginDayDetail->status=0
        ]);

        $msg="Date Details Deleted Successfully.";

        return response()->json([
            'status'=>200,
            'message'=>$msg
        ]);
    } else {
        return response()->json([
            'status'=>404,
            'message'=>'Details Not Found'
        ]);
    }
}

// manage ticket return times
public function get_return_times(Request $request)
{
    $return_tickets = DB::table('ticket_return_times')
    ->where('status',1)
    ->select('time','start_ticket','end_ticket','id')
    ->orderBy('start_ticket','Asc')
    ->get();

    if ($request->ajax()) {
        $return_tickets = DataTables::of($return_tickets)
        
            ->addColumn('time', function ($row) {
                return '<span>'.$row->time.'</span>';
            })

            ->addColumn('start_ticket', function ($row) {
                return '<span>' . $row->start_ticket . '</span>';
            })
            ->addColumn('end_ticket', function ($row) {
                return '<span>' . $row->end_ticket . '</span>';
            })

            ->addColumn('action', function ($row) {
                return
                '<div class="group">
                    <a class="btn btn-sm btn-info text-white m-1 edit-time" time-id='.$row->id.'>Edit</a>
                    <a class="btn btn-sm btn-info text-white m-1 delete-time" time-id='.$row->id.'>Delete</a>
                </div>';
            })
            ->rawColumns(['time','start_ticket','end_ticket','action'])
            ->make(true);

        return $return_tickets;
    }
}

public function get_return_time_details($id)
{
    $returnTimeDetails=DB::table('ticket_return_times')
    ->where('ticket_return_times.id',$id)
    ->first();

    if($returnTimeDetails)
    {
        return response()->json([
            'status'=>200,
            'data'=>$returnTimeDetails
        ]);
    } else {
        return response()->json([
            'status'=>404,
            'message'=>'Details Not Found'
        ]);
    }
}

public function create_update_return_time(Request $request)
{
    $data=$request->all();

    $rules = [
        'return_time' => 'required','regex:/^(0?[1-9]|1[0-2]):[0-5][0-9] (AM|PM)$/',
        'start_ticket' => 'required|gt:0',
        'end_ticket' => 'required|gt:start_ticket'
    ];

    $custommessages = [
        'return_time.required' => 'Return time value is required',
        'return_time.regex' => 'Return time format should be hh:mm AM/PM',
        'start_ticket.required' => 'Start ticket value is required',
        'start_ticket.gt' => 'Start ticket value should be greater than 0',
        'end_ticket.required' => 'End ticket value is required',
        'end_ticket.gt' => 'End ticket value should be greater than start ticket value'
    ];

    $validator = Validator::make($data, $rules, $custommessages);

    if ($validator->fails()) {
        return response()->json([
            'status' => 405,
            'message' => $validator->errors()
        ]);
    } else {

        $allReturnTickets=DB::table('ticket_return_times')
            ->where('status',1)
            ->select('start_ticket','end_ticket')
            ->orderBy('start_ticket','Asc')
            ->get();

        if($request->current_return_id)
        {
            $current_return_time=ticketReturnTime::find($request->current_return_id);

            foreach($allReturnTickets as  $key=>$returnTicket)
            {
                $arrayKeys=$allReturnTickets->keys()->toArray();
                $largestKeyValue=max($arrayKeys);

                if (isset($allReturnTickets[$key-2])) 
                {
                    if($data['end_ticket'] == $allReturnTickets[$key]->start_ticket && $data['start_ticket'] <= $allReturnTickets[$key-2]->end_ticket)
                    { 
                        print_r(($key-2).'+'.$allReturnTickets[$key-2]->start_ticket.'+'.$allReturnTickets[$key-1]->end_ticket.'=='.($key).'-'.$allReturnTickets[$key]->start_ticket.'-'.$allReturnTickets[$key]->end_ticket.',');
                        
                        $message = $data['start_ticket']  . ' to '.$data['end_ticket'] .' is overlapping existing range '. $allReturnTickets[$key-2]->start_ticket .' - '. $allReturnTickets[$key-2]->end_ticket;

                        return response()->json([
                            'status'=>415,
                            'message'=>$message                    
                        ]);

                    }

                    if($data['end_ticket'] >= $allReturnTickets[$key]->end_ticket && $data['start_ticket'] <= $allReturnTickets[$key-1]->end_ticket)
                    { 
                        
                        $message = $data['start_ticket']  . ' to '.$data['end_ticket'] .' is overlapping existing range. '. $allReturnTickets[$key]->start_ticket .' - '. $allReturnTickets[$key]->end_ticket;

                        return response()->json([
                            'status'=>415,
                            'message'=>$message                    
                        ]);

                    }
                }
                
                if($key+1 <= $largestKeyValue)
                {
                    if($data['end_ticket'] >= $allReturnTickets[$key+1]->start_ticket && $data['start_ticket'] == $allReturnTickets[$key]->start_ticket)
                    { 
                        
                        $message = $data['start_ticket']  . ' to '.$data['end_ticket'] .' is overlapping existing range.'. $allReturnTickets[$key+1]->start_ticket .' - '. $allReturnTickets[$key+1]->end_ticket;

                        return response()->json([
                            'status'=>415,
                            'message'=>$message                    
                        ]);

                    }
                }
                        
            }

            $current_return_time->update([
                $current_return_time->start_ticket=$data['start_ticket'],
                $current_return_time->end_ticket=$data['end_ticket'],
                $current_return_time->time=$data['return_time']
            ]);
            
            $message="Details Updated Successfully";

            return response()->json([
                'status'=>200,
                'message'=>$message
            ]);
        }else
        {
            
            foreach($allReturnTickets as  $key=>$returnTicket)
            {

                if( $data['end_ticket'] <= $returnTicket->end_ticket && $data['end_ticket'] >= $returnTicket->start_ticket)
                { 
                    $message = $data['start_ticket']  . ' to '.$data['end_ticket'] .' is overlapping existing range. '. $allReturnTickets[$key]->start_ticket .' - '. $allReturnTickets[$key]->end_ticket;
                    return response()->json([
                        'status'=>413,
                        'message'=>$message                    
                    ]);

                }

                if( $data['start_ticket'] >= $allReturnTickets[$key]->start_ticket &&  $data['start_ticket'] <= $allReturnTickets[$key]->end_ticket || $data['start_ticket'] >= $allReturnTickets[$key]->start_ticket && $data['end_ticket'] <= $allReturnTickets[$key]->end_ticket)
                { 
                    $message = $data['start_ticket']  . ' to '.$data['end_ticket'] .' is overlapping existing range. '. $allReturnTickets[$key]->start_ticket .' - '. $allReturnTickets[$key]->end_ticket;

                    return response()->json([
                        'status'=>414,
                        'message'=>$message                    
                    ]);

                }
            }

            // if($data['start_ticket'] == 1)
            // {
            //     $return_time_ticket=new ticketReturnTime();
            //     $return_time_ticket->start_ticket=$data['start_ticket'];
            //     $return_time_ticket->end_ticket=$data['end_ticket'];
            //     $return_time_ticket->time=$defaultStartTime->default_time;
            //     $return_time_ticket->save();

            //     $message="Details registered Successfully";

            //     return response()->json([
            //         'status'=>200,
            //         'message'=>$message
            //     ]);
            // }

            $return_time_ticket=new ticketReturnTime();
            $return_time_ticket->start_ticket=$data['start_ticket'];
            $return_time_ticket->end_ticket=$data['end_ticket'];
            $return_time_ticket->time=$data['return_time'];
            $return_time_ticket->save();

            $message="Details registered Successfully";

            return response()->json([
                'status'=>200,
                'message'=>$message
            ]);
        }
    }
}

public function delete_return_time(Request $request)
{
    $returnTime=ticketReturnTime::where('id',$request->current_return_time_id)->first();

    if($returnTime)
    {
        $returnTime->update([
            $returnTime->status=0
        ]);

        $msg="Details Deleted Successfully.";

        return response()->json([
            'status'=>200,
            'message'=>$msg
        ]);
    } else {
        return response()->json([
            'status'=>404,
            'message'=>'Details Not Found'
        ]);
    }
}

        
}
