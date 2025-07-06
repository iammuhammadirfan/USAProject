@extends('admin.layout')
@section('content')
@section('multiple_ticket_details_styles')
<style>
    
    /* *{
        font-size: 18px;
        color:black;
        /* font-weight: 500; 
    } */
    .custom-div {
        background-color: black;
        color: red;
    }
    
    .serving-number {
       color:red; font-size: 32em;
    }

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
    }

    .manage-ticket-div p
    {
        text-align:center;
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
        <span>Issued:  {{ date("M d,  Y", strtotime($generatedTicketDetails->created_at)) }} at {{ date("h:i:s A", strtotime($generatedTicketDetails->created_at)) }}</span>
        @if($generatedTicketDetails->projected_return_time !== null)
        <span>Return Time: {{ $generatedTicketDetails->projected_return_time }}</span>
        @endif
    </div>
    <div class="row-2-details">
        <table class="table table-bordered table-striped ">
                <tr>
                    <th><b>Name</b></th>
                    <th><b>Case Number</b></th>
                    <th><b>Ticket</b></th>
                </tr>
                @foreach($ticketDetails as $ticket)
                    <tr>
                        <td><span>{{ $ticket->firstName }} {{ $ticket->lastName }}</span></td>
                        <td><span>{{ $ticket->caseNumber }}</span></td>
                        <td><span class="text-danger">{{ $ticket->ticket_number }}</span></td>
                    </tr>
                @endforeach
            </table>
    </div>
</div>
<!-- <div class="manage-ticket-div mt-2">
    <p>Check the current number being called before returning to your groceries.</p>
    <a href="/number-served" target="_blank" class="btn btn-warning btn-md border border-dark" >Check Number Being Served</a>
</div> -->
<div class="view-ticket-div">
    <a href="/view-multiple-users-ticket-size-pdf/{{ $generatedTicketDetails->multiple_id }}/card/" target="_blank" class="btn btn-warning btn-md border border-dark">Print</a>
</div>
<!-- <div class="view-id-details">
    <span>You Must Have Your Id Card to pick up your groceries</span>
    <img src="/idcard.jpg" class="img-fluid"/>
    <p style="color:red; text-align:center;">Sample Card for Illustration</p>
</div> -->
<div class="manage-ticket-div mt-2">
    <p>Once cancelled, the ticket number will be invalid and you will have to get new tickets. </p>
    <a ticket-id="1" class="btn btn-warning btn-md border border-dark cancel-my-ticket">Cancel My Ticket Numbers</a>
</div>

<div class="modal fade" id="cancelMultipleTicketsModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
            <p>Your ticket numbers will be cancelled and will no longer be
                valid. Click continue to proceed. Click cancel to keep your
                ticket number.</p>
        </div>
        <div style="display: flex;justify-content: space-around;align-items: center;margin: 5px;">
        <a class="btn btn-warning waves-effect border border-dark close-modal" data-dismiss="modal">Cancel</a>
        <a ticket-id="{{ $generatedTicketDetails->multiple_id }}" class="btn btn-warning waves-effect border border-dark cancel-multiple-ticket-btn">Continue</a>
        </div>
        </div>
    </div>
</div>
@endsection

@section('multiple_ticket_details_script')

        <script>
            $(document).on('click', '.close-modal', function(e) {
                e.preventDefault();

                $('#cancelMultipleTicketsModal').modal('hide');
            });

            $(document).on('click', '.cancel-my-ticket', function(e) {
                e.preventDefault();

                $('#cancelMultipleTicketsModal').modal('toggle');
            });

            $(document).on('click', '.cancel-multiple-ticket-btn', function()
            {
                var ticketId=$(this).attr('ticket-id');

                var csrf = document.querySelector('meta[name="csrf-token"]').content;

                var url = '/cancel-multiple-tickets-details';

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
                            $('#cancelMultipleTicketsModal').modal('hide');

                            var msg='Your ticket has been successfully cancelled.';

                            var redirecturl = '/admin/dashboard';

                            Swal.fire(msg).then(() => {
                                window.location.href = redirecturl;
                            });

                        } else if (response.status == 404)
                        {
                            Swal.fire(response.message)
                        }
                        
                    }
                });

            });
        </script>
@endsection
