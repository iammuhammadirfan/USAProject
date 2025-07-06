<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\notificationMessage;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $notificationMessages=notificationMessage::where('status',1)->get()->toArray();
        
        session()->put('notificationMessages', $notificationMessages);

        $notificationmsgs=Session::get('notificationMessages');
    }
}
