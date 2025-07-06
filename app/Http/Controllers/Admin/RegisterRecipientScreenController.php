<?php

namespace App\Http\Controllers\Admin;

use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
// use Barryvdh\DomPDF\PDF;
use Dompdf\Dompdf;
use Session;
use App\Services\TicketService;
use App\Models\generatedTicket;
use App\Events\TicketsAnalytics;
use App\Events\ReloadTicketsTable;
use App\Events\UncheckedInTickets;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class RegisterRecipientScreenController extends Controller
{

	public function print_id_card_pdf($id)
	{
		$userIdCard=DB::table('users')
		->select('id','id_card','case_number')
		->where('id','=',$id)
		->first();

		DB::table('activity_log')->insertGetId([
            'staff_id' => Auth::id(),
			'user_id' => $id,
            'activity_id' => 11,
            'created_at' => Carbon::now()
        ]);

		$extension = pathinfo($userIdCard->id_card, PATHINFO_EXTENSION);

		$printIdcard=new Dompdf();

		$imgPath=public_path('/images/id_cards/'.$userIdCard->id_card);

		if (file_exists($imgPath) && is_file($imgPath)) {
			$imgContent = file_get_contents($imgPath);
			if ($imgContent !== false) {
				$img_details='<img style="object-fit:contain;" src="data:image/'.$extension.';base64,'.base64_encode($imgContent).'" />';
			} else {
				$img_details='<p>ID Card Not Available in Server.Please Reupload It.</p>';
			}
		} else {
			$img_details='<p style="margin:25% 5%; text-align:center; font-weight:700;font-size:50px;">ID Card Not Found In Server.</p>';
		}

		\Log::info("Checking file at: " . $imgPath);

		$html='<!DOCTYPE html>
			<html>
				<head>
					<title>GOGO User '.$userIdCard->case_number.' ID Card</title>
					<style>
						*{
							padding: 0;
							margin:0;
						}
					</style>
				</head>
				<body>
					<div>'.$img_details.'
					</div>
				</body>
			</html>';

		$printIdcard->loadHtml($html);

		$printIdcard->setPaper(array(0, 0,480,755),'landscape');

		$printIdcard->render();

		return $printIdcard->stream('GOGO User '.$userIdCard->case_number.' ID Card.pdf', array("Attachment" => false));
	}

	Public function find_user_details(Request $request){
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
		
		$user_data=DB::table('users')
		->where('case_number', 'LIKE', '%' . $rawCaseNumber)
		->where(['status'=>1])
		->select('id','first_name','last_name','proxy','case_number','date_of_birth')
		->first();

		if($user_data == null)
		{
			return response()->json([
				'status' => 404,
				'message' => 'Case number not found'
			]);

		} else 
		{
			return response()->json([
				'status' => 200,
				'data' => $user_data
			]);

		}
    }
	
	public function get_user($id){

		$user = DB::table('users')
			// ->leftJoin('activity_log as staffName','users.id','=','activity_log.staff_id')
			->where(['users.id'=>$id,
			// 'staffName.activity_id'=>12,
			])
			->select('users.date_of_birth',
				'users.idcard_issued_date',
				'users.id_card',
				'users.id',
				'users.first_name',
				'users.last_name',
				'users.case_number',
				'users.proxy',
				'users.is_active')
			// ->selectRaw('ticketGeneratorUser.first_name as firstName')
			// ->selectRaw('ticketGeneratorUser.last_name as lastName')
			// ->selectRaw('ticketGeneratorUser.case_number as caseNumber')
			->first();

		$user_register_details = DB::table('activity_log')
			->leftJoin('users as staffName','activity_log.staff_id','=','staffName.id')
			->where(['activity_log.user_id'=>$id,'activity_log.activity_id'=>12,
			])
			->selectRaw('staffName.first_name as stafffName')
			->selectRaw('staffName.last_name as stafflName')
			->selectRaw('activity_log.created_at as staffCreatedDate')
			->first();

		if($user_register_details !== null)
		{
			$adminFName=$user_register_details->stafffName;
			$adminLName=$user_register_details->stafflName;
			$adminCreatedDate=Carbon::parse($user_register_details->staffCreatedDate)->format('m/d/Y');
		} else {
			$adminFName=null;
			$adminLName=null;
			$adminCreatedDate=null;
		}


		$tickets = DB::table('generated_tickets')
			->where('user_id', $id)
			->where(['is_cancelled'=>0,'is_active'=>1,'status'=>1,'checked_in'=>0,])
			->where('created_at','>=', Carbon::parse('2024-12-25 23:59:59'))
			->where('created_at','<',Carbon::now()->subDay()->setTime(23, 59, 59))
			->select('id','ticket_number','created_at')
			->get();

		$userDetails = (object) [
			'id' => $user->id,
			'date_of_birth' => $user->date_of_birth,
			'idcard_issued_date' => $user->idcard_issued_date,
			'id_card' => $user->id_card,
			'first_name' => $user->first_name,
			'last_name' => $user->last_name,
			'proxy' => $user->proxy,
			'is_active' => $user->is_active,
			'case_number' => $user->case_number,
			'user_tickets' => $tickets,
			'staff_fname' => $adminFName,
			'staff_lname' => $adminLName,
			'staff_date' => $adminCreatedDate
		];

		return $userDetails;
	}
	
	public function update_user(Request $request)
	{
		$data=$request->all();
		
        $rules = [
            'lname'=>'required',
            'fname'=>'required',
            'dob'=>'required',
            'case_number'=>'required'
        ];

        $custommessages = [
            'fname.required' => 'First name value is required',
            'lname.required' => 'Last name value is required',
            'dob.required' => 'Select Date of Birth',
            'case_number.required' => 'Case number value is required'
        ];

        $validator = Validator::make($data, $rules, $custommessages);

        if ($validator->fails()) 
		{
            return response()->json([
                'status' => 405,
                'message' => $validator->errors()
            ]);
        } else 
		{
			//count the number of case number
			$userDetailsCount=DB::table('users')->where('case_number',$request->case_number)->where('status',1)->count();
			$userDetails=User::where('id',$request->user_id)->first();

			// dd($data,$userDetails);die();
			
			if($data['idcard_issued_date'] == null)
			{
				$idIssueDate=null;
			} else {
				$idIssueDate=Carbon::parse($data['idcard_issued_date'])->format('Y-m-d');
			}

			if($data['disabled_user'] == 1)
			{
				$is_picked_warning=1;
			} else {
				$is_picked_warning=0;
			}

			if($data['id_card'] == null && $userDetails->id_card == "")
			{
				$id_card="";
			}

			if($data['id_card'] == null && $userDetails->id_card !== "")
			{
				$id_card=$userDetails->id_card;
			}

			if($data['id_card'] !== null)
			{
				if ($request->hasFile('id_card')) {
                    $imagetmp = $request->file('id_card');
                    if ($imagetmp->isValid()) {
                        $extension = $imagetmp->getClientOriginalExtension();
                        $id_card = $userDetails->case_number.'-'.rand(111,9999).'.'.$extension;
                        $dest = public_path('/images/id_cards/');
						// $dest = $imagetmp->storeAs('/images/id_cards/', $id_card, 'public2');
                        $imagetmp->move($dest, $id_card);
                    }
                }

				DB::table('activity_log')->insertGetId([
					'staff_id' => Auth::id(),
					'user_id' => $request->user_id,
					'activity_id' => 10,
					'created_at' => Carbon::now()
				]);

				$id_card=$id_card;
			}
			
			//$userDetailsCount > 0 && $userDetails->case_number !== $data['case_number']
			if ($userDetailsCount > 0 && $userDetails->case_number == $data['case_number'] || $userDetailsCount == 0 && $userDetails->case_number !== $data['case_number'])
			{
				$userDetails->update([
					'case_number'=>$data['case_number'],
					'first_name'=>$data['fname'],
					'last_name'=>$data['lname'],
					'proxy'=>$data['proxy'],
					'date_of_birth'=>Carbon::parse($data['dob'])->format('Y-m-d'),
					'idcard_issued_date'=>$idIssueDate,
					'id_card'=>$id_card,
					'is_active'=>$data['disabled_user'],
					'is_picked_warning'=>$is_picked_warning
				]);

				if($data['disabled_user'] == 1)
				{
					$userDetails->update([
						'is_picked_warning'=>0
					]);
				}

				DB::table('activity_log')->insertGetId([
					'staff_id' => Auth::id(),
					'user_id' => $request->user_id,
					'activity_id' => 13,
					'created_at' => Carbon::now()
				]);
				
				$message="Details Updated successfully";
				
				return response()->json([
					'status'=>200,
					'msg'=>$message
				]);
			} else {
                    $message="Case Number exists for another user";
                    return response()->json([
                        'status'=>500,
                        'msg'=>$message
                    ]);
                }
				
		}
	}
	
	public function delete_user(Request $request)
	{
        $userDetails=User::where('id',$request->user_id)->first();
			
			if($userDetails)
			{
				$userDetails->update([
					'status'=>0,
				]);
				
				$message="Details Deleted successfully";
					
				return response()->json([
					'status'=>200,
					'msg'=>$message
				]);
			} else 
			{
				$message="Details Not Found";
					
				return response()->json([
					'status'=>404,
					'msg'=>$message
				]);
			}
	}
	
	public function register_record_single_ticket(Request $request)
	{
		$data=$request->all();

		// dd($data);die();

        $rules = [
            'last_name'=>'required',
            'first_name'=>'required',
            'date_of_birth'=>'required',
            'case_number'=>'required',
            'proxy'=>'required|in:yes,no',
			// 'idcard_issue_date'=>'required',
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

        if ($validator->fails()) 
		{
            return response()->json([
                'status' => 405,
                'message' => $validator->errors()
            ]);
        } else 
		{
            $user=User::where('case_number',$request->case_number)->where('status',1)
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

			// if($request->idcard_issue_date !== null)
			// {
			// 	$idcard_issuance_date=Carbon::parse($request->idcard_issue_date)->format('Y-m-d');
			// } else {
			// 	$idcard_issuance_date=null;
			// }

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
				'staff_id' => Auth::id(),
				'user_id' => $user->id,
				'activity_id' => 12,
				'created_at' => Carbon::now()
			]);

			$request->merge([
				'userId' => $user->id,
				'submission_type' => 0
			]);

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
            
			// $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));

			// $dateObj= new DateTime("now", new DateTimeZone("America/Vancouver"));

			// $todaysDate=$dateObj->format("Y-m-d");

			
			// $getDeactivatedTicketNumber=DB::table('generated_tickets')
			// 								->where('generated_tickets.is_active','=',0)
			// 								->where('generated_tickets.is_cancelled','=',1)
			// 								->whereDate('generated_tickets.created_at',$todaysDate)
			// 								->pluck('generated_tickets.ticket_number')
			// 								->first();
											
			// $latestSavedData=generatedTicket::
			// select('ticket_number','created_at')
			// ->whereDate('generated_tickets.created_at',$todaysDate)
			// ->orderBy('ticket_number', 'Desc')
			// ->first();

			// $service = new TicketService(); 
    
			// try {
			// 	$response = $service->generateTickets($request);
				
			// 	// Ensure we always return a JSON response
			// 	if ($response instanceof \Illuminate\Http\JsonResponse) {
			// 		return $response;
			// 	}
				
			// 	return response()->json([
			// 		'status' => 200,
			// 		'message' => 'Ticket generated successfully',
			// 		'data' => $response
			// 	]);
			// } catch (\Exception $e) {
			// 	return response()->json([
			// 		'status' => 500,
			// 		'message' => $e->getMessage()
			// 	], 500);
			// }

			// $ticket=new generatedTicket();
			// $ticket->user_id=$user->id;
			// $ticket->is_reset=0;
			// $ticket->save();
			
			// if($latestSavedData)
			// {
			// 	$latestSavedDate=Carbon::parse($latestSavedData->created_at)->format('Y-m-d');

			// 	if($todaysDate == $latestSavedDate)
			// 	{
			// 		if($getDeactivatedTicketNumber)
			// 		{
			// 			$ticketNumber=$getDeactivatedTicketNumber;

			// 			$TicketDetails=generatedTicket::where('is_active',0)->first();

			// 			$TicketDetails->update([
			// 				'is_active' => 1,
			// 				'is_cancelled' => 1
			// 			]);
			// 		} else {
			// 			$ticketNumber=$latestSavedData->ticket_number + 1;
			// 		}
			// 	} else {
			// 		if($getDeactivatedTicketNumber)
			// 		{
			// 			$ticketNumber=$getDeactivatedTicketNumber;

			// 			$TicketDetails=generatedTicket::where('is_active',0)->first();

			// 			$TicketDetails->update([
			// 				'is_active' => 1,
			// 				'is_cancelled' => 1
			// 			]);
			// 		} else {
			// 			$ticketNumber=$latestSavedData->ticket_number + 1;
			// 		}
			// 	}

			// } else {
			// 	$ticketNumber=1;
			// }

			// $getAllReturnTimes=DB::table('ticket_return_times')
			// ->where('ticket_return_times.is_active','=',1)
			// ->where('ticket_return_times.status','=',1)
			// ->select('ticket_return_times.start_ticket','ticket_return_times.end_ticket','ticket_return_times.time',)
			// ->get();

			// $projectedReturnTime=0;
			// foreach($getAllReturnTimes as $key=>$return_time)
			// {
			// 	if($ticketNumber >= $return_time->start_ticket && $ticketNumber <= $return_time->end_ticket)
			// 	{
			// 		$projectedReturnTime=$return_time->time;
			// 	}
			// }

			// $savedTicketDetails=generatedTicket::find($ticket->id);

			// $savedTicketDetails->update([
			// 	// 'projected_return_time' => $projectedReturnTime,
			// 	'ticket_number' => $ticketNumber,
			// 	'is_first' => 1
			// ]);

			// if (Session::has('returnTimesStatus'))
			// {
			// 	$projectedReturnTime=0;
				
			// 	foreach($getAllReturnTimes as $key=>$return_time)
			// 	{
			// 		if($ticketNumber >= $return_time->start_ticket && $ticketNumber <= $return_time->end_ticket)
			// 		{
			// 			$projectedReturnTime=$return_time->time;
			// 		}
			// 	}

			// 	$savedTicketDetails->update([
			// 		$savedTicketDetails->projected_return_time=$projectedReturnTime,
			// 	]);
			// }
			// if (Session::has('returnTimesStatus') && Session::get('returnTimesStatus') == 1)
			// {
			// 	$ticketsReturnTimes = Session::get('ticketsReturnTimes');
			// 	$projectedReturnTime=0;

			// 	foreach($ticketsReturnTimes as $key=>$return_time)
			// 	{
			// 		if($ticketNumber >= $return_time->start_ticket && $ticketNumber <= $return_time->end_ticket)
			// 		{
			// 			$projectedReturnTime=$return_time->time;
			// 		}
			// 	}

			// 	$savedTicketDetails->update([
			// 		$savedTicketDetails->projected_return_time=$projectedReturnTime,
			// 	]);
			// }

			
			// if($request->generatedBy && $request->generatedBy == 1)
			// {
			// 	$savedTicketDetails->update([
			// 		'generated_by' => Auth::user()->id
			// 	]);
			// }

			// $id=$ticket->id;

			// $ticketsAnalyticsData=generatedTicket::tickets_analytics();

			// event(new TicketsAnalytics($ticketsAnalyticsData));

			// $newTicketsData=generatedTicket::get_new_tickets($id);

			// event(new ReloadTicketsTable($newTicketsData));
    
			// // $uncheckedTicketsData=generatedTicket::not_checkedin_tickets();

			// // event(new UncheckedInTickets($uncheckedTicketsData));

			// return response()->json([
			// 	// 'status'=>200,
			// 	'ticket_id'=>$savedTicketDetails->id,
			// 	'status' => 200,
            //     'message' => 'Recipient has been registered and ticket generated successfully.'
			// ]);
		}
	}

	public function reset_error_tickets(Request $request)
	{
		// get no show users for 26th dec
		$firstNoShowTickets=DB::table('generated_tickets')
			->where(['is_cancelled'=>0,'is_active'=>1,'checked_in'=>0])
			->where('created_at','>=', Carbon::parse('2024-12-26 07:00:00'))
			->where('created_at','<',Carbon::parse('2024-12-26 23:59:59'))
			->pluck('user_id')
			->toArray();

		DB::table('users')->update(['is_picked_warning' => 0,'is_active' => 1,'status'=> 1]);
		

		foreach($firstNoShowTickets as $key=>$user)
		{
			DB::table('users')->where('id',$user)->update(['is_picked_warning' => 1]);
		}

		$secondNoShowTickets=DB::table('generated_tickets')
			->where(['is_cancelled'=>0,'is_active'=>1,'checked_in'=>0])
			->where('created_at','>=', Carbon::parse('2025-01-02 07:00:00'))
			->where('created_at','<',Carbon::parse('2025-01-02 23:59:59'))
			->pluck('user_id')
			->toArray();

		foreach($secondNoShowTickets as $key2=>$user2)
		{
			$user=DB::table('users')->where('id',$user2)->select('is_picked_warning')->first();

			if($user->is_picked_warning == 1)
			{
				DB::table('users')->where('id',$user2)->update(['is_picked_warning' => 2,'is_active'=>0]);
			} else {
				DB::table('users')->where('id',$user2)->update(['is_picked_warning' => 1]);
			}
		}


		$thirdNoShowTickets=DB::table('generated_tickets')
			->where(['is_cancelled'=>0,'is_active'=>1,'checked_in'=>0,])
			->where('created_at','>=', Carbon::parse('2025-01-09 07:00:00'))
			->where('created_at','<',Carbon::parse('2025-01-09 23:59:59'))
			->pluck('user_id')
			->toArray();

		foreach($thirdNoShowTickets as $key3=>$user3)
		{
			$user=DB::table('users')->where('id',$user3)->select('is_picked_warning')->first();

			if($user->is_picked_warning == 1)
			{
				DB::table('users')->where('id',$user3)->update(['is_picked_warning' => 2,'is_active'=>0]);
			} else {
				DB::table('users')->where('id',$user3)->update(['is_picked_warning' => 1]);
			}
		}
		return response()->json([
			'message' => 'Tickets Reset Successfully.'
		]);
	}
}
