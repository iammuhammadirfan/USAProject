<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Session;
use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\Memo;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\generatedTicket;
use App\Events\TicketsAnalytics;
use Illuminate\Http\JsonResponse;
use App\Models\notificationMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB as FacadesDB;
use App\Services\LoginService;

class LoginController extends Controller
{
    protected $loginService;

    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    public function showLoginForm(Request $request)
	{  
        // Use cached data for better performance
        $checkEnabledMsg = DB::table('memos')->where(['status'=>1,'is_enabled'=>1,'is_type'=>1])->first();
        
        // Use cached ticket count
        $todayTotalTickets = $this->loginService->getTodayTicketsCount();

        // Use cached ticket limit checker
        $ticketLimitChecker = $this->loginService->getTicketLimitChecker();

        if($checkEnabledMsg)
        {
            $enabledMsgStatus=1;
            $enabledMsg=$checkEnabledMsg->message;
        } else {
            $enabledMsgStatus=0;
            $enabledMsg='';
        }

        // Use cached notification messages
        $notificationMessages = $this->loginService->getNotificationMessages();

        session()->forget('notificationMessages');
        session()->forget('returnTimesStatus');
        session()->forget('ticketsReturnTimes');

        session()->put('notificationMessages', $notificationMessages);

        if ($ticketLimitChecker->return_times_status == 1)
        {
            // Use cached return times
            $ticketsReturnTimes = $this->loginService->getReturnTimes();

            session()->put('returnTimesStatus', 1);
            session()->put('ticketsReturnTimes', $ticketsReturnTimes);
        }

        if (session()->has('first_page_visit'))
        {
            session()->forget('first_page_visit');
            session()->put('first_page_visit', 0);
        } else {
            session()->put('first_page_visit', 1);
        }

        if (session()->has('maxTicketsReachedId'))
        {
            session()->forget('maxTicketsReachedId');
            session()->forget('maXTicketsToday');
        }

        if($ticketLimitChecker->ticket_limit_status == 1)
        {
             session()->put('maXTicketsToday', $ticketLimitChecker->ticket_limit);
        }

        if($todayTotalTickets >= $ticketLimitChecker->ticket_limit && $ticketLimitChecker->ticket_limit !== 0)
        {
            session()->put('maxTicketsReachedId', 1);
        }

        $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));
        $extractedCurrentTime= $dateObj->format("H:i");
        $todaysDate=$dateObj->format("Y-m-d");
        $dayOfTheWeek=strtolower(date('l', strtotime($todaysDate)));

        // Use cached login days time
        $loginDayDate = $this->loginService->getLoginDaysTime();

        $day=$loginDayDate->day;
        $date=$loginDayDate->date;

        $rawStartTime=str_replace('.', ':', $loginDayDate->start_time);
        $rawEndTime=str_replace('.', ':', $loginDayDate->end_time);

        $startTimeFormat = Carbon::createFromFormat('g:i A', $rawStartTime)->format('H:i');
        $endTimeFormat = Carbon::createFromFormat('g:i A', $rawEndTime)->format('H:i');

        $start_time=$startTimeFormat.':00';
        $end_time=$endTimeFormat.':00';

        $today = Carbon::now();

        if($day == $dayOfTheWeek || $date == $todaysDate)
        {
            $todayDateFormat = $today->format('M d, Y');

            $loginUsersDateTime=$todayDateFormat . ' ' . $start_time;
            $loginUsersDateEndTime=$todayDateFormat . ' ' . $end_time;

            $resetingTime = Carbon::today()->setTime(23, 59);
            $systemResetingTime = $resetingTime->format('Y-m-d H:i:s');

            if($day == $dayOfTheWeek)
            {
                $nextDayAtStartTime = $today->next($day)->format('M d, Y') . ' ' . $start_time;
                $nextWeekLoginStartTime = Carbon::parse($nextDayAtStartTime)->toDateTimeString();
            } else {
                $nextWeekLoginStartTime = '';
            }
        } else {
            if($date !== null)
            {
                $nextLoginDate = Carbon::parse($date)->format('M d, Y');
                $nextDayAtStartTime = $nextLoginDate. ' ' . $start_time;
                $nextDayAtEndTime = $nextLoginDate. ' ' . $end_time;

                $loginUsersDateTime = Carbon::parse($nextDayAtStartTime)->toDateTimeString();
                $loginUsersDateEndTime = Carbon::parse($nextDayAtEndTime)->toDateTimeString();
                $nextWeekLoginStartTime='';
            } else {
                $nextLoginDate = $today->next($day)->format('M d, Y');
                $nextDayAtStartTime = $nextLoginDate. ' ' . $start_time;
                $nextDayAtEndTime = $nextLoginDate. ' ' . $end_time;

                $loginUsersDateTime = Carbon::parse($nextDayAtStartTime)->toDateTimeString();
                $loginUsersDateEndTime = Carbon::parse($nextDayAtEndTime)->toDateTimeString();
                $nextWeekLoginStartTime='';
            }
            $systemResetingTime = '';
        }

        return view('user.login', compact('ticketLimitChecker','systemResetingTime','enabledMsgStatus','enabledMsg','loginUsersDateTime','loginUsersDateEndTime','nextWeekLoginStartTime'));
    }

    public function admin_login()
	{
        // Use cached data for better performance
        $ticketLimitChecker = $this->loginService->getTicketLimitChecker();
        $notificationMessages = $this->loginService->getNotificationMessages();

        session()->forget('notificationMessages');
        session()->forget('returnTimesStatus');
        session()->forget('ticketsReturnTimes');

        session()->put('notificationMessages', $notificationMessages);

        if ($ticketLimitChecker->return_times_status == 1)
        {
                $ticketsReturnTimes = $this->loginService->getReturnTimes();
            session()->put('returnTimesStatus', 1);
            session()->put('ticketsReturnTimes', $ticketsReturnTimes);
        }

        return view('admin_login');
    }

    public function login(Request $request)
    {
        $data=$request->all();

        $rules=[
            'last_name' => 'required|string',
            'case_number' => 'required|string',
        ];

        $custommessages=[
            'last_name.required'=>'Enter Last Name',
            'case_number.required'=>'Enter Case Number',
        ];

        $validator = Validator::make( $data,$rules,$custommessages );
            
        if($validator->fails())
        {
            return response()->json([
                'status' => 405,
                'message' => $validator->errors()
            ]);
        }

        // Use optimized user validation with LoginService
        $user = $this->loginService->validateUser($request->last_name, $request->case_number);

        $ntfcationMsgs=Session::get('notificationMessages');
                    
        if ($user !== null) 
        {
            // check if a user has a warning then log them out if they have a warning
            if($user->is_warning == 1)
            {
                $user->update([
                    'is_active' => 0
                ]);

                return response()->json([
                    'status'=>410,
                    'message'=>'Your account is currently unavailable. Please visit in person for assistance.'
                ]);
            } else {
                if($user->is_picked_warning == 2 && $user->is_active == 0)
                {
                    $notpickedGoodiesSecondWarnNotificationMsg = $ntfcationMsgs->firstWhere('modal_no', 15);

                    return response()->json([
                        'status'=>415,
                        'message'=>$notpickedGoodiesSecondWarnNotificationMsg->message
                    ]);
                }
                
                if ($user->is_active == 1) 
                {
                    if ($user->role !== 2) 
                    {
                        Auth::login($user);

                        DB::table('activity_log')->insertGetId([
                            'staff_id' => Auth::id(),
                            'activity_id' => 8,
                            'created_at' => Carbon::now()
                        ]);

                        return response()->json([
                            'status'=>201,
                        ]);
                    }

                    if($request->login_type && $request->login_type == 'admin_login')
                    {
                        if ($user->role !== 2) {
                            Auth::login($user);

                            DB::table('activity_log')->insertGetId([
                                'staff_id' => Auth::id(),
                                'activity_id' => 8,
                                'created_at' => Carbon::now()
                            ]);

                            return response()->json([
                                'status'=>201,
                            ]);
                        } else {
                            return response()->json([
                                'status'=>420,
                                'message'=>'This page can only be accessed by adminstrators only'
                            ]);
                        }
                    }
                    
                    $captalisedUserCaseNumber = Str::upper($user->case_number);
                    $captalisedCaseNumber = Str::upper($request->case_number);

                    if ($captalisedUserCaseNumber == $captalisedCaseNumber && strtolower($user->last_name) == strtolower($request->last_name)) 
                    {
                        $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));
                        $extractedCurrentTime= $dateObj->format("H:i");
                        $todaysDate=$dateObj->format("Y-m-d");
                        $dayOfTheWeek=strtolower(date('l', strtotime($todaysDate)));

                        // Use cached login times
                        $getDayLoginTimes = $this->loginService->getDayLoginTimes($dayOfTheWeek);
                        $getLoginDateTimes = $this->loginService->getLoginDateTimes($todaysDate);
                        $loginDayDate = $this->loginService->getLoginDaysTime();
                
                        $day=$loginDayDate->day;
                        $date=$loginDayDate->date;

                        if($day == $dayOfTheWeek || $date == Carbon::now())
                        {
                            if($getDayLoginTimes)
                            {
                                $dayStartTime=date('H:i', strtotime($getDayLoginTimes->start_time));
                                $dayEndTime=date('H:i', strtotime($getDayLoginTimes->end_time));
                            } else {
                                $dayStartTime="0";
                                $dayEndTime="0";
                            }

                            if($getLoginDateTimes)
                            {
                                $dateStartTime=date('H:i', strtotime($getLoginDateTimes->start_time));
                                $dateEndTime=date('H:i', strtotime($getLoginDateTimes->end_time));
                            } else {
                                $dateStartTime="0";
                                $dateEndTime="0";
                            }

                            if(($extractedCurrentTime >= $dayStartTime && $extractedCurrentTime <= $dayEndTime) || ($extractedCurrentTime >= $dateStartTime && $extractedCurrentTime <= $dateEndTime))
                            {
                                // Use cached ticket limit checker
                                $ticketLimitChecker = $this->loginService->getTicketLimitChecker();
                                
                                // Use cached today's tickets count
                                $totalTodaysTicketsCount = $this->loginService->getTodayTicketsCount();
                                
                                if($user->is_picked_warning == 1)
                                {
                                    $notpickedGoodiesFirstWarnNotificationMsg = $ntfcationMsgs->firstWhere('modal_no', 14);

                                    Session::put([
                                        'user_name'=>$request->last_name,
                                        'case_number'=>$request->case_number
                                    ]);

                                    return response()->json([
                                        'status'=>416,
                                        'message'=>$notpickedGoodiesFirstWarnNotificationMsg->message
                                    ]);
                                }

                                Auth::login($user);

                                // Use cached ticket analytics for better performance
                                $ticketsAnalytics = $this->loginService->getTicketsAnalytics();
                                event(new TicketsAnalytics($ticketsAnalytics));

                                $parsedDate=Carbon::parse($todaysDate);

                                // Use optimized ticket checker
                                $ticketChecker = $this->loginService->getUserExistingTicket($user->id, $todaysDate);

                                if($ticketChecker)
                                {
                                    if($ticketChecker->multiple_id)
                                    {
                                        return response()->json([
                                            'status'=>203,
                                            'id'=>$ticketChecker->multiple_id
                                        ]);
                                    }

                                    return response()->json([
                                        'status'=>204,
                                        'id'=>$ticketChecker->id
                                    ]);
                                }

                                // check if a user is a bot
                                if($request->confirm_case_number !== null)
                                {
                                    $user->update(['is_warning'=>1]);
                                    Session::put('is_bot', 1);
                                }

                                return response()->json([
                                    'status'=>200
                                ]);
                            } else {
                                return response()->json([
                                    'status'=>417,
                                    'message'=>'Login is not available right now'
                                ]);
                            }
                        } else {
                            return response()->json([
                                'status'=>417,
                                'message'=>'Login is not available right now'
                            ]);
                        }
                    }
                } else {
                    return response()->json([
                        'status'=>418,
                        'message'=>'Your account cannot be accessed at the moment.Please visit the administrator for assistance'
                    ]);
                }
            }
        } else {
            return response()->json([
                'status'=>421,
                'message'=>'Invalid login credentials. Please check and try again.'
            ]);            
        }
    }

    public function permit_login(Request $request)
    {
        $user = User::where(['last_name'=>$request->last_name,'case_number'=>$request->case_number])
        ->first();

        Auth::login($user);
    }

    public function custom_login(Request $request)
    {
        $request->session()->forget('user_name','case_number');

        $user = User::where(['last_name'=>$request->last_name,'case_number'=>$request->case_number])
        ->first();

        $dateObject= new DateTime("now", new DateTimeZone("America/Vancouver"));
        $todaysDate=$dateObject->format("Y-m-d");

        Auth::login($user);

        // Use cached ticket analytics for better performance
        $ticketsAnalytics = $this->loginService->getTicketsAnalytics();
        event(new TicketsAnalytics($ticketsAnalytics));

        $parsedDate=Carbon::parse($todaysDate);

        // Use optimized ticket checker
        $ticketChecker = $this->loginService->getUserExistingTicket($user->id, $todaysDate);

        if($ticketChecker)
        {
            if($ticketChecker->multiple_id)
            {
                return response()->json([
                    'url_type'=>'multiple_tickets',
                    'id'=>$ticketChecker->multiple_id
                ]);
            }

            return response()->json([
                'url_type'=>'single_ticket',
                'id'=>$ticketChecker->id
            ]);
        }

        // check if a user is a bot
        if($request->confirm_case_number !== null)
        {
            $user->update(['is_warning'=>1]);
            Session::put('is_bot', 1);
        }

        return response()->json([
            'url_type'=>'get_ticket_options',
        ]);
    }

    public function system_reset_function($id)
    {
        $reset_id=$id;
        $response_data=generatedTicket::system_reset_functions($reset_id);

        return response()->json([
            'data'=>$response_data
        ]);
    }

    // check duplicates function
    public function manage_duplicates()
    {
        $duplicateUsersTickets=DB::table('generated_tickets')
            ->whereDate('created_at', Carbon::today())
            ->select('ticket_number', DB::raw('COUNT(*) as count'))
            ->groupBy('ticket_number')
            ->having('count', '>', 1)
            ->get();

        dd($duplicateUsersTickets);die();
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        if(Auth::user()->role == '3')
        {
            DB::table('activity_log')->insertGetId([
                'staff_id' => Auth::id(),
                'activity_id' => 9,
                'created_at' => Carbon::now()
            ]);
        }
        Auth::logout();

        // Redirect to homepage after logout
        return redirect()->route('home');
    }
}
