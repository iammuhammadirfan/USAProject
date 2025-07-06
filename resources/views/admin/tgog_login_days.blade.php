@extends('admin.layout')
@section('content')
@section('admin_tool_styles')
{{-- datatables --}}
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css"/>

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

    
</style>
@stop
<?php 
    //  get active login days
    $activeLoginDays=DB::table('login_days_time')->where('login_days_time.is_active',1)->pluck('login_days_time.day')->toArray();

    $activeDates=DB::table('login_days_time')->where('login_days_time.status',1)->pluck('login_days_time.date')->toArray();
?>
<div class="card p-3 shadow">
    <h3 style="text-align: center;">Manage Login Days/Dates</h3>
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
<!-- <script src="https://cdn.tiny.cloud/1/4sxjlpvl7c4jfn3yhm5pjar918m5gb48cio5o9felm9oqhui/tinymce/7/tinymce.min.js"></script> -->

{{-- moment js --}}
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script> 

<script>

    document.addEventListener('DOMContentLoaded', function() {

        document.getElementById('backToBottom').style.display = "none";

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