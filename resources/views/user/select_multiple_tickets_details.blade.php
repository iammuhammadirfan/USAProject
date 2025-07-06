@extends('user.layout')
@section('content')
@section('select_multiple_tickets_details_styles')
{{-- select2 --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

{{-- alertify --}}
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>
<!-- Default theme -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/default.min.css"/>
<!-- Semantic UI theme -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/semantic.min.css"/>

<style>

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

.select2-container--default .select2-selection--single .select2-selection__rendered li {
    padding-right: 10px;
    list-style-type: none;
}

.selected-ticket-holder,.main-case-ticket-holder
{
    display: flex;
    gap:10px;
    align-items:center;
    padding:5px;
}

.selected-case-number{
            width:20%;
        }

.error-span
{
    width: 50%; 
    padding: 10px;
    border-radius: 5px;
}

.add-extra-tickets
{
    width: 100%;
    display: flex;
    gap: 30px;
    align-items: center;
    flex-direction:column;
}

.add-extra-tickets h4{
    font-size: 30px;
    text-align: center;
    margin-bottom: 10px;
    margin-left: -5%;
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

#ticketsInQueueModal
{
    background-color:#ffc107;
}

.main-text-holder
{
    font-size: 50px;font-weight: 500;text-align: center;
}


.progress-bar-animated {
    transition: width 0.3s ease;
}

#progressContainer {
    margin-top: 15px;
    display: none;
}

#progressBar {
    min-width: 2em; /* Ensure percentage text is always visible */
}


@media(max-width:768px)
{
    .main-text-holder
    {
        font-size: 30px;
    }

    .selected-ticket-holder
    {
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .selected-case-number{
            width:70%;
        }

    .error-span
    {
        width: 100%;
    }
}

@media(max-width:480px)
{
    .add-extra-tickets
    {
        gap:20px;
    }
    
    .add-extra-tickets h4 {
        font-size: 22px;
        margin-left: 0;
    }
}
</style>
@stop

<div style="font-size: 16px; color:black; font-weight:500;">
<h2 class="text-center m-2">Multiple Tickets</h2>

<p style="text-align: center;margin-top: 40px;">You must already be an approved proxy on file to pick up groceries for someone else.</p>

<div class="row mt-2 ">
    <div class="col-md-6">
        <input type="hidden" class="number-of-tickets">
    </div>
</div>
@if (session('message'))
    <div class="alert alert-danger">
        {!! session('message') !!}
    </div>
@endif

<div class="row mt-2">
    <div class="col-md-12">
        <div class="add-extra-tickets">
            <h4>How many total tickets do you need?</h4>
            <select style="width:50%;" class="form-control admin-select2 text-dark number-of-tickets-id" required>
                <option selected disabled value="">Total Number of Tickets</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
            </select>
        </div>
        @if($userDetails->role == 1)
            <?php
                $disabledAttr='';
                $inputValue='';
                $readonlyAttr='';
                $customStyle='pointer';
                $nameText='';
                $nameId='';
                $bgColor='';
            ?>
        @else
            <?php
                $disabledAttr='disabled';
                $inputValue=''.$userDetails->case_number.'';
                $readonlyAttr='readonly';
                $customStyle='not-allowed';
                $nameText=''.$userDetails->first_name.' '.$userDetails->last_name.'';
                $nameId=''.$userDetails->id.'';
                $bgColor='bg-success';
            ?> 
        @endif
        <div class="main-case-ticket-holder ticket-holder11 d-none">
            <input type="text" class="form-control selected-case-number case-number11" readonly value="{{ $userDetails->case_number }}" placeholder="Case Number">
            <span class="user-name11 error-span bg-success">{{ $userDetails->first_name }} {{ $userDetails->last_name }}</span>
            <input type="hidden" class="ticket-holder extra-ticket ticket-holder-id11 selected-ticket-id" user-id="{{ $userDetails->id }}">
        </div>
        <div id="ticketsHolderDiv"></div>
        <div style="display: flex;justify-content: space-around;align-items: center;margin-top: 20px;">
            <a class="get-multiple-ticket-numbers btn btn-warning btn-lg px-5 border border-dark py-1 mb-2 " style="border: 1px solid #343a40; text-decoration: none; margin-top:10px;">Get Ticket Numbers</a>
        </div>
    </div>
</div>
</div>

<?php
    $notificationmsgs=Session::get('notificationMessages');

    $continueApprovalOnExpireData = $notificationmsgs->firstWhere('modal_no', 2);

    $selectNumberOfTicketsData = $notificationmsgs->firstWhere('modal_no', 3);

    $emptyInputsErrorData=$notificationmsgs->firstWhere('modal_no', 4);

    $checkCasenumberExistenceData=$notificationmsgs->firstWhere('modal_no', 5);

    $inputsErrorData=$notificationmsgs->firstWhere('modal_no', 6);

    $continuetoGetTicketsData=$notificationmsgs->firstWhere('modal_no', 7);

    $savingPopUpData=$notificationmsgs->firstWhere('modal_no', 8);
?>
<div class="modal fade" id="continueApprovalOnExpireModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
            <p>{{ Str::limit(strip_tags($continueApprovalOnExpireData->message)) }}</p>
        </div>
        <div style="display: flex;justify-content: space-around;align-items: center;margin: 5px;">
        <a class="btn btn-warning waves-effect border border-dark continue-approval-on-expire-btn">Continue</a>
        
        </div>
        </div>
    </div>
</div>

<div class="modal fade" id="selectNumberofTicketsModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
            <p>{{ Str::limit(strip_tags($selectNumberOfTicketsData->message)) }}</p>
        </div>
        <div style="display: flex;justify-content: space-around;align-items: center;margin: 5px;">
        <a class="btn btn-warning waves-effect border border-dark close-modal">close</a>
        
        </div>
        </div>
    </div>
</div>

<div class="modal fade" id="emptyInputsErrorModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
            <p>{{ Str::limit(strip_tags($emptyInputsErrorData->message)) }}</p>
        </div>
        <div style="display: flex;justify-content: space-around;align-items: center;margin: 5px;">
        <a class="btn btn-warning waves-effect border border-dark close-modal">close</a>
        
        </div>
        </div>
    </div>
</div>

<div class="modal fade" id="checkCaseNumberExistenceModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
            <p>{{ Str::limit(strip_tags($checkCasenumberExistenceData->message)) }}</p>
        </div>
        <div style="display: flex;justify-content: space-around;align-items: center;margin: 5px;">
        <a class="btn btn-warning waves-effect border border-dark close-modal">close</a>
        
        </div>
        </div>
    </div>
</div>

<div class="modal fade" id="inputsErrorModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
            <p>{{ Str::limit(strip_tags($inputsErrorData->message)) }}</p>
        </div>
        <div style="display: flex;justify-content: space-around;align-items: center;margin: 5px;">
        <a class="btn btn-warning waves-effect border border-dark close-modal">close</a>
        
        </div>
        </div>
    </div>
</div>

<div class="modal fade" id="continuetoGetTicketsModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
                <div class="modal-body">
                    <p>You will receive <span class="tickets-count-number"></span> tickets.</p>
                    <p>{{ Str::limit(strip_tags($continuetoGetTicketsData->message)) }}.</p>
                </div>
                <input type="hidden" name="submission_type" class="submission-type" value="1"/>
                <div id="getTicketsForm" class="d-none">
                    
                </div>
                <div style="display: flex;justify-content: space-around;align-items: center;margin: 5px;">
                    <a class="btn btn-warning waves-effect border border-dark close-modal">Cancel</a>
                    <button class="btn btn-warning waves-effect border border-dark continue-to-get-tickets-btn" type="submit">Continue</button>
                </div>
                <div class="progress-container" id="progressContainer">
                    <div id="progressBar" class="progress-bar"></div>
                </div>
                <span style="color: red;font-size: 18px;font-weight: 500;font-style: italic;text-align: center;" class="redirect-text d-none">Ticket/s Is being Processed.Please Wait As you Are being redirected to the Ticket Details</span>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="mutlipleTicketsPopUpModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="preloader-wrapper">
            <div class="preloader">
                <p style="
                font-size: 25px;
                font-weight: 500;
            ">{{ Str::limit(strip_tags($savingPopUpData->message)) }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('select_multiple_tickets_details_script')
    {{-- select2 --}}
   <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

   {{-- alertify --}}
   <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>
   
    {{-- jquery ui plugins --}}
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script> 
	

   <script>

        let interval;

        $(document).ready(function() {
            $('.admin-select2').select2();
			
			const timeout = 480000;
			var idleTimer = null;
			
			$('*').bind('mousemove click mouseup mousedown keydown keypress keyup submit change mouseenter scroll resize dblclick', function () {
				clearTimeout(idleTimer);

				idleTimer = setTimeout(function () {
					window.location.reload(true);
				}, timeout);
			});
			
			var isTicketable = {{ $isApplicable }};

            if (isTicketable == 1)
            {
                $('.get-multiple-ticket-numbers').prop("disabled", true).css("cursor", "not-allowed");
            }
        });
		
		$(document).on('click', '.verify-admin-case-number', function()
        {
            var ticketNo=$(this).attr('ticket-number');
            var caseNumber=$('.case-number'+ticketNo+'').val();

            var userIdsObject = JSON.stringify($('.ticket-holder').map(function() {
                return $(this).attr('user-id');
            }).get());

            if(caseNumber == '')
            {
                $('.user-name'+ticketNo).removeClass('d-none').addClass('bg-danger text-white').text('').text('Enter Case Number In The Input').attr('is-error',1);

                return;
            }

            var url = '/admin/verify-admin-casenumber';

            $.ajax({
                url: url,
                method: 'GET',
                data: {
                    caseNumber: caseNumber,
                    userIdsObject: userIdsObject
                },
                success: function(response) {
                    console.log(response);

                    if(response.status == 415)
                    {
                        // 
                        $('.user-name'+ticketNo).removeClass('d-none').addClass('bg-danger text-white').text('').text(response.message);
                        $('.case-number'+ticketNo).attr('is-error',1);

                        $('.remove-ticket'+ticketNo).removeClass('d-none');
                        $('.ticket-holder-id'+ticketNo).removeClass('selected-ticket-id');
                        $('.ticket-holder-id'+ticketNo).removeAttr('user-id','');
                    }  else if(response.status == 430)
                    {
                        // 
                        $('.user-name'+ticketNo).removeClass('d-none').addClass('bg-danger text-white').text('').text(response.message);
                        $('.remove-ticket'+ticketNo).addClass('d-none');
                        $('.ticket-holder-id'+ticketNo).removeClass('selected-ticket-id');

                        $('.case-number'+ticketNo).attr('is-error',1);

                    }  else if (response.status == 201)
                    {
                        $('.user-name'+ticketNo).removeClass('d-none').removeClass('bg-danger text-white').addClass('bg-success').text('');
                        $('.remove-ticket'+ticketNo).addClass('d-none');

                        var fname = response.data.first_name;
                        var lname = response.data.last_name;
                        var userId = response.data.id;

                        $('.user-name'+ticketNo).text(fname+"   "+lname);
                        $('.ticket-holder-id'+ticketNo).attr('user-id',userId).addClass('selected-ticket-id');

                        $('.case-number'+ticketNo).removeAttr('is-error',1);

                    }

                }
            });
        });

        $(document).on('click', '.continue-approval-on-expire-btn', function()
        {
            $('#continueApprovalOnExpireModal').modal('hide');

            window.location.reload();
        });

        $(document).on('change', '.number-of-tickets-id', function(e) {
            e.preventDefault();

            var numberOfSelectedTickets = Number($(this).val());
			
			const currentNumberOfTickets = Number($('.selected-ticket-holder').length) + 1;
			
			//const divsToGenerate = numberOfSelectedTickets-currentNumberOfTickets-1;
			
			$('.main-case-ticket-holder').removeClass('d-none');
			
			$(".number-of-tickets-id option:selected").prop("selected", false);
			$(".number-of-tickets-id option:first").prop("selected", "selected");
			
            
            //$("#ticketsHolderDiv").empty();
			
			console.log(currentNumberOfTickets,numberOfSelectedTickets);

            $('.add-extra-tickets .select2 .selection .select2-selection .select2-selection__rendered').html('');

            $('.add-extra-tickets .select2 .selection .select2-selection .select2-selection__rendered').append(
                '<li class="select2-selection__choice" title="Total Number of Tickets" data-select2-id="21"><span class="select2-selection__choice__remove" role="presentation"></span>Total Number of Tickets</li>'
            );
			
			if(currentNumberOfTickets == 1 && numberOfSelectedTickets > currentNumberOfTickets)
			{
				const newDivsToGenerate=numberOfSelectedTickets-1;
				
				var userID={{ $userDetails->id }}
				
				for (var i=0; i<newDivsToGenerate; i++)
				{
					$('#ticketsHolderDiv').append('<div class="selected-ticket-holder ticket-holder'+[i]+'" ><input type="text" class="form-control selected-case-number case-number'+[i]+'" placeholder="Case Number"><button type="button" ticket-number="'+[i]+'" class="btn btn-warning check-case-number">Check</button><button type="button" ticket-number="'+[i]+'" class="btn btn-danger d-none remove-ticket'+[i]+' remove-ticket-btn">Remove</button><span class="user-name'+[i]+' d-none error-span"></span><input type="hidden" class="ticket-holder extra-ticket ticket-holder-id'+[i]+'"/></div>');
				}
				
				$('#getTicketsForm').append('<div class="main-case-ticket-holder ticket-holder11"><input type="text" name="user_id[]" value="'+userID+'" class="selected-ticket-ids ticket-holder ticket-holder-id11 selected-ticket-id"></div>');
				
			} else if (currentNumberOfTickets > 1 && numberOfSelectedTickets > currentNumberOfTickets)
			{
				const lastDivTicketNumber=Number($('#ticketsHolderDiv div button:last').attr('ticket-number'));
				
				const divsToGenerate=numberOfSelectedTickets-currentNumberOfTickets;
				
				for (var i=0; i<divsToGenerate; i++)
				{
					const nextDivNumber=lastDivTicketNumber + 1 + i;
					
					$('#ticketsHolderDiv').append('<div class="selected-ticket-holder ticket-holder'+[nextDivNumber]+'" ><input type="text" class="form-control selected-case-number case-number'+[nextDivNumber]+'" placeholder="Case Number"><button type="button" ticket-number="'+[nextDivNumber]+'" class="btn btn-warning check-case-number">Check</button><button type="button" ticket-number="'+[nextDivNumber]+'" class="btn btn-danger d-none remove-ticket'+[nextDivNumber]+' remove-ticket-btn">Remove</button><span class="user-name'+[nextDivNumber]+' d-none error-span"></span><input type="hidden" class="ticket-holder extra-ticket ticket-holder-id'+[nextDivNumber]+'"/></div>')
					
				}
			} else if (currentNumberOfTickets > 1 && numberOfSelectedTickets < currentNumberOfTickets)
			{
				const numberOfDivsToRemove = currentNumberOfTickets-numberOfSelectedTickets;
				
				$('#ticketsHolderDiv .selected-ticket-holder').slice(-numberOfDivsToRemove).remove();
			}
			
			$('.number-of-tickets').val(1);
        });

        // check if case number is valid or the proxy is available for the user
        // the proxy should have a value of yes
        $(document).on('click', '.check-case-number', function()
        {
            var ticketNo=$(this).attr('ticket-number');
			
            var caseNumber=$('.case-number'+ticketNo+'').val();

            var loggedInUserCaseNumber=$(".case-number").text();
			
			console.log(caseNumber,loggedInUserCaseNumber);

            var userIdsObject = JSON.stringify($('.ticket-holder').map(function() {
                return $(this).attr('user-id');
            }).get());

            if(caseNumber !== '' && caseNumber.toUpperCase() === loggedInUserCaseNumber.toUpperCase())
            {
                $('.user-name'+ticketNo).removeClass('d-none').addClass('bg-danger text-white');

                $('.user-name'+ticketNo).text('').text('Case Number belongs to the logged In user').attr('is-error',1);

                return;
            }

            if(caseNumber == '')
            {
                $('.user-name'+ticketNo).removeClass('d-none').addClass('bg-danger text-white');

                $('.user-name'+ticketNo).text('').text('Enter Case Number In The Input').attr('is-error',1);

                return;
            }
            
            var url = '/check-case-number';

            $.ajax({
                url: url,
                method: 'GET',
                data: {
                    caseNumber: caseNumber,
                    userIdsObject: userIdsObject
                },
                success: function(response) {
                    console.log(response);

                    if(response.status == 415)
                    {
                        // 
                        $('.user-name'+ticketNo).removeClass('d-none').addClass('bg-danger text-white').text('').text(response.message);
                        $('.case-number'+ticketNo).attr('is-error',1);

                        $('.remove-ticket'+ticketNo).removeClass('d-none');
                        $('.ticket-holder-id'+ticketNo).removeClass('selected-ticket-id').removeAttr('user-id','');
                    } else if (response.status == 201)
                    {
                        $('.user-name'+ticketNo).removeClass('d-none').removeClass('bg-danger text-white').addClass('bg-success');
                        $('.user-name'+ticketNo).text('');
                        $('.remove-ticket'+ticketNo).addClass('d-none');

                        var fname = response.data.first_name;
                        var lname = response.data.last_name;
                        var userId = response.data.id;

                        $('.user-name'+ticketNo).text(fname+"   "+lname);
                        $('.user-name'+ticketNo).text(fname+"   "+lname);
                        $('.ticket-holder-id'+ticketNo).attr('user-id',userId);

                        $('.ticket-holder-id'+ticketNo).addClass('selected-ticket-id');

                        $('.case-number'+ticketNo).removeAttr('is-error',1);
						
						$('.number-of-tickets').val(Number($('.number-of-tickets').val())+1);
						
						$('#getTicketsForm').append('<div id="uploadSelectedTicket'+ticketNo+'" class="upload-selected-ticket-holder ticket-holder'+ticketNo+'" ><input type="text" name="user_id[]" value="'+userId+'" class="selected-ticket-ids ticket-holder ticket-holder-id'+ticketNo+'"/></div>')

                    } else if (response.status == 404)
                    {
                        $('.user-name'+ticketNo).removeClass('d-none').addClass('bg-danger text-white');
                        $('.remove-ticket'+ticketNo).removeClass('d-none');
                        $('.ticket-holder-id'+ticketNo).removeClass('selected-ticket-id');
                        
                        $('.user-name'+ticketNo).text('').text(response.message);

                        $('.case-number'+ticketNo).attr('is-error',1);

                        
                    } else if(response.status == 450)
                    {
                        // 
                        $('.user-name'+ticketNo).removeClass('d-none').addClass('bg-danger text-white').text('').text(response.message);
                        $('.remove-ticket'+ticketNo).addClass('d-none');
                        $('.ticket-holder-id'+ticketNo).removeClass('selected-ticket-id');

                        $('.case-number'+ticketNo).attr('is-error',1);

                    } else if(response.status == 430)
                    {
						$('.user-name'+ticketNo).text('').addClass('d-none');
						$('.remove-ticket'+ticketNo).addClass('d-none');
						$('.ticket-holder-id'+ticketNo).attr('user-id','');
						$('#uploadSelectedTicket'+ticketNo).remove();
						$('.number-of-tickets').val(Number($('.number-of-tickets').val())-1);
						$('.case-number'+ticketNo).attr('is-error',1);
						Swal.fire(response.message);

                    }

                }
            });
        });

        $(document).on('click', '.remove-ticket-btn', function()
        {
            var ticketNo=$(this).attr('ticket-number');

            $('.ticket-holder'+ticketNo).remove();

            var ticketsCount=$('.number-of-tickets').val();

            var newTicketsCount=ticketsCount-1;

            $('.number-of-tickets').val(newTicketsCount);
        });

        $(document).on('click', '.one-ticket-btn', function()
        {
            var userId=$(this).attr('user-id');

            $('#oneTicketModal').modal('toggle');

        });

        $(document).on('click', '.get-multiple-ticket-numbers', function()
        {
            var userIdsObject = $('.selected-ticket-id').map(function() {
                return $(this).attr('user-id');
            }).get();

            var numberOfTickets= $('.selected-case-number').length;

            var emptyInputsObject = $('.selected-case-number').filter(function() {
                return $.trim($(this).val()) === '';
            });

            var errorInputsObject = $('input[is-error="1"]').each(function() {
                $(this);   
            });

            var checkedTicketsObject = $('input[user-id]').each(function() {
                $(this);   
            });

            console.log(
			emptyInputsObject.length,
            errorInputsObject.length,
            Number(numberOfTickets),
            checkedTicketsObject.length)

            // check if user has selected another option
            // if(userIdsObject.length == 1)
            // {
            //     $('#selectNumberofTicketsModal').modal('toggle');
            // }
            if(emptyInputsObject.length == 0 && errorInputsObject.length == 0 && numberOfTickets==1  && checkedTicketsObject.length==1)
            {
                $('#selectNumberofTicketsModal').modal('toggle');
            }

            if(emptyInputsObject.length > 0 && errorInputsObject.length == 0 && checkedTicketsObject.length)
            {
                $('#emptyInputsErrorModal').modal('toggle');
            }

            if(errorInputsObject.length > 0 && emptyInputsObject.length == 0 || emptyInputsObject.length > 0 && errorInputsObject.length > 0 && numberOfTickets !=='')
            {
                $('#inputsErrorModal').modal('toggle');
				//$('#checkCaseNumberExistenceModal').modal('toggle');


            }

            if(emptyInputsObject.length == 0 && errorInputsObject.length == 0  && checkedTicketsObject.length !== Number(numberOfTickets))
            {
                $('#checkCaseNumberExistenceModal').modal('toggle');
            }

            if(Number(numberOfTickets) > 1 && emptyInputsObject.length == 0 && errorInputsObject.length == 0 && checkedTicketsObject.length == Number(numberOfTickets))
            {
                $('.tickets-count-number').text(checkedTicketsObject.length);

                $('#continuetoGetTicketsModal').modal('toggle');
            }
        });

        $(document).on('click', '.continue-to-get-tickets-btn', function(e) {
            e.preventDefault();

            // Disable buttons and show progress container
            $('.get-multiple-ticket-numbers, .continue-to-get-tickets-btn')
                .prop("disabled", true)
                .css("cursor", "not-allowed");
            
            $('#progressContainer').show();
            updateProgress(0);

            // Prepare data
            const userIdsObject = JSON.stringify($('.selected-ticket-ids').map(function() {
                return $(this).val();
            }).get());

            const submission_type = $('.submission-type').val();
            
            // Create simulated progress (0-80%) while waiting for response
            const progressInterval = setInterval(() => {
                const currentProgress = parseInt($('#progressBar').css('width'));
                if (currentProgress < 80) {
                    updateProgress(currentProgress + 1);
                }
            }, 50);

            // Create AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route("manage_tickets_generation") }}', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            
            // Real upload progress (if applicable)
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
            
            // Send request
            xhr.send(JSON.stringify({
                userIdsObject: userIdsObject,
                submission_type: submission_type,
                _token: document.querySelector('meta[name="csrf-token"]').content
            }));

            // Helper functions
            function handleResponse(response) {
                $('.get-multiple-ticket-numbers, .continue-to-get-tickets-btn')
                    .prop("disabled", false)
                    .css("cursor", "pointer");

                switch(response.status) {
                    case 429:
                    case 500:
                    case 530:
                    case 550:
                        Swal.fire(response.message);
                        $('#progressContainer').hide();
                        break;
                    case 200:
                        $('.redirect-text').addClass('d-block').removeClass('d-none');
                        setTimeout(() => {
                            window.location.href = '/multiple-users-ticket-details/' + response.id;
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

// $(document).on('click', '.continue-to-get-tickets-btn', function(e) {
//     e.preventDefault();

//     $('.get-multiple-ticket-numbers,.continue-to-get-tickets-btn')
//         .prop("disabled", true)
//         .css("cursor", "not-allowed");
    
//     $('#progressContainer').show();
//     updateProgress(0);

//     var userIdsObject = JSON.stringify($('.selected-ticket-ids').map(function() {
//         return $(this).val();
//     }).get());

//     var submission_type=$('.submission-type').val();
    
//     const xhr = new XMLHttpRequest();
//     xhr.open('POST', '{{ route("manage_tickets_generation") }}', true);
//     xhr.setRequestHeader('Content-Type', 'application/json');
//     xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
//     xhr.setRequestHeader('Accept', 'application/json');
    
//     xhr.upload.addEventListener('progress', function(e) {
//         if (e.lengthComputable) {
//             const percent = Math.round((e.loaded / e.total) * 100);
//             updateProgress(percent);
//         }
//     });

    
//     xhr.onload = function() {
//         try {
//             const response = JSON.parse(xhr.responseText);
//             console.log(response);

//             $('.get-multiple-ticket-numbers,.continue-to-get-tickets-btn')
//                 .prop("disabled", false)
//                 .css("cursor", "pointer");

//             switch(response.status) {
//                 case 429:
//                 case 500:
//                 case 530:
//                 case 550:
//                     Swal.fire(response.message);
//                     break;
//                 case 200:
//                     $('#progressContainer').hide();
//                     $('.redirect-text').addClass('d-block').removeClass('d-none');
//                     window.location.href = '/multiple-users-ticket-details/' + response.id;
                    
//                     break;
//                 default:
//                     Swal.fire('Unexpected response from server');
//             }
//         } catch (e) {
//             Swal.fire('Error parsing server response');
//         }
//     };

    
//     xhr.onerror = function() {
//         clearInterval(progressCheckInterval);
//         $('#progressContainer').hide();
//         Swal.fire('Network error occurred');
//     };
    
//     // Send request
//     xhr.send(JSON.stringify({
//         userIdsObject: userIdsObject,
//         submission_type: submission_type,
//         _token: document.querySelector('meta[name="csrf-token"]').content
//     }));
// });

// function updateProgress(percent) {
//     $('#progressBar').css('width', percent + '%').text(percent + '%');
// }

                        

        $(document).on('click', '.close-modal', function()
        {

            $('#multipleTicketModal').modal('hide');

            $('#inputsErrorModal').modal('hide');

            $('#emptyInputsErrorModal').modal('hide');

            $('#selectNumberofTicketsModal').modal('hide');

            $('.tickets-count-number').text('');

            $('#continuetoGetTicketsModal').modal('hide');

            $('#checkCaseNumberExistenceModal').modal('hide');
        });
   </script>
@endsection
