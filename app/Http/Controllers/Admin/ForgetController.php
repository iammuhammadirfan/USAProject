<?php

namespace App\Http\Controllers\Admin;

use DB;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\notificationMessage;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ForgetController extends Controller
{
    //
    public function forgetUser(){

        $notificationMessages=notificationMessage::where('status',1)->select('modal_no','message')->get();
        
        if (Session::has('notificationMessages')){
            // do some thing if the key is exist
          }else{
            session()->put('notificationMessages', $notificationMessages);
          }
        return view('user.forget');
    }

    public function forgetUserCaseNumber(Request $request)
    {
        $requestData = $request->all();

        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'date_of_birth' => 'required'
        ];

        $custommessages = [
            'first_name.required' => 'First Name is required',
            'last_name.required' => 'Last Name is required',
            'date_of_birth.required' => 'Date of Birth is required',
        ];

        $validator = Validator::make($requestData, $rules,$custommessages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 405,
                'message' => $validator->errors()
            ]);
        } else {
            // Construct the query
            $date_of_birth=Carbon::parse($requestData['date_of_birth'])->format('Y-m-d');

            $query = User::where([
                'first_name'=>$requestData['first_name'],
                'last_name'=>$requestData['last_name'],
                'is_active'=>1
            ])
            ->whereDate('date_of_birth',$date_of_birth)
            ->select('first_name','last_name','case_number')
            ->first();

            // Check if a user is found
            if ($query) {
                return response()->json([
                    'status' => 200,
                    'data'=>$query
                ]);
            } else {
                return response()->json([
                    'status' => 400,
                    'message'=>'Sorry, case number not available. Please visit New Hope Community Church (1821 Meadowview Road, Sacramento, CA, 95832) on Thursdays for assistance.'
                ]);
            }

        }
    }

    public function test_link(){

        return view('testLink');
    }

    public function upload_link(){

        
    }
}
