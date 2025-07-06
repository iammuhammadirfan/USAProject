@extends('admin.layout')
@section('content')
@section('s_ticket_details_styles')

<style>

    .row-1-details
    {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        font-size: 15px;
    }

    .ticket-span
    {
        margin-right: 20%;
    }

    .row-2-details
    {
        padding: 10px;
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: center;
    }

    .ticket-user-details
    {
        width:50%;
    }

    .ticket-number
    {
        margin: auto;
        color: red;
        font-size: 50px;
    }

    .view-ticket-div
    {
        margin: 10px;
        display: flex;
        justify-content: space-around;
        align-items: center;
    }

    .view-ticket-div a{
        text-decoration: none;
        width: 10%;
    }

    .manage-ticket-div
    {
        padding: 20px;
        display: block;
        text-align: center;
    }

    .manage-ticket-div a
    {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 50%;
        margin: 10px auto;
        text-decoration:none;
        width:25%;
    }
	
	.view-id-details
	{
		text-align: center;
		margin: 5px 0;
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 10px;
	}
	
	.view-id-details span
	{
		text-transform: uppercase;
		font-weight: 500;
		color: red;
		font-size: 20px;
	}
	
	.view-id-details img
	{
		width: 25%;
	}


    @media(max-width:768px)
    {
        .view-ticket-div {
            display: block;
            text-align:center;
        }

        .view-ticket-div a{
            width: 40%;
        }

        .manage-ticket-div a
        {
            width: 50%;
        }
		
		.view-id-details img
		{
			width: 70%;
		}
    }
	
	@media(max-width:480px)
	{
		.manage-ticket-div a {
			width: 80%;
		}
	}
</style>
@stop

<div style="padding: 10px;">
    <div class="row-1-details">
        <span>Issued: {{ date("M d, Y", strtotime($ticketDetails->created_at)) }} at {{ date("h:i:s A", strtotime($ticketDetails->created_at)) }}</span>
        <span class="ticket-span">Ticket Number</span>
    </div>
    <div class="row-2-details">
        <div class="ticket-user-details">
            <table class="table table-bordered table-striped ">
                <tr>
                    <td><b>Name</b></td>
                    <td><span class="user-name">{{ $ticketDetails->firstName }} {{ $ticketDetails->lastName }}</span></td>
                </tr>
                <tr>
                    <td><b>Case Number</b> </td>
                    <td><span class="case-number">{{ $ticketDetails->caseNumber }}</span>
                    </td>
                </tr>
                @if($ticketDetails->projected_return_time !== null)
                <tr>
                    <td><b>Return Time</b> </td>
                    <td><span class="return-time">{{ $ticketDetails->projected_return_time }}</span>
                    </td>
                </tr>
                @endif
            </table>
        </div>
        <div class="ticket-number">
            <span>{{ $ticketDetails->ticket_number }}</span>
        </div>
        
    </div>
</div>
<!-- <div class="manage-ticket-div">
    <p>Check the current number being called before returning to your groceries.</p>
        <a href="/number-served" target="_blank" class="btn btn-warning btn-md border border-dark">Check Number Being Served</a>   
</div> -->
<div class="view-ticket-div mt-2">
    {{-- <a href="/view-ticket-size-pdf/{{ $ticketDetails->id }}/letter/" target="_blank" class="btn btn-warning btn-md border border-dark" style="text-decoration: none;">View Print Friendly Version (letter size)</a> --}}
    <a href="/view-ticket-size-pdf/{{ $ticketDetails->id }}/card/" target="_blank" class="btn btn-warning btn-md border border-dark">Print</a>
</div>
<!-- <div class="view-id-details">
    <span style="text-transform:uppercase;">You Must Have Your Id Card to pick up your groceries</span>
    <img src="/idcard.jpg" class="img-fluid"/>
    <p style="color:red; text-align:center;">Sample Card for Illustration</p>
</div> -->
<div class="manage-ticket-div mt-2">
    <p>Once cancelled, the ticket number will be invalid and you will have to get a new ticket.</p>
    <a ticket-id="1" class="btn btn-warning btn-md border border-dark cancel-my-ticket" style="text-decoration: none;width: 25%">Cancel My Ticket Number</a>
</div>

<?php
    $notificationmsgs=Session::get('notificationMessages');

    $objData = $notificationmsgs->firstWhere('modal_no', 1);
?>
<div class="modal fade" id="cancelTicketModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
            <p>{{ Str::limit(strip_tags($objData->message)) }}</p>
        </div>
        <div style="display: flex;
justify-content: space-around;
align-items: center;
margin: 5px;">
        <a class="btn btn-warning border border-dark waves-effect close-modal" data-dismiss="modal">Cancel</a>
        <a ticket-id="{{ $ticketDetails->id }}" class="btn btn-warning waves-effect border border-dark cancel-one-ticket-btn">Continue</a>
        </div>
        </div>
    </div>
</div>
@endsection

@section('s_ticket_details_script')
<script>
    $(document).on('click', '.close-modal', function(e) {
        e.preventDefault();

        $('#cancelTicketModal').modal('hide');
    });

    $(document).on('click', '.cancel-my-ticket', function(e) {
        e.preventDefault();

        $('#cancelTicketModal').modal('toggle');
    });

    $(document).on('click', '.cancel-one-ticket-btn', function()
    {
        var ticketId=$(this).attr('ticket-id');

        var csrf = document.querySelector('meta[name="csrf-token"]').content;

        var url = '/cancel-one-ticket-details';

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                ticketId: ticketId,
                _token: csrf
            },
            success: function(response) {

                console.log(response);

                if (response.status == 200)
                {
                    $('#cancelTicketModal').modal('hide');

                    var msg='Your ticket has been successfully cancelled.';

                    var redirecturl = '/admin/get-single-ticket';

                    Swal.fire(msg).then(() => {
                        window.location.href = redirecturl;
                    });
                } else if (response.status == 404)
                {
                    Swal.fire(response.message);
                }
                
            }
        });

    });
</script>
@endsection
