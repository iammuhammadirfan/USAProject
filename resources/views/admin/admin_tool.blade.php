@extends('admin.layout')
@section('content')
@section('admin_tool_styles')
{{-- datatables --}}
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.bootstrap.css"/>

{{-- select2 --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

{{-- timepicker --}}
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">

{{-- date picker css--}}
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">

<!-- sweetalert -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

{{-- alertify --}}
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>
<!-- Default theme -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/default.min.css"/>
<!-- Semantic UI theme -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/semantic.min.css"/>

<style>
    .ui-timepicker-container{ 
        z-index:1151 !important; 
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered li {
        padding-right: 10px;
        list-style-type: none;
    }

    .dt-length label {
        margin-left: 10px;
    }

    .update-password-form
    {
        display: flex;
        gap: 20px;
        align-items: center;
        padding: 20px;
    }

    .case-number-input
    {
        width:60%;
    }

    .nav-link{
        padding: 10px;
        font-size: 18px;
        font-weight: 500;
    }

    .update-ticket-limit,.update-distribution-time
    {
        width: 20%;
        margin-left: 5%;
        border-radius: 5px;
    }

    .update-return-times-status {
            width: 10%;
        }

    @media(max-width:450px)
    {
        .update-return-times-status {
            width: 30%;
        }
    }
</style>
@stop

@if (Session::has('success'))
    <div class="alert alert-success">
        {{ Session::get('success') }}
    </div>
@endif
<?php
    // Fetch user with role '2'
    $user = DB::table('users')->where('role', '2')->first();
    
    // Check if user exists and is_enabled field is set
    if ($user && $user->is_enabled == 1) {
        $status = 'Enabled';
    } else {
        $status = 'Disabled';
    }
    
    $admin = DB::table('users')->where('role', '1')->first();
?>

<div class="mt-5" style="display: flex; justify-content:flex-start; align-items:center;">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-warning btn-lg px-5 border border-dark py-1 mb-2" style="border: 1px solid #343a40;">Main Menu</a>
</div>
<div class="card p-3 shadow">
    <h2 class="text-center p-3">System Settings</h2>
    <nav>
        <div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
            <button class="nav-link active" id="nav-psw-db-tab" data-bs-toggle="tab" data-bs-target="#navPasswordDb" type="button" role="tab" aria-controls="navPasswordDb" aria-selected="true">Password and Database Management</button>
            <button class="nav-link" id="nav-memos-tab" data-bs-toggle="tab" data-bs-target="#navMemos" type="button" role="tab" aria-controls="navMemos" aria-selected="false">Memos</button>
            <button class="nav-link" id="nav-days-dates-tab" data-bs-toggle="tab" data-bs-target="#navDaysDates" type="button" role="tab" aria-controls="navDaysDates" aria-selected="false">Login Days and Dates</button>
            <button class="nav-link" id="nav-notification-msgs-tab" data-bs-toggle="tab" data-bs-target="#navNotificationMsgs" type="button" role="tab" aria-controls="navNotificationMsgs" aria-selected="false">Notification Messages</button>
            <button class="nav-link" id="nav-manage-tickets-tab" data-bs-toggle="tab" data-bs-target="#navManageTickets" type="button" role="tab" aria-controls="navManageTickets" aria-selected="false">Tickets Limit</button>
            <button class="nav-link" id="nav-return-times-tab" data-bs-toggle="tab" data-bs-target="#navReturnTimes" type="button" role="tab" aria-controls="navReturnTimes" aria-selected="false">Return Times</button>
            <button class="nav-link" id="nav-distribution-mgt-tab" data-bs-toggle="tab" data-bs-target="#navDistributionMgt" type="button" role="tab" aria-controls="navDistributionMgt" aria-selected="false">Manage Distribution Times</button>
            <!-- <button class="nav-link" id="nav-ticket-reset-tab" data-bs-toggle="tab" data-bs-target="#navTicketReset" type="button" role="tab" aria-controls="navTicketReset" aria-selected="false">Reset Ticket Number</button> -->
        </div>
    </nav>
    <div class="tab-content p-3 border bg-light" id="nav-tabContent">
        <div class="tab-pane fade active show" id="navPasswordDb" role="tabpanel" aria-labelledby="nav-psw-db-tab">
            <div class="row mt-4">
                <div class="col-md-6 mt-4">
                    <form action="admin-password/{{ $admin->id }}" method="post">
                        @csrf
                        <h3 style="text-align: center;">Update Admin Password</h3>
                        <div class="update-password-form">
                            <input type="text" name="casenumber"
                            placeholder="Enter New Password" class="form-control case-number-input" />
                            <button type="submit"
                            class="btn btn-warning btn-lg px-5 border border-dark py-1 mb-2">Save</button>
                        </div>
                    </form>
                    <span class="mt-2">Current Admin Password: {{ $admin->case_number }} </span>
                </div>
                <div class="col-md-6 mt-4">
                    <h3 style="text-align: center;">Load New Database File</h3>
                    <div class="update-password-form">
                        <form action="{{ route('admin.import.excel') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input class="form-control" type="file" name="file" id="formFile">
                            <button type="submit"
                        class="btn btn-warning btn-lg px-5 border border-dark py-1 mb-2">Save</button>
                        </form>
                    </div>
    
                    <a class="btn btn-success mx-auto" href="{{ route('admin.export.excel') }}">Export Users Data</a>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="navMemos" role="tabpanel" aria-labelledby="nav-memos-tab">
            <div class="mx-auto">
                <h3 style="text-align: center;">Manage memos</h3>
                <div style="display: flex; justify-content:flex-end">
                    <a class="btn btn-warning add-new-memo btn-sm">Add New</a>
                </div>
                <table id="popUpMsgsTable" class="table table-striped table-bordered" style="width:100%;">
                    <thead>
                    <tr>
                            <td>Message</td>
                            <td>Enabled</td>
                            <td>Action</td>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="navReturnTimes" role="tabpanel" aria-labelledby="nav-return-times-tab">
             <?php use Illuminate\Support\Facades\DB;
                $max_tickets=DB::table('tickets_management')->select('return_times_status','ticket_limit_status','ticket_limit','d_start_time','d_end_time')->first();
            ?>
            <div class="mx-auto">
                <div style="display: flex; gap: 5px;flex-direction: column;">
                    <h5>Enable Return Times For Tickets?</h5>
                    <div style="display: flex;gap: 40px;">
                        <label for="yesEnableTicketReturnTimes">
                            <input type="radio" id="yesEnableTicketReturnTimes" {{ ($max_tickets->return_times_status=="1")? "checked" : "" }} class="enable-return-times" name="manage_return_times" value="1">Yes
                        </label>
                        <label for="noEnableTicketReturnTimes">
                            <input type="radio" id="noEnableTicketReturnTimes" {{ ($max_tickets->return_times_status=="0")? "checked" : "" }} class="enable-return-times" name="manage_return_times" value="0">No
                        </label>
                    </div>
                    <button type="submit" class="btn btn-warning btn-md update-return-times-status">Save</button>
                </div>

                <h3 style="text-align: center;">Manage Return Times</h3>
                <div style="display: flex; justify-content:flex-start">
                    <a class="btn btn-warning add-return-time btn-md">Add New</a>
                </div>
                <table id="ticketReturnTimesTable" class="table table-striped table-bordered" style="width:100%;">
                    <thead>
                    <tr>
                            <td>Start</td>
                            <td>End</td>
                            <td>Time</td>
                            <td>Action</td>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="navDaysDates" role="tabpanel" aria-labelledby="nav-days-dates-tab">
            <div class="row mt-5 mx-auto">
                <div class="col-md-6">
                    <h4 style="text-align: center;">Manage Login Day/s</h4>
                    <div>
                        <a class="btn btn-warning add-new-day btn-md">Add New</a>
                    </div>
                    <table id="addedLoginDaysTable" class="table table-striped table-bordered" style="width:100%;">
                        <thead>
                        <tr>
                                <td>Day</td>
                                <td>Start Time</td>
                                <td>End Time</td>
                                <td>Action</td>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <h4 style="text-align: center;">Added Dates</h4>
                    <div>
                        <a class="btn btn-warning add-new-date btn-md">Add New</a>
                    </div>
                    <table id="addedDatesTable" class="table table-striped table-bordered" style="width:100%;">
                        <thead>
                        <tr>
                            <td>Date</td>
                            <td>Start Time</td>
                            <td>End Time</td>
                            <td>Action</td>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="navNotificationMsgs" role="tabpanel" aria-labelledby="nav-notification-msgs-tab">
            <div class="mx-auto">
                <h3 style="text-align: center;">Manage Notification Messages</h3>
                <table id="notificationMsgsTable" class="table table-striped table-bordered" style="width:100%;">
                    <thead>
                    <tr>
                            <td>Title</td>
                            <td>Message</td>
                            <td>Action</td>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="navManageTickets" role="tabpanel" aria-labelledby="nav-manage-tickets-tab">
            <div class="mx-auto" style="display: flex;flex-direction: column;gap: 5px;">
                @if($max_tickets->ticket_limit_status == 1)
                    <?php
						$maxtickets=$max_tickets->ticket_limit;
					?>
				@else
                    <?php
						$maxtickets='';
					?> 
				@endif
                <input type="number" style="padding: 20px;width: 40%;" autocomplete="off" class="form-control" value="{{ $maxtickets }}" id="maxTickets" placeholder="Maximum Tickets" />
                <h5>Enable Ticket Limit?</h5>
                <div style="display: flex;gap: 40px;">
                    <label for="yesEnableTicketLimit"><input type="radio" id="yesEnableTicketLimit" class="enable-ticket-limit" name="enable_ticket_limit" value="1">Yes</label>
                    <label for="noEnableTicketLimit"><input type="radio" id="noEnableTicketLimit" class="enable-ticket-limit" name="enable_ticket_limit" value="0">No</label>
                </div>
                <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                <button type="submit"
                class="btn-warning border border-dark mb-2 update-ticket-limit">Save</button>
            </div>
        </div>
        <div class="tab-pane fade" id="navDistributionMgt" role="tabpanel" aria-labelledby="nav-distribution-mgt-tab">
            <div class="mx-auto" style="display: flex;flex-direction: column;gap: 5px;">
                <div style="display: flex;gap: 20px;align-items: center;">
                    <div class="form-group inputdetails" style="text-align:left;">
                        <label>Start time</label>
                        <input type="text" value="{{ $max_tickets->d_start_time}}" class="form-control text-dark time-picker d-start-time" name="start_time" autocomplete="off" placeholder="Enter start time">
                    </div>
                    <div class="form-group inputdetails" style="text-align:left;">
                        <label>End time</label>
                        <input type="text" value="{{ $max_tickets->d_end_time}}" class="form-control text-dark time-picker d-end-time" name="end_time" autocomplete="off" placeholder="Enter end time">
                    </div>
                </div>
                <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                <button type="submit" class="btn-warning border border-dark mb-2 update-distribution-time">Save</button>
            </div>
        </div>
        <!-- <div class="tab-pane fade" id="navTicketReset" role="tabpanel" aria-labelledby="nav-ticket-reset-tab">
            <div class="mx-auto" style="display: flex;
                flex-direction: column;
                justify-content: space-around;
                align-items: center;
                gap: 5px;">
                <div style="display: flex;gap: 20px;align-items: center;">
                    <div class="form-group inputdetails">
                        <label>Current Ticket in Queue is </label>
                        <span id="currentTicketInqueue"></span>
                    </div>
                </div>
                <button type="submit" class="btn-warning btn-lg border border-dark mb-2 reset-ticket-to-zero">Reset</button>
            </div>
        </div> -->
    </div>
</div>

<div class="modal fade" id="addEditReturnTimeModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
           <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center add-edit-return-time-header" id="custom-width-modalLabel"></h4>
            </div>
            <form method="POST" class="add-edit-return-time-form">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="current_return_id" class="current-return-id">
                    <div class="row section-groups m-2">
                        <div class="form-group inputdetails col-sm-6" style="text-align:left;">
                            <label>Start Ticket</label>
                            <input type="text" class="form-control text-dark start-ticket" name="start_ticket" autocomplete="off" placeholder="Enter start ticket">
                        </div>
                        <div class="form-group inputdetails col-sm-6" style="text-align:left;">
                            <label>End Ticket</label>
                            <input type="text" class="form-control text-dark end-ticket" name="end_ticket" autocomplete="off" placeholder="Enter end ticket">
                        </div>
                    </div>
                    
                    <div class="row section-groups m-2">
                        <div class="form-group inputdetails col-sm-6" style="text-align:left;">
                            <label>Time</label>
                            <input type="text" class="form-control text-dark time-picker return-time" name="return_time" autocomplete="off" placeholder="Time(hh:mm AM/PM)">
                        </div>
                    </div>
                </div>
            
                <div class="modal-footer">
                    <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                    <button type="button" class="btn btn-default waves-effect close-modal" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger waves-effect">Save</button>
                </div>
            </form>   
           </div>
        </div>
    </div>

<div class="modal fade" id="changePopupMessageModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
           <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center confirm-status-header" id="custom-width-modalLabel"></h4>
             </div>
             <form method="POST" class="confirm-status-form">
                @csrf
                <div class="modal-body">
                   <div class="row section-groups">
                      <input type="hidden" name="memo_id" class="memo-id">
                   </div>
                </div>
            
                <div class="modal-footer">
                   <button type="button" class="btn btn-warning waves-effect close-modal" data-dismiss="modal">Close</button>
                   <button type="submit" class="btn btn-danger waves-effect">Save</button>
                </div>
             </form> 
           </div>
        </div>
    </div>

    <div class="modal fade" id="addEditPopupMessageModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg add-edit-msg-modal-dialog">
           <div class="modal-content add-edit-msg-modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center add-edit-message-header" id="custom-width-modalLabel"></h4>
             </div>
             <form method="POST" class="edit-memo-form">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="memo_id" class="memo-id">
                 <div class="row section-groups m-2">
                    <div class="col-12">
                        <label>Message</label>
                        {{-- <div class="form-control text-dark message">

                        </div> --}}
                        <textarea class="form-control text-dark message" placeholder="Enter Message" name="message" rows="7"></textarea>
                    </div>
                 </div>
                </div>
            
                <div class="modal-footer">
                    <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                   <button type="button" class="btn btn-warning waves-effect close-modal" data-dismiss="modal">Close</button>
                   <button type="submit" class="btn btn-danger waves-effect">Save</button>
                </div>
             </form>
           </div>
        </div>
    </div>

    <div class="modal fade" id="viewPopupMessageModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg view-msg-modal-dialog">
           <div class="modal-content view-msg-modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center view-message-header" id="custom-width-modalLabel">Memo Details</h4>
             </div>
             
                <div class="modal-body">
                    <div class="row section-groups m-2">
                        <div class="col-12">
                            <p class="view-msg-div"></p>
                        </div>
                    </div>
                </div>
            
                <div class="modal-footer">
                    <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                   <button type="button" class="btn btn-warning waves-effect close-modal" data-dismiss="modal">Close</button>
                </div>
           </div>
        </div>
    </div>

    <div class="modal fade" id="addEditDayTimeModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
           <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center add-edit-day-time-header" id="custom-width-modalLabel"></h4>
             </div>
             <form method="POST" class="add-edit-day-time-form">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="current_day_id" class="current-day-id">
                   <div class="row section-groups m-2">
                    <div class="form-group inputdetails col-12 selected-day-div">
                        <label class="labeldropdown">Day</label>
                        <select class="admin-select2 form-control text-dark selected-date-id" required style="width: 100%;">
                            <option disabled val=''>Select Day</option>
                            <option value="monday">Monday</option>
                            <option value="tuesday">Tuesday</option>
                            <option value="wednesday">Wednesday</option>
                            <option value="thursday">Thursday</option>
                            <option value="friday">Friday</option>
                            <option value="saturday">Saturday</option>
                            <option value="sunday">Sunday</option>
                        </select>
                    </div>
                 </div>
                 <div class="row section-groups m-2">
                    <div class="form-group inputdetails col-sm-6" style="text-align:left;">
                        <label>Start time</label>
                        <input type="text" class="form-control text-dark time-picker start-time" name="start_time" autocomplete="off" placeholder="Enter start time">
                    </div>
                    <div class="form-group inputdetails col-sm-6" style="text-align:left;">
                        <label>End time</label>
                        <input type="text" class="form-control text-dark time-picker end-time" name="end_time" autocomplete="off" placeholder="Enter end time">
                    </div>
                 </div>
                </div>
            
                <div class="modal-footer">
                    <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                   <button type="button" class="btn btn-warning waves-effect close-modal" data-dismiss="modal">Close</button>
                   <button type="submit" class="btn btn-danger waves-effect">Save</button>
                </div>
             </form>
           </div>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteDayModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
           <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center confirm-delete-header" id="custom-width-modalLabel"></h4>
             </div>
             <form method="POST" class="confirm-delete-day-form">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="delete_id" class="delete-id">
                    <div class="row section-groups m-2">
                        <div class="col-sm-12">
                            <p>Are You sure you want to Delete?</p>
                        </div>
                    </div>
                </div>
            
                <div class="modal-footer">
                    <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                   <button type="button" class="btn btn-warning waves-effect close-modal" data-dismiss="modal">Close</button>
                   <button type="submit" class="btn btn-danger waves-effect">Save</button>
                </div>
             </form> 
           </div>
        </div>
    </div>

    <div class="modal fade" id="resetQueueTicketsModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
           <div class="modal-content">
             <div class="modal-body">
                    <h4 style="font-size: 1.4rem;text-align: center;">Are you sure you want to reset ticket to 0?</h4>
                    <div class="row section-groups m-2">
                        <div class="col-sm-12">
                            <p style="text-align: left;
    font-size: 1.2rem;">Resetting ticket to 0 will lead to all the current tickets being reset and the tickets will be lost. Kindly confirm the tickets before resetting!</p>
                        </div>
                    </div>
                </div>
            
                <div class="modal-footer">
                    <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                   <button type="button" class="btn btn-danger waves-effect close-modal" data-dismiss="modal">Close</button>
                   <button type="submit" class="btn btn-warning waves-effect confirm-reset-ticket-to-zero">Yes</button>
                </div>
           </div>
        </div>
    </div>

    {{-- manage Dates Modals --}}
    <div class="modal fade" id="addEditDateTimeModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
           <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center add-edit-day-time-header" id="custom-width-modalLabel"></h4>
            </div>
            <form method="POST" class="add-edit-date-time-form">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="current_date_id" class="current-date-id">
                    <div class="row section-groups m-2">
                        <div class="form-group inputdetails col-12">
                            <label class="labeldropdown">Date</label>
                            <input type="text" class="form-control text-dark date" autocomplete="off" id="addedDate" name="date" placeholder="Enter date">
                        </div>
                    </div>
                    <div class="row section-groups m-2">
                        <div class="form-group inputdetails col-sm-6" style="text-align:left;">
                            <label>Start time</label>
                            <input type="text" class="form-control text-dark time-picker start-time" name="start_time" autocomplete="off" placeholder="Enter start time">
                        </div>
                        <div class="form-group inputdetails col-sm-6" style="text-align:left;">
                            <label>End time</label>
                            <input type="text" class="form-control text-dark time-picker end-time" name="end_time" autocomplete="off" placeholder="Enter end time">
                        </div>
                    </div>
                </div>
            
                <div class="modal-footer">
                    <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                    <button type="button" class="btn btn-warning waves-effect close-modal" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger waves-effect add-edit-date-btn">Save</button>
                </div>
            </form>   
           </div>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteDateModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-center confirm-delete-header" id="custom-width-modalLabel"></h4>
                 </div>
                 <form method="POST" class="confirm-delete-date-form">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="delete_id" class="delete-id">
                        <div class="row section-groups m-2">
                            <div class="col-sm-12">
                                <p>Are You sure you want to Delete?</p>
                            </div>
                        </div>
                    </div>
                
                    <div class="modal-footer">
                        <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                       <button type="button" class="btn btn-warning waves-effect close-modal" data-dismiss="modal">Close</button>
                       <button type="submit" class="btn btn-danger waves-effect">Save</button>
                    </div>
                 </form>
            </div>
        </div>
    </div>

    {{-- manage notification msgs --}}
    <div class="modal fade" id="editNotificationMessageModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg edit-notification-msg-modal-dialog">
           <div class="modal-content edit-notification-msg-modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center edit-notification-msg-header" id="custom-width-modalLabel">Edit Notification Message</h4>
             </div>
             <form method="POST" class="edit-notification-msg-form">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="notification_msg_id" class="notification-msg-id">
                     <div class="row section-groups m-2">
                        <div class="form-group inputdetails col-12">
                            <label class="labeldropdown">Title</label>
                            <input type="text" class="form-control text-dark title" readonly name="title">
                        </div>
                    </div>
                    <div class="row section-groups m-2">
                        <div class="col-12">
                            <label>Message</label>
                            <textarea class="form-control text-dark message" placeholder="Enter Message" name="message" rows="7"></textarea>
                        </div>
                    </div>
                </div>
            
                <div class="modal-footer">
                    <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                   <button type="button" class="btn btn-warning waves-effect close-modal" data-dismiss="modal">Close</button>
                   <button type="submit" class="btn btn-danger waves-effect">Save</button>
                </div>
             </form>
           </div>
        </div>
    </div>

    <div class="modal fade" id="viewNotificationMessageModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg view-notification-msg-modal-dialog">
           <div class="modal-content view-notification-msg-modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center" id="custom-width-modalLabel">Notification Message Details</h4>
             </div>
             
                <div class="modal-body">
                    <div class="row section-groups m-2">
                        <div class="col-12">
                            <p class="view-notification-msg-div"></p>
                        </div>
                    </div>
                </div>
            
                <div class="modal-footer">
                    <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                   <button type="button" class="btn btn-warning waves-effect close-modal" data-dismiss="modal">Close</button>
                </div>
           </div>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteDateModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-center confirm-delete-header" id="custom-width-modalLabel"></h4>
                 </div>
                 <form method="POST" class="confirm-delete-date-form">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="delete_id" class="delete-id">
                        <div class="row section-groups m-2">
                            <div class="col-sm-12">
                                <p>Are You sure you want to Delete?</p>
                            </div>
                        </div>
                    </div>
                
                    <div class="modal-footer">
                        <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                       <button type="button" class="btn btn-warning waves-effect close-modal" data-dismiss="modal">Close</button>
                       <button type="submit" class="btn btn-danger waves-effect">Save</button>
                    </div>
                 </form>
            </div>
        </div>
    </div>
@endsection

@section('admin_tool_script')
{{-- datatable --}}
<script type="text/javascript" src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script> 
<script type="text/javascript" src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>

{{-- alertify --}}
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>

{{-- select2 --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- timepicker --}}
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>

{{-- jquery ui plugins --}}
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>

<!-- sweetalert -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

{{-- tiny mce --}}
<script src="https://cdn.tiny.cloud/1/4sxjlpvl7c4jfn3yhm5pjar918m5gb48cio5o9felm9oqhui/tinymce/7/tinymce.min.js"></script>

{{-- moment js --}}
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script> 

<script>

    let theEditor;

    document.addEventListener('DOMContentLoaded', function() {

        $(document).ready(function() {

            // alert('Ruto must go');
            
            var loginStatus = {{ $loginStatus }}

            if(loginStatus == 1)
            {
                $('.check-login-status').prop("checked",true).attr("login-status",'1') ;
            }

            if(loginStatus == 0)
            {
                $('.check-login-status').prop("checked",false).attr("login-status",'0') 
            }

            var maxTicketsVal=$('#maxTickets').val();

            if(maxTicketsVal !== '')
            {
                $('#yesEnableTicketLimit').prop("checked",true);
                $('#noEnableTicketLimit').prop("checked",false);
            } else {
                $('#yesEnableTicketLimit').prop("checked",false);
                $('#noEnableTicketLimit').prop("checked",true);
            }

            // $('.admin-select2').select2();

            $('.time-picker').timepicker({
                timeFormat: 'h:mm p',
                interval: 30,
                minTime: '7:00am',
                // maxTime: '12:00pm',
                // defaultTime: '08',
                startTime: '7:00am',
                dynamic: true,
                dropdown: true,
                scrollbar: true
            });

            $('#notificationMsgsTable').DataTable({
                columnDefs: [{
                    "defaultContent": "-",
                    "targets": "_all",
                    "className": "dt-center", "targets": "_all"
                    }],
                "iDisplayLength":25,
                processing:true,
                serverside:true,
                responsive:true,
                "ajax": {
                    "url": "/admin/get-notification-msgs",
                    "type": "get",
                },
                columns: [
                    {  data: 'title',name:'title',orderable:true,searchable:true},
                    {  data: 'message',name:'message',orderable:true,searchable:true },
                    {  data: 'action',name:'action',orderable:false,searchable:false }
                ],
            });

            
            $('#ticketReturnTimesTable').DataTable({
                columnDefs: [{
                    "defaultContent": "-",
                    "targets": "_all",
                    "className": "dt-center", "targets": "_all"
                    }],
                "iDisplayLength":25,
                processing:true,
                serverside:true,
                responsive:true,
                "ajax": {
                    "url": "/admin/get-return-times",
                    "type": "get",
                },
                columns: [
                    {  data: 'start_ticket',name:'start_ticket',orderable:true,searchable:true},
                    {  data: 'end_ticket',name:'end_ticket',orderable:true,searchable:true },
                    {  data: 'time',name:'time',orderable:true,searchable:true },
                    {  data: 'action',name:'action',orderable:false,searchable:false }
                ],
            });

            $('#popUpMsgsTable').DataTable({
                columnDefs: [{
                    "defaultContent": "-",
                    "targets": "_all",
                    "className": "dt-center", "targets": "_all"
                    }],
                "iDisplayLength":25,
                processing:true,
                serverside:true,
                responsive:true,
                "ajax": {
                    "url": "/admin/get-memos",
                    "type": "get",
                },
                columns: [
                    {  data: 'message',name:'message',orderable:true,searchable:true},
                    {  data: 'is_enabled',name:'is_enabled',orderable:true,searchable:true },
                    {  data: 'action',name:'action',orderable:false,searchable:false }
                ],
            });

            $('#addedDatesTable').DataTable({
                columnDefs: [{
                    "defaultContent": "-",
                    "targets": "_all",
                    "className": "dt-center", "targets": "_all"
                    }],
                "iDisplayLength":25,
                processing:true,
                serverside:true,
                responsive:true,
                "ajax": {
                    "url": "/admin/get-added-dates",
                    "type": "get",
                },
                columns: [
                    {  data: 'date',name:'date',orderable:true,searchable:true },
                    {  data: 'start_time',name:'start_time',orderable:true,searchable:true},
                    {  data: 'end_time',name:'end_time',orderable:true,searchable:true },
                    {  data: 'action',name:'action',orderable:false,searchable:false }
                ],
            });

            $('#addedLoginDaysTable').DataTable({
                columnDefs: [{
                    "defaultContent": "-",
                    "targets": "_all",
                    "className": "dt-center", "targets": "_all"
                    }],
                "iDisplayLength":25,
                processing:true,
                serverside:true,
                responsive:true,
                "ajax": {
                    "url": "/admin/get-login-days",
                    "type": "get",
                },
                columns: [
                    {  data: 'day',name:'day',orderable:true,searchable:true },
                    {  data: 'start_time',name:'start_time',orderable:true,searchable:true},
                    {  data: 'end_time',name:'end_time',orderable:true,searchable:true },
                    {  data: 'action',name:'action',orderable:false,searchable:false }
                ],
            });

            var activeDates = @json($activeDates);

            console.log(activeDates);

            // datepicker for selecting the date of birth
            $( function() {
                $( "#addedDate" ).datepicker(
                    {
                        dateFormat: 'mm/dd/yy',
                        minDate: 0
                    }
                );
            });

            // function updateCurrentTicket() {

            //     var url = '/admin/current-redis-ticket';

            //     $.ajax({
            //         url: url,
            //         method: 'GET',
            //         success: function(response)
            //         {
            //             console.log(response);
            //             document.getElementById('currentTicketInqueue').textContent = 
            //             `${response}`;
            //         }
            //     });
            //     // fetch('/admin/current-redis-ticket')
            //     //     .then(response => response.json())
            //     //     .then(data => {
            //     //         if (data.success) {

            //     //             console.log(data);

            //     //             // document.getElementById(currentTicketInqueue').textContent = 
            //     //             //     `Current Ticket: ${data.current_ticket}`;
            //     //         }
            //     //     });
            // }

            // // Initial load
            // updateCurrentTicket();

            // // Auto-refresh
            // setInterval(updateCurrentTicket, 1000);

            // tinymce

            
        });

        $(document).on('click','.reset-ticket-to-zero',function(e){

            e.preventDefault();
            
            $('#resetQueueTicketsModal').modal('toggle');
        });

        $(document).on('click','.confirm-reset-ticket-to-zero',function(e){

            e.preventDefault();

            var postData = {
                submission_type:3,
                _token: '{{ csrf_token() }}'
            };

            $.ajax({
                url: '/manage-tickets-generation',
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(response) {

                    console.log(response);

                    if (response.status == 200)
                    {
                        $('#resetQueueTicketsModal').modal('hide');
                        swal('Ticket in queue reset successfully.');
                    }
                }
            });
        })

        // function showResetConfirmation() {
        //     Swal.fire({
        //         title: "Are you sure you want to reset ticket to 0?",
        //         text: "Resetting ticket to 0 will lead to all the current tickets being reset and the tickets will be lost. Kindly confirm the tickets before resetting!",
        //         icon: "warning",
        //         showCancelButton: true,
        //         confirmButtonColor: "#3085d6",
        //         cancelButtonColor: "#d33",
        //         confirmButtonText: "Yes"
        //     }).then((result) => {
        //         if (result.isConfirmed) {
        //             var postData = {
        //                 _token: '{{ csrf_token() }}'
        //             };

        //             $.ajax({
        //                 url: '/admin/reset-redis-ticket',
        //                 type: 'POST',
        //                 data: postData,
        //                 dataType: 'json',
        //                 success: function(response) {
        //                     Swal.fire({
        //                         title: "Success!",
        //                         text: 'Ticket in queue reset successfully to 0.',
        //                         icon: "success"
        //                     });
        //                 },
        //                 error: function(xhr) {
        //                     Swal.fire({
        //                         title: "Error!",
        //                         text: xhr.responseJSON.message || 'An error occurred while resetting',
        //                         icon: "error"
        //                     });
        //                 }
        //             });
        //         }
        //     });
        // }

        $(document).on('change', '.enable-ticket-limit', function(e) {
            e.preventDefault();

            var ticketLimitVal = $(this).val();

            if(ticketLimitVal == 0)
            {
                $('#maxTickets').val('');
            }
        });

        $(document).on('keyup', '#maxTickets', function(e) {
            e.preventDefault();

            $("#noEnableTicketLimit").prop("checked", false);
            $("#yesEnableTicketLimit").prop("checked", true);
        });

        $(document).on('click', '.update-return-times-status', function(e) {
            e.preventDefault();

            var return_times_status = $('input[name="manage_return_times"]:checked').val();

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            var url = '/admin/manage-ticket-return-times';
                
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    return_times_status: return_times_status,
                    _token: csrf
                },
                success: function(response) {

                    console.log(response);

                    swal(response.message);
                }
            });
        });

        $(document).on('click', '.update-ticket-limit', function(e) {
            e.preventDefault();

            var ticketLimitVal = $('input[name="enable_ticket_limit"]:checked').val();

            var maxTickets = $('#maxTickets').val();

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            var url = '/admin/manage-ticket-limit';
                
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    ticketLimitVal:ticketLimitVal,
                    maxTickets:maxTickets,
                    _token: csrf
                },
                success: function(response) {

                    console.log(response);

                    if (response.status == 200) {

                        $('.error-list').html(" ").addClass('d-none');

                        $("#noEnableTicketLimit").prop("checked", false);

                        swal(response.message);

                    } else if (response.status == 201) {
                        $('.error-list').html(" ").addClass('d-none');
                        $('#maxTickets').val('');
                        swal(response.message);

                        $("#yesEnableTicketLimit").prop("checked", false);

                    } else if (response.status == 405) {
                        $('.error-list').html(" ").removeClass('d-none');
                        $.each(response.message, function(key, err_value) {
                            $('.error-list').append('<li>' + err_value + '</li>');
                        })

                        $("#noEnableTicketLimit").prop("checked", true);
                        $("#yesEnableTicketLimit").prop("checked", false);
                    }
                }
            });
        });

        $(document).on('click', '.update-distribution-time', function(e) {
            e.preventDefault();

            var start_time = $('.d-start-time').val();

            var end_time = $('.d-end-time').val();

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            var url = '/admin/manage-distribution-times';
                
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    start_time:start_time,
                    end_time:end_time,
                    _token: csrf
                },
                success: function(response) {

                    console.log(response);

                    if (response.status == 200) {

                        $('.error-list').html(" ").addClass('d-none');

                        $("#noEnableTicketLimit").prop("checked", false);

                        swal(response.message);

                    } else if (response.status == 201) {
                        $('.error-list').html(" ").addClass('d-none');
                        $('#maxTickets').val('');
                        swal(response.message);

                        $("#yesEnableTicketLimit").prop("checked", false);

                    } else if (response.status == 405) {
                        $('.error-list').html(" ").removeClass('d-none');
                        $.each(response.message, function(key, err_value) {
                            $('.error-list').append('<li>' + err_value + '</li>');
                        })

                        $("#noEnableTicketLimit").prop("checked", true);
                        $("#yesEnableTicketLimit").prop("checked", false);
                    }
                }
            });
        });

        $(document).on('focusin', function(e) {
            if ($(e.target).closest(".tox").length) {
                e.stopImmediatePropagation();
            }
        });

        $(document).on('change', '.check-login-status', function(e) {
            e.preventDefault();

            var loginStatus = $(this).attr('login-status');

            $('#manageUserLoginModal').modal('toggle');

            if(loginStatus == 1)
            {
                $('.manage-user-login-text').text('Disable User Login')
                $('.manage-user-login-btn').attr('login-status','0')
            } else {
                $('.manage-user-login-text').text('Enable User Login')
                $('.manage-user-login-btn').attr('login-status','1')
            }
        });

        $(document).on('click', '.manage-user-login-btn', function(e) {
            e.preventDefault();

            var status=$(this).attr('login-status');

            var url = '/admin/manage-user-login';

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            $.ajax({
                url: url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: function(){
                    var data = new FormData();
                    data.append('status',status);
                    data.append('_token' , csrf);
                    return data;
                    console.log(data)
                }(),
                success: function(response) {

                    console.log(response);

                    if (response.status == 200) {
                        
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.message);

                        $('#manageUserLoginModal').modal('hide');

                        $('.check-login-status').attr('login-status',response.is_enabled);

                        if(response.is_enabled == 1)
                        {
                            $('.check-login-status').prop("checked",true)
                        }

                        if(response.is_enabled == 0)
                        {
                            $('.check-login-status').prop("checked",false)
                        }

                    }
                }
            });

        });


        $(document).on('click', '.manage-user-login-close-modal', function(e) {
            e.preventDefault();

            var currentStatus=$('.manage-user-login-btn').attr('login-status');

            $('#manageUserLoginModal').modal('hide');

            if(currentStatus == 0)
            {
                $('.check-login-status').prop("checked",true) 
            } else if (currentStatus == 1)
            {
                $('.check-login-status').prop("checked",false) 
            }

        });

        $(document).on('click', '.close-modal', function(e) {
            e.preventDefault();

            $('.error-list').html(" ").addClass('d-none');

            $('#resetQueueTicketsModal').modal('hide');

            $('#confirmDeleteReturnTimeModal').modal('hide');
            
            $('#addEditReturnTimeModal').modal('hide');

            $('#changePopupMessageModal').modal('hide');

            $('#addEditPopupMessageModal').modal('hide');

            $('#addEditDayTimeModal').modal('hide');

            $('#confirmDeleteDayModal').modal('hide');

            $('#addEditDateTimeModal').modal('hide');

            $('#viewPopupMessageModal').modal('hide');

            $('#confirmDeleteDateModal').modal('hide');

            $('#editNotificationMessageModal').modal('hide');

            $('#viewNotificationMessageModal').modal('hide');
        });

        // manage notification msgs
        $(document).on('click', '.edit-notificationmsg', function() 
        {
            $('#editNotificationMessageModal').modal('toggle');
            var notificationMsgId=$(this).attr('notificationmsg-id');

            $('body').css("overflow", "hidden");

            $('.notification-msg-id').val(notificationMsgId);

            // tinymce.activeEditor.setContent(" ");

            $.ajax({
                url: '/admin/get-notification-msgs-details/' + notificationMsgId,
                method: 'GET',
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response)
                    if (response.status == 404) {
                        swal(response.message);
                    } else if (response.status == 200) {
                        $('.title').val(response.data.title);

                        const htmlParser= new DOMParser().parseFromString(response.data.message, 'text/html');
                        const textString= htmlParser.body.textContent;

                        $('.edit-notification-msg-form textarea').val(textString);
                        
                        // tinymce.activeEditor.setContent(response.data.message);
                    }


                }
            })
        });

        $(document).on('submit', '.edit-notification-msg-form', function(e) {
            e.preventDefault();

            var url = '/admin/update-notification-msgs';
            var form = $('.edit-notification-msg-form')[0];
            var formdata = new FormData(form);

            $.ajax({
                url: url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: formdata,
                success: function(response) {

                    console.log(response);

                    if (response.status == 200) {

                        $('.error-list').html(" ").addClass('d-none');
                        
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.message);

                        $('#editNotificationMessageModal').modal('hide');

                        $('#notificationMsgsTable').DataTable().ajax.reload(null, false);

                    } else if (response.status == 400) {
                        $('.error-list').html(" ").addClass('d-none');

                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.message);
                    } else if (response.status == 405) {
                        $('.error-list').html(" ").removeClass('d-none');
                        $.each(response.message, function(key, err_value) {
                            $('.error-list').append('<li>' + err_value + '</li>');
                        })
                    }

                }
            });
        });

        // view notification msg
        $(document).on('click', '.view-notificationmsg', function() 
        {
            $('#viewNotificationMessageModal').modal('toggle');
            var notificationMsgId=$(this).attr('notificationmsg-id');
            $('.view-msg-div').html('');

            $('body').css("overflow", "hidden");

            $.ajax({
                url: '/admin/get-notification-msgs-details/' + notificationMsgId,
                method: 'GET',
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response)
                    if (response.status == 404) {
                        swal(response.message);
                    } else if (response.status == 200) {
                        const htmlParser= new DOMParser().parseFromString(response.data.message, 'text/html');
                        const textString= htmlParser.body.textContent;
                        $('.view-notification-msg-div').html(textString)
                    }


                }
            })
        });

        // add new return time
        $(document).on('click', '.add-return-time', function()
        {
            $('.current-return-id,.start-ticket,.end-ticket,.return-time').val('');

            $('#addEditReturnTimeModal').modal('toggle');

            $('.error-list').html(" ").addClass('d-none');

            $('.add-edit-return-time-header').html('').html('Add New Return Time');
        });

        $(document).on('click', '.edit-time', function() 
        {
            $('#addEditReturnTimeModal').modal('toggle');
            var returnTimeId=$(this).attr('time-id');

            $('body').css("overflow", "hidden");

            $('.current-return-id').val(returnTimeId);

            $('.add-edit-message-header').html('')

            $.ajax({
                url: '/admin/get-return-time-details/' + returnTimeId,
                method: 'GET',
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response)
                    if (response.status == 404) {
                        swal(response.message);
                    } else if (response.status == 200) {
                        $('.start-ticket').val(response.data.start_ticket);
                        $('.end-ticket').val(response.data.end_ticket);
                        $('.return-time').val(response.data.time);
                        $('.add-edit-message-header').html('Edit Return Time Details')
                    }


                }
            })
        });

        $(document).on('submit', '.add-edit-return-time-form', function(e)
        {
            e.preventDefault();
            var url = '/admin/create-update-return-time';
            var form = $('.add-edit-return-time-form')[0];
            var formdata = new FormData(form);

            $.ajax({
                url:url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: formdata,
                success: function(response) {

                    console.log(response);

                    $('.error-list').html(" ").addClass('d-none');

                    if (response.status == 200) {
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.message);

                        $('#addEditReturnTimeModal').modal('hide');

                        $('#ticketReturnTimesTable').DataTable().ajax.reload(null, false);

                    } else if (response.status == 415) {
                        // $('.error-list').html(" ").addClass('d-none');

                        swal(response.message);
                        // alertify.set('notifier', 'position', 'top-center');
                        // alertify.success(response.message);
                    } else if (response.status == 405) {
                        $('.error-list').html(" ").removeClass('d-none');
                        $.each(response.message, function(key, err_value) {
                            $('.error-list').append('<li>' + err_value + '</li>');
                        })
                    } else if (response.status == 414) {
                        // $('.error-list').html(" ").addClass('d-none');

                        swal(response.message);
                    }

                }
            });
        });

        $(document).on('click', '.delete-time', function() 
        {
            $('#confirmDeleteReturnTimeModal').modal('toggle');

            var currentReturnTimeId=$(this).attr('time-id');

            $('body').css("overflow", "hidden");

            $('.delete-id').val(currentReturnTimeId);
        });

        $(document).on('submit', '.confirm-delete-return-id-form', function(e)
        {
            e.preventDefault();
            var url = '/admin/delete-return-time';

            var current_return_time_id=$('.delete-id').val();

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            $.ajax({
                url: url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: function(){
                    var data = new FormData();
                    data.append('current_return_time_id',current_return_time_id);
                    data.append('_token' , csrf);
                    return data;
                    console.log(data)
                }(),
                success: function(response) {

                    console.log(response);

                    if (response.status == 200) {
                        
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.message);

                        $('#confirmDeleteReturnTimeModal').modal('hide');

                        $('#ticketReturnTimesTable').DataTable().ajax.reload(null, false);

                    } else if (response.status == 404) {
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.message);
                    }

                }
            });
        });
        
        // enable disable memo
        $(document).on('click', '.memo-status-btn', function(e) {
            e.preventDefault();

            var memoId=$(this).attr('memo-id');

            var memoIsenabled=$(this).attr('memo-isenabled');

            $('#changePopupMessageModal').modal('show');

            $('body').css("overflow", "hidden");

            $('.memo-id').val(memoId);
            
            if(memoIsenabled == 0)
            {
                $('.confirm-status-header').text("Enable Memo Message?");
            } else {
                $('.confirm-status-header').text("Disable Memo Message?");
            }
        });

        $(document).on('submit','.confirm-status-form',function(e)
        {
            e.preventDefault();
            var form = $('.confirm-status-form')[0];
            var formdata = new FormData(form);

            $.ajax({
                url: '/admin/enable-disable-memo',
                method: 'POST',
                processData: false,
                contentType: false,
                data: formdata,
                success: function(response) {

                    console.log(response);
                    $('#changePopupMessageModal').modal('hide');

                    if (response.status == 200) {
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.msg);

                        $('#popUpMsgsTable').DataTable().ajax.reload(null, false);
                    }

                }
            });
        })

        // add memo
        $(document).on('click', '.add-new-memo', function()
        {
            $('#addEditPopupMessageModal').modal('toggle');

            $('.add-edit-message-header').html('');

            $('.memo-id').val('');

            $('.error-list').html(" ").addClass('d-none');

            // theEditor.setData( '' );
            // tinyMCE.activeEditor.setContent('');
            // tinymce.get(tinymce_editor_id).setContent('');

            $('.add-edit-message-header').html('Add New Memo');
        });

        // edit memo
        $(document).on('click', '.edit-memo', function() 
        {
            $('#addEditPopupMessageModal').modal('toggle');
            var memoId=$(this).attr('memo-id');

            $('body').css("overflow", "hidden");

            $('.memo-id').val(memoId);

            // $('.title').val('');
            // $('.message').val('');
            // tinymce.activeEditor.setContent(" ");

            $('.add-edit-message-header').html('')

            $.ajax({
                url: '/admin/get-memo-details/' + memoId,
                method: 'GET',
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response)
                    if (response.status == 404) {
                        swal(response.message);
                    } else if (response.status == 200) {
                        $('.title').val(response.data.title);
                                // var trimmedText=$(response.data.message).text();

                        // var msgNotification=response.data.message;

                        // const textMsgNotification= msgNotification.body.textContent;

                        // $('.edit-notification-msg-form textarea').val(textMsgNotification);
                        
                        // theEditor.setData(trimmedText);

                        // tinymce.activeEditor.setContent(response.data.message);
                        // tinymce.get(tinymce_editor_id).setContent(trimmedText);
                        // var msg=response.data.message;


                        // const textMsgData= msg.body.textContent;

                        const htmlParser= new DOMParser().parseFromString(response.data.message, 'text/html');
                        const textString= htmlParser.body.textContent;

                        // $('.edit-notification-msg-form textarea').val(textMsgNotification);

                        $('#addEditPopupMessageModal textarea').val(textString);
                        $('.add-edit-message-header').html('Edit Memo Details')
                    }


                }
            })
        });

        $(document).on('submit', '.edit-memo-form', function(e) {
            e.preventDefault();

            var url = '/admin/create-update-memo';
            var form = $('.edit-memo-form')[0];
            var formdata = new FormData(form);

            $.ajax({
                url: url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: formdata,
                success: function(response) {

                    console.log(response);

                    if (response.status == 200) {

                        $('.error-list').html(" ").addClass('d-none');
                        
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.message);

                        $('#addEditPopupMessageModal').modal('hide');

                        $('#popUpMsgsTable').DataTable().ajax.reload(null, false);

                    } else if (response.status == 400) {
                        $('.error-list').html(" ").addClass('d-none');

                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.message);
                    } else if (response.status == 405) {
                        $('.error-list').html(" ").removeClass('d-none');
                        $.each(response.message, function(key, err_value) {
                            $('.error-list').append('<li>' + err_value + '</li>');
                        })
                    }

                }
            });
        });

        // view memo
        $(document).on('click', '.view-memo', function() 
        {
            $('#viewPopupMessageModal').modal('toggle');
            var memoId=$(this).attr('memo-id');
            $('.view-msg-div').html('');

            $('body').css("overflow", "hidden");

            $.ajax({
                url: '/admin/get-memo-details/' + memoId,
                method: 'GET',
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response)
                    if (response.status == 404) {
                        swal(response.message);
                    } else if (response.status == 200) {
                        const htmlParser= new DOMParser().parseFromString(response.data.message, 'text/html');
                        const textString= htmlParser.body.textContent;
                        $('.view-msg-div').html(textString)
                    }


                }
            })
        });

        // add date and time and an alert on whether to disable the current selected day
        $(document).on('click', '.add-new-day', function()
        {
            $('#addEditDayTimeModal').modal('toggle');

            $('.error-list').html(" ").addClass('d-none');

            // $('.add-edit-day-time-header').html('');

            $(".selected-date-id").prop("disabled", false).val('').select2();

            $('.current-day-id,.start-time,.end-time').val('');

            $("#addedDate").prop("disabled", false);

            $('.add-edit-date-btn').prop("disabled", false).css("cursor", "pointer");

            $('.add-edit-day-time-header').html(" ").html('Add New Day');
        });

        $(document).on('click', '.edit-day', function() 
        {
            $('#addEditDayTimeModal').modal('toggle');

            $('.error-list').html(" ").addClass('d-none');

            // $('.add-edit-day-time-header').html('');

            var currentDayId=$(this).attr('day-id');

            $('body').css("overflow", "hidden");

            $('.current-day-id').val(currentDayId);

            // $(".selected-date-id").prop("disabled", true);
            
            $('.add-edit-date-btn').prop("disabled", false).css("cursor", "pointer");
            
            $('.start-time,.end-time').val('');

            $('.add-edit-day-time-header').html('').html('Edit Day Details');

            $.ajax({
                url: '/admin/get-login-day-details/' + currentDayId,
                method: 'GET',
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response)
                    if (response.status == 404) {
                        swal(response.message);
                    } else if (response.status == 200) {
                        $('.selected-date-id').val(response.data.day);
                        $('.start-time').val(response.data.start_time);
                        $('.end-time').val(response.data.end_time);
                    }


                }
            })
        });

        $(document).on('submit', '.add-edit-day-time-form', function(e) {
            e.preventDefault();

            var url = '/admin/create-update-login-day';

            var current_day_id=$('.current-day-id').val();

            var selected_day_id=$('.selected-date-id').val();

            var start_time=$('.start-time').val();

            var end_time=$('.end-time').val();

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            $('.add-edit-date-btn').prop("disabled", true).css("cursor", "not-allowed");

            $.ajax({
                url: url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: function(){
                    var data = new FormData();
                    data.append('current_day_id',current_day_id);
                    data.append('selected_day_id',selected_day_id);
                    data.append('_token' , csrf);
                    data.append('start_time' ,start_time);
                    data.append('end_time' ,end_time);
                    return data;
                    console.log(data)
                }(),
                success: function(response) {

                    console.log(response);

                    if (response.status == 200) {

                        $('.error-list').html(" ").addClass('d-none');

                        $('.selected-day-div .select2 .selection .select2-selection .select2-selection__rendered').html('').append(
                            '<li class="select2-selection__choice" title="Select Day" data-select2-id="21"><span class="select2-selection__choice__remove" role="presentation"></span>Select Day</li>'
                        );

                        $(".selected-date-id option[value='"+ response.selected_day +"']").prop("disabled", true).css({
                            cursor: "none"
                        });

                        $('.start-time').val('7:00am');
                        $('.end-time').val('3.30pm');

                        $('.add-edit-date-btn').prop("disabled", false).css("cursor", "pointer");

                        $('#addEditDayTimeModal').modal('hide');

                        $('#addedDatesTable').DataTable().ajax.reload(null, false);

                        $('#addedLoginDaysTable').DataTable().ajax.reload(null, false);

                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.message);

                    } else if (response.status == 405) {

                        $('.add-edit-date-btn').prop("disabled", false).css("cursor", "pointer");

                        $('.error-list').html(" ").removeClass('d-none');
                        $.each(response.message, function(key, err_value) {
                            $('.error-list').append('<li>' + err_value + '</li>');
                        })
                    }

                }
            });
        });

        $(document).on('click', '.delete-day', function() 
        {
            $('#confirmDeleteDayModal').modal('toggle');

            $('.confirm-delete-header').html('Delete Day');

            var currentDayId=$(this).attr('day-id');

            $('body').css("overflow", "hidden");

            $('.delete-id').val(currentDayId);
        });

        $(document).on('submit', '.confirm-delete-day-form', function(e) {
            e.preventDefault();

            var url = '/admin/delete-login-day';

            var current_day_id=$('.delete-id').val();

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            $.ajax({
                url: url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: function(){
                    var data = new FormData();
                    data.append('current_day_id',current_day_id);
                    data.append('_token' , csrf);
                    return data;
                    console.log(data)
                }(),
                success: function(response) {

                    console.log(response);

                    if (response.status == 200) {
                        
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.message);

                        $(".selected-date-id option[value='"+ response.deletedDay +"']").prop("disabled", false).css({
                            cursor: "pointer"
                        });

                        $('#confirmDeleteDayModal').modal('hide');

                        $('#addedDatesTable').DataTable().ajax.reload(null, false);

                        $('#addedLoginDaysTable').DataTable().ajax.reload(null, false);

                    } else if (response.status == 404) {
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.message);
                    }

                }
            });
        });

        // manage date
        $(document).on('click', '.add-new-date', function()
        {
            $('#addEditDateTimeModal').modal('toggle');

            $('.error-list').html(" ").addClass('d-none');

            $('.date,.current-date-id,.start-time,.end-time').val('');

            $("#addedDate").prop("disabled", false);

            $('.add-edit-day-time-header').html('').html('Add New Date');
        });


        $(document).on('click', '.edit-date', function() 
        {
            $('#addEditDateTimeModal').modal('toggle');

            $('.error-list').html(" ").addClass('d-none');

            var currentDateId=$(this).attr('date-id');

            $('body').css("overflow", "hidden");

            $('.current-date-id').val(currentDateId);

            $('.date,.start-time,.end-time').val('');

            $('.add-edit-date-time-header').html('').html('Edit Date Details');

            $.ajax({
                url: '/admin/get-added-date-details/' + currentDateId,
                method: 'GET',
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response)
                    if (response.status == 404) {
                        swal(response.message);
                    } else if (response.status == 200) {
                        // convert date format using moment
                        $('.date').val(moment(response.data.date).format('DD-MM-YYYY'));
                        $(".date").prop("disabled", true)
                        $('.start-time').val(response.data.start_time);
                        $('.end-time').val(response.data.end_time);
                    }


                }
            })
        });

        $(document).on('submit', '.add-edit-date-time-form', function(e) {
            e.preventDefault();

            var url = '/admin/create-update-added-date';
            var form = $('.add-edit-date-time-form')[0];
            var formdata = new FormData(form);

            $.ajax({
                url: url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: formdata,
                success: function(response) {

                    console.log(response);

                    if (response.status == 200) {

                        $('.error-list').html(" ").addClass('d-none');

                        $('.start-time').val('');
                        $('.end-time').val('');

                        $('#addEditDateTimeModal').modal('hide');

                        $('#addedDatesTable').DataTable().ajax.reload(null, false);

                        $('#addedLoginDaysTable').DataTable().ajax.reload(null, false);

                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.message);

                    } else if (response.status == 405) {
                        $('.error-list').html(" ").removeClass('d-none');
                        $.each(response.message, function(key, err_value) {
                            $('.error-list').append('<li>' + err_value + '</li>');
                        })
                    }

                }
            });
        });

        $(document).on('click', '.delete-date', function() 
        {
            $('#confirmDeleteDateModal').modal('toggle');

            $('.confirm-delete-header').html('Delete Date');

            var currentDateId=$(this).attr('date-id');

            $('body').css("overflow", "hidden");

            $('.delete-id').val(currentDateId);
        });

        $(document).on('submit', '.confirm-delete-date-form', function(e) {
            e.preventDefault();

            var url = '/admin/delete-added-date';

            var current_date_id=$('.delete-id').val();

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            $.ajax({
                url: url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: function(){
                    var data = new FormData();
                    data.append('current_date_id',current_date_id);
                    data.append('_token' , csrf);
                    return data;
                    console.log(data)
                }(),
                success: function(response) {

                    console.log(response);

                    if (response.status == 200) {
                        
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.message);

                        $('#confirmDeleteDateModal').modal('hide');

                        $('#addedDatesTable').DataTable().ajax.reload(null, false);

                    } else if (response.status == 404) {
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.message);
                    }

                }
            });
        });
    });
</script>
@endsection