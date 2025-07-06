@extends('admin.layout')
@section('content')
@section('admin_tool_styles')
{{-- datatables --}}
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css"/>

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


    .dt-length label {
        margin-left: 10px;
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

<!-- <div class="card p-3 shadow"> -->
    <?php use Illuminate\Support\Facades\DB;
        $max_tickets=DB::table('tickets_management')->select('return_times_status','ticket_limit_status','ticket_limit','d_start_time','d_end_time')->first();
    ?>
    <div style="display: flex; gap: 5px;flex-direction: column;align-items:center;">
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
<!-- </div> -->

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

<div class="modal fade" id="confirmDeleteReturnTimeModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center confirm-delete-header" id="custom-width-modalLabel">Delete Details</h4>
            </div>
            <form method="POST" class="confirm-delete-return-id-form">
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

{{-- timepicker --}}
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>

{{-- jquery ui plugins --}}
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>

<!-- sweetalert -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

{{-- moment js --}}
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script> 

<script>

    let theEditor;

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

        // add new return time
        $(document).on('click', '.add-return-time', function()
        {
            $('#addEditReturnTimeModal').modal('toggle');

            $('.current-return-id,.start-ticket,.end-ticket,.return-time').val('');

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
                    } else if (response.status == 413) {
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

    });
</script>
@endsection