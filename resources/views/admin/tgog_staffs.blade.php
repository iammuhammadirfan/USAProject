@extends('admin.layout')
@section('content')
@section('admin_staffs_styles')
{{-- datatables --}}
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css"/>

<!-- sweetalert -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

{{-- alertify --}}
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>
<!-- Default theme -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/default.min.css"/>
<!-- Semantic UI theme -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/semantic.min.css"/>

<style>

    .dt-length label {
        margin-left: 10px;
    }
</style>
@stop

<div class="mx-auto">
    <h3 style="text-align: center;">Manage Staffs</h3>
    <div style="display: flex; justify-content:flex-end">
        <a class="btn btn-warning add-new-staff btn-sm">Add New</a>
    </div>
    <table id="staffsTable" class="table table-striped table-bordered" style="width:100%;">
        <thead>
        <tr>
                <td>First Name</td>
                <td>Last Name</td>
                <td>Action</td>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
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
                        <label>Memo Intended For?</label>
                        <div class="col-6">
                            <input type="radio" name="is_type" value="1" class="is-type" id="bothUserAndAdmin">
                            <label for="bothUserAndAdmin">Admins and Users</label>
                        </div>
                        <div class="col-6">
                            <input type="radio" name="is_type" value="0" class="is-type" id="adminOnly">
                            <label for="adminOnly">Admins Only</label>
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

    <div class="modal fade" id="viewPopupMessageModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg view-msg-modal-dialog">
           <div class="modal-content view-msg-modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center view-message-header" id="custom-width-modalLabel">Memo Details</h4>
             </div>
             
                <div class="modal-body">
                    <div class="row section-groups m-2">
                        <span id="intendedUser"></span>
                    </div>
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
@endsection

@section('admin_staffs_script')
{{-- datatable --}}
<script type="text/javascript" src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script> 
<script type="text/javascript" src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>

{{-- select2 --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- jquery ui plugins --}}
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>

<!-- sweetalert -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<script>

    document.addEventListener('DOMContentLoaded', function() {

        $(document).ready(function() {

            $('.admin-select2').select2();

            $('#staffsTable').DataTable({
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
                    "url": "/admin/staffs",
                    "type": "get",
                },
                columns: [
                    {  data: 'f_name',name:'f_name',orderable:true,searchable:true},
                    {  data: 'l_name',name:'l_name',orderable:true,searchable:true},
                    {  data: 'user_name',name:'user_name',orderable:true,searchable:true},
                    {  data: 'action',name:'action',orderable:false,searchable:false }
                ],
            });
            
        });

        // $(document).on('click','.reset-ticket-to-zero',function(e){

        //     e.preventDefault();
            
        //     $('#resetQueueTicketsModal').modal('toggle');
        // });

        // $(document).on('click','.confirm-reset-ticket-to-zero',function(e){

        //     e.preventDefault();

        //     var postData = {
        //         submission_type:3,
        //         _token: '{{ csrf_token() }}'
        //     };

        //     $.ajax({
        //         url: '/manage-tickets-generation',
        //         type: 'POST',
        //         data: postData,
        //         dataType: 'json',
        //         success: function(response) {

        //             console.log(response);

        //             if (response.status == 200)
        //             {
        //                 $('#resetQueueTicketsModal').modal('hide');
        //                 swal('Ticket in queue reset successfully.');
        //             }
        //         }
        //     });
        // })

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

        

        // manage notification msgs
        // $(document).on('click', '.edit-notificationmsg', function() 
        // {
        //     $('#editNotificationMessageModal').modal('toggle');
        //     var notificationMsgId=$(this).attr('notificationmsg-id');

        //     $('body').css("overflow", "hidden");

        //     $('.notification-msg-id').val(notificationMsgId);

        //     // tinymce.activeEditor.setContent(" ");

        //     $.ajax({
        //         url: '/admin/get-notification-msgs-details/' + notificationMsgId,
        //         method: 'GET',
        //         processData: false,
        //         contentType: false,
        //         success: function(response) {
        //             console.log(response)
        //             if (response.status == 404) {
        //                 swal(response.message);
        //             } else if (response.status == 200) {
        //                 $('.title').val(response.data.title);

        //                 const htmlParser= new DOMParser().parseFromString(response.data.message, 'text/html');
        //                 const textString= htmlParser.body.textContent;

        //                 $('.edit-notification-msg-form textarea').val(textString);
                        
        //                 // tinymce.activeEditor.setContent(response.data.message);
        //             }


        //         }
        //     })
        // });

        // $(document).on('submit', '.edit-notification-msg-form', function(e) {
        //     e.preventDefault();

        //     var url = '/admin/update-notification-msgs';
        //     var form = $('.edit-notification-msg-form')[0];
        //     var formdata = new FormData(form);

        //     $.ajax({
        //         url: url,
        //         method: 'POST',
        //         processData: false,
        //         contentType: false,
        //         data: formdata,
        //         success: function(response) {

        //             console.log(response);

        //             if (response.status == 200) {

        //                 $('.error-list').html(" ").addClass('d-none');
                        
        //                 alertify.set('notifier', 'position', 'top-center');
        //                 alertify.success(response.message);

        //                 $('#editNotificationMessageModal').modal('hide');

        //                 $('#notificationMsgsTable').DataTable().ajax.reload(null, false);

        //             } else if (response.status == 400) {
        //                 $('.error-list').html(" ").addClass('d-none');

        //                 alertify.set('notifier', 'position', 'top-center');
        //                 alertify.success(response.message);
        //             } else if (response.status == 405) {
        //                 $('.error-list').html(" ").removeClass('d-none');
        //                 $.each(response.message, function(key, err_value) {
        //                     $('.error-list').append('<li>' + err_value + '</li>');
        //                 })
        //             }

        //         }
        //     });
        // });

        // // view notification msg
        // $(document).on('click', '.view-notificationmsg', function() 
        // {
        //     $('#viewNotificationMessageModal').modal('toggle');
        //     var notificationMsgId=$(this).attr('notificationmsg-id');
        //     $('.view-msg-div').html('');

        //     $('body').css("overflow", "hidden");

        //     $.ajax({
        //         url: '/admin/get-notification-msgs-details/' + notificationMsgId,
        //         method: 'GET',
        //         processData: false,
        //         contentType: false,
        //         success: function(response) {
        //             console.log(response)
        //             if (response.status == 404) {
        //                 swal(response.message);
        //             } else if (response.status == 200) {
        //                 const htmlParser= new DOMParser().parseFromString(response.data.message, 'text/html');
        //                 const textString= htmlParser.body.textContent;
        //                 $('.view-notification-msg-div').html(textString)
        //             }


        //         }
        //     })
        // });

        
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

            $('.is-type').prop('checked',false);

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

            $('.is-type').prop('checked',false);

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

                        if(response.data.is_type == '1')
                        {
                            $('#bothUserAndAdmin').prop('checked',true);
                        } else {
                            $('#adminOnly').prop('checked',true);
                        }
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

                        if(response.data.is_type == '1')
                        {
                            $('#intendedUser').text('Memo Intended For Admins and Users')
                        } else {
                            $('#intendedUser').text('Memo Intended For Admins')
                        }
                    }


                }
            })
        });
    });
</script>
@endsection