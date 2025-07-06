@extends('user.layout')
@section('content')
@section('ticket_options_styles')

<style>
    .progress-bar-animated {
        transition: width 0.3s ease;
    }

    .progress-container {
        display: none;
        width: 100%;
        background-color: #ffffff;
        padding: 20px;  
    }
    
    .progress-bar {
        width: 0%;
        height: 40px;
        background-color: #ffc107;
        text-align: center;
        line-height: 40px;
        font-size: 40px;
        border-radius: 5px;
    }
	
	.sweet-alert h2 {
		color: #bf0909;
		font-size: 30px;
		text-align: center;
		font-weight: 600;
		text-transform: none;
		position: relative;
		margin: 25px 0;
		padding: 0;
		line-height: 40px;
		display: block;
	}

	
	.option-multiple-tickets img,.option-single-ticket img
	{
		width: 250px;
		height: 120px;
		object-fit: contain;
	}
	
	.tickets-options-holder-div
	{
		display: flex;
		gap: 20px;
		align-items: center;
		margin:5% 20%;
	}

	.option-multiple-tickets,.option-single-ticket
	{
		display: flex;
		gap: 10px;
		align-items: center;
		flex-direction: column;
		width: 100%;
	}

	.option-single-ticket .link-holder,.option-multiple-tickets .link-holder
	{
		display: flex;
		align-items: center;
		gap: 10px;
		flex-direction: column;
		padding: 2px 10px;
		margin-top: -5px;
		background: red;
		color: white;
		border-radius: 10px;
		text-decoration: none;
		margin-top: 5px;
	}

	 
	.option-single-ticket .link-holder > span:first-child,.option-multiple-tickets .link-holder > span:first-child{
		font-size: 22px;
		font-weight: 500;
		padding: 0 20px;
		text-align: center;
	}

	.option-single-ticket .link-holder > span:last-child,.option-multiple-tickets .link-holder > span:last-child{
		font-size: 70px;
		font-weight: 500;
		margin-top: -10px;
	}
	
	body.modal-open {
		overflow: hidden;
		position: fixed;
		width: 100%;
	}
	
	.option-single-ticket .link-holder{
			width:70%;
		}

    /* #ticketsInQueueModal
    {
        background-color:#ffc107;
    } */

    .main-text-holder
    {
        font-size: 50px;font-weight: 500;text-align: center;
    }

	@media(max-width:992px)
    {
		.option-single-ticket .link-holder{
			width:80%;
		}
    }
	
	@media(max-width:768px)
    {
        .tickets-options-holder-div {
			gap: 10px;
			margin: 5% 15%;
		}

        .main-text-holder
        {
            font-size: 30px;
        }
    }

    @media(max-width:480px)
    {
        .tickets-options-holder-div {
			flex-direction: column;
			gap: 5px;
		}
		
		.option-single-ticket .link-holder, .option-multiple-tickets .link-holder {
			padding: 0;
		}
		
		.option-single-ticket .link-holder{
			padding: 0;
			width:70%;
		}
    }
</style>
@stop

<h3 style="text-align: center;margin: 12px 0;">How many tickets do you need?</h3>
<div class="tickets-options-holder-div">
    <div class="option-multiple-tickets">
        <img class="multiple-tickets-btn" src="/images/multiple tickets.png"/>
        <a href="#" class="link-holder multiple-tickets-btn">
            <span>Multiple Tickets</span>
            <span>2+</span>
        </a>
    </div>
    <span style="font-size: 50px;font-weight: 500;">OR</span>
        <div class="option-single-ticket">
        <img class="single-ticket-btn" style="width: 180px; margin-bottom:-7px;" src="/images/one ticket.png"/>
        <a href="#" class="link-holder single-ticket-btn" style="background-color: #0549d9;">
            <span>One Ticket</span>
            <span>1</span>
        </a>
        </div>
</div>
@if (session('message'))
    <div class="alert alert-success">
        {{ session('message') }}
    </div>
@endif

<?php
        $notificationmsgs=Session::get('notificationMessages');

        $savingPopUpData=$notificationmsgs->firstWhere('modal_no', 8);

        $oneTicketModalData = $notificationmsgs->firstWhere('modal_no', 9);

        $multipleTicketsData = $notificationmsgs->firstWhere('modal_no', 10);
    ?> 

    <div class="modal fade" id="oneTicketModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
           <div class="modal-content">
              <div class="modal-body">
                <p>{{ Str::limit(strip_tags($oneTicketModalData->message)) }}</p>
           </div> 
           <div style="display: flex;justify-content: space-around;align-items: center;margin: 5px;">
            <a class="btn btn-warning waves-effect border border-dark close-modal">Cancel</a>
            <a user-id="{{ Auth::user()->id }}" class="btn btn-warning waves-effect border border-dark one-ticket-modal-continue-btn">Continue</a>
         </div>
         <div class="progress-container" id="progressContainer">
            <div id="progressBar" class="progress-bar">0%</div>
        </div>
        <span style="color: red;font-size: 18px;font-weight: 500;font-style: italic;text-align: center;" class="redirect-text d-none">Please Wait As you Are being redirected to the Ticket Details</span>
           </div>
        </div>
    </div>

    <div class="modal fade" id="multipleTicketModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
           <div class="modal-content">
              <div class="modal-body">
                <p>{{ Str::limit(strip_tags($multipleTicketsData->message)) }}</p>
           </div>
           <div style="display: flex;
    justify-content: space-around;
    align-items: center;
    margin: 5px;">
            <a class="btn btn-warning waves-effect border border-dark close-modal" data-dismiss="modal">Cancel</a>
            <a href="/select-multiple-ticket-details/{{ Auth::user()->id }}" class="btn btn-warning waves-effect border border-dark  multiple-ticket-modal-btn">Continue</a>
         </div>
           </div>
        </div>
    </div>
@endsection

@section('ticket_options_script')
    {{-- alertify --}}
   <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>
   
    {{-- jquery ui plugins --}}
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script> 
	
	{{--sweetalert--}}
	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

   <script>
   
        $(document).ready(function()
        {
            var isTicketable = {{ $isApplicable }};

            if (isTicketable == 1)
            {
                $('.single-ticket-btn,.multiple-tickets-btn').prop("disabled", true).css("cursor", "not-allowed");
            }
			
			const timeout = 480000;  // 900000 ms = 5 minutes
			var idleTimer = null;
			$('*').bind('mousemove click mouseup mousedown keydown keypress keyup submit change mouseenter scroll resize dblclick', function () {
				clearTimeout(idleTimer);

				idleTimer = setTimeout(function () 
				{
					window.location.reload(true);
				}, timeout);
			});
			//$("body").trigger("mousemove");
        });

        $(document).on('click', '.single-ticket-btn', function()
        {
            var userId=$(this).attr('user-id');

            $('#oneTicketModal').modal('toggle');

            $("#oneTicketModal #oneTicketUserId").attr("user-id", "value");
			
			return;

        });
        $(document).on('click', '.one-ticket-modal-continue-btn', function(e)
        {
            e.preventDefault();

            $('.one-ticket-modal-continue-btn').prop("disabled", true).css("cursor", "not-allowed");
            
            // Show progress
            $('#progressContainer').show();
            updateProgress(0);
            
            // Get form values
            const formData = {
                submission_type:0,
                userId: $(this).attr('user-id'),
                _token: document.querySelector('meta[name="csrf-token"]').content
            };

            // Create simulated progress (0-80%) while waiting for response
            const progressInterval = setInterval(() => {
                const currentProgress = parseInt($('#progressBar').css('width'));
                if (currentProgress < 80) {
                    updateProgress(currentProgress + 1);
                }
            }, 50);
            
            // AJAX request with object data
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route("manage_tickets_generation") }}', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 80); // Only go to 80% for upload
                    updateProgress(percent);
                }
            });

            xhr.onload = function() {
                clearInterval(progressInterval); // Stop simulated progress
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    console.log(response);

                    // Complete the progress animation (80-100%)
                    let finalProgress = 80;
                    const completionInterval = setInterval(() => {
                        if (finalProgress >= 100) {
                            clearInterval(completionInterval);
                            handleResponse(response);
                        } else {
                            finalProgress += 2;
                            updateProgress(finalProgress);
                        }
                    }, 20);
                    
                } catch (e) {
                    handleError('Error parsing server response');
                }
            };

            xhr.onerror = function() {
                clearInterval(progressInterval);
                handleError('Network error occurred');
            };

            // Helper functions
            function handleResponse(response) {
                $('.one-ticket-modal-continue-btn').prop("disabled", false).css("cursor", "pointer");

                switch(response.status) {
                    case 429:
                    case 500:
                    case 530:
                    case 550:
                        Swal.fire(response.message);
                        $('#progressContainer').hide();
                        break;
                    case 200:
                        // $('.redirect-text').addClass('d-block').removeClass('d-none');
                        setTimeout(() => {
                            var redirecturl = '/one-ticket-details/'+response.ticket_id;
                            window.location.href = redirecturl;
                        }, 800); // Short delay to show completion
                        break;
                    default:
                        handleError('Unexpected response from server');
                }
            }

            function handleError(message) {
                $('#progressContainer').hide();
                Swal.fire(message);
                $('.get-multiple-ticket-numbers, .continue-to-get-tickets-btn')
                    .prop("disabled", false)
                    .css("cursor", "pointer");
            }


            
            // xhr.onload = function() {
            //     try {
            //         const response = JSON.parse(xhr.responseText);

            //         console.log(response);

                    
            //         if (response.status === 429) {

            //             $('#continuetoGetTicketsModal').hide();

            //             alertify.set('notifier', 'position', 'top-center');
            //             alertify.success(response.message);

            //         } else if (response.status === 200) 
            //         {
            //             $('#progressContainer').hide();

            //             // $('.redirect-text').removeClass('d-none').addClass('d-block');

            //             var redirecturl = '/one-ticket-details/'+response.ticket_id;
            //             window.location.href = redirecturl;
            //         } else if (response.status === 500){
            //             $('#continuetoGetTicketsModal').hide();

            //             alertify.set('notifier', 'position', 'top-center');
            //             alertify.success(response.message);
            //         }
            //     } catch (e) {
            //         $('#continuetoGetTicketsModal').hide();

            //         alertify.set('notifier', 'position', 'top-center');
            //         alertify.success('Error parsing server response');
            //     }
            // };
            
            // xhr.onerror = function() {
            //     $('#progressContainer').hide();
            //     alertify.set('notifier', 'position', 'top-center');
            //     alertify.success('Network error occurred');
            //     // $('#responseMessage').text('Network error occurred').addClass('error');
            // };
            
            // Send as JSON string
            xhr.send(JSON.stringify(formData));
        });

        function updateProgress(percent) {
            const $progressBar = $('#progressBar');
            $progressBar.css('width', percent + '%').text(percent + '%');
            $progressBar.addClass('progress-bar-animated');
            
            // Add/remove animation class
            if (percent < 100) {
                $progressBar.addClass('progress-bar-animated');
            } else {
                $progressBar.removeClass('progress-bar-animated').text('Complete!');
            }
        }
        
        $(document).on('click', '.multiple-tickets-btn', function()
        {
            $('#multipleTicketModal').modal('toggle');
        });

        $(document).on('click', '.close-modal', function()
        {
            $('#multipleTicketModal').modal('hide');

            $('#oneTicketModal').modal('hide');
        });
   </script>
@endsection
