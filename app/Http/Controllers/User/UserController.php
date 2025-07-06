<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\generatedTicket;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class UserController extends Controller
{

   
    public function index(){
        return view('user.dashboard');
    }

    public function print(){
        return view('user.print');
    }

    public function number_being_served(){
        
        
        return view('user.number_being_served');
    }
  
}