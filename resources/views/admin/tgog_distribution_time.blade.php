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

<!-- <div class="card p-3 shadow"> -->
    <h2 class="text-center p-3">Manage Distribution Time</h2>
    <div style="display: flex;flex-direction: column;gap: 5px;align-items: center;">
        <div style="display: flex;gap: 20px;align-items: center;">
            <?php use Illuminate\Support\Facades\DB;
                $max_tickets=DB::table('tickets_management')->select('return_times_status','ticket_limit_status','ticket_limit','d_start_time','d_end_time')->first();
            ?>
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
<!-- </div> -->
@endsection

@section('admin_tool_script')

{{-- timepicker --}}
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>


<!-- sweetalert -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

{{-- moment js --}}
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script> 

<script>

    let theEditor;

    document.addEventListener('DOMContentLoaded', function() {

        document.getElementById('backToBottom').style.display = "none";
        
        $(document).ready(function() {

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


            // datepicker for selecting the date of birth
            $( function() {
                $( "#addedDate" ).datepicker(
                    {
                        dateFormat: 'mm/dd/yy',
                        minDate: 0
                    }
                );
            });
            
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