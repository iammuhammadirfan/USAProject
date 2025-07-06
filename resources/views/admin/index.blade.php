@extends('admin.layout')
@section('title',"TGOG")
@section('content')
@section('index_styles')
@stop

<div class="row">
    <div class="text-center mb-4">
        <p>TGOG Ver 7.1 (05/14/2025)</p>
        <img src="{{ asset('/tgoglogo.webp') }}" class="img-fluid" alt="NHCC Logo" width="130" height="60"/>
    </div>
    <h1 class="admin-title text-center mb-5">Administrator Main</h1>
    <div class="row">
        <div class="col-lg-8 col-md-10">
            <div class="d-flex flex-column admin-nav">
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="{{route('admin.view.recipients.screen')}}" class="nav-link">View Sign-ups</a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/number-control" class="nav-link">Number Control</a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/overview-dashboard" class="nav-link">Overview Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/get-single-ticket" target="_blank" class="nav-link">Get Single Ticket</a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/multiple-tickets-details/{{ Auth::user()->id }}" target="_blank" class="nav-link">Get Multiple Tickets</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('admin.register.recipient.screen')}}" class="nav-link">Register New Recipient</a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/modify-users" target="_blank" class="nav-link">Search Recipient Record</a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/volunteer-group-signups" target="_blank" class="nav-link">Volunteers/Group Home Sign-ups</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('admin.tool.index')}}" class="nav-link">System Settings</a>
                    </li>
                </ul>
                <ul class="nav nav-pills flex-column mt-5">
                    <li class="nav-item">
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav-link logout-link">Log Off</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-lg-2 col-md-1"></div>
    </div>
</div>

@endsection