@extends('admin.layout')
@section('content')
@section('admin_tool_styles')
{{-- datatables --}}
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css"/>

<!-- {{-- select2 --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

{{-- timepicker --}}
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">

{{-- date picker css--}}
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css"> -->

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

<div class="card p-3 shadow">
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

<!-- {{-- manage notification msgs --}} -->
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
@endsection

@section('admin_tool_script')
{{-- datatable --}}
<script type="text/javascript" src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script> 
<script type="text/javascript" src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>

{{-- alertify --}}
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>


<!-- sweetalert -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

{{-- moment js --}}
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script> 

<script>

    let theEditor;

    document.addEventListener('DOMContentLoaded', function() {

        document.getElementById('backToBottom').style.display = "none";


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
    });
</script>
@endsection