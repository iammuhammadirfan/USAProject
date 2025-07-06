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

<?php
    $max_tickets=DB::table('tickets_management')->select('ticket_limit_status','ticket_limit')->first();
?>

<!-- <div class="card p-3 shadow"> -->
     <h3 style="text-align: center;">Manage Ticket Limit</h3>
    <div style="display: flex;flex-direction: column;gap: 5px;align-items:center;">
        @if($max_tickets->ticket_limit_status == 1)
            <?php
                $maxtickets=$max_tickets->ticket_limit;
            ?>
        @else
            <?php
                $maxtickets='';
            ?> 
        @endif
        <label>Current Ticket Limit</label>
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
<!-- </div> -->
@endsection

@section('admin_tool_script')


{{-- jquery ui plugins --}}
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>

<!-- sweetalert -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        document.getElementById('backToBottom').style.display = "none";

        var maxTicketsVal=$('#maxTickets').val();

        if(maxTicketsVal !== '')
        {
            $('#yesEnableTicketLimit').prop("checked",true);
            $('#noEnableTicketLimit').prop("checked",false);
        } else {
            $('#yesEnableTicketLimit').prop("checked",false);
            $('#noEnableTicketLimit').prop("checked",true);
        }

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
    });
</script>
@endsection