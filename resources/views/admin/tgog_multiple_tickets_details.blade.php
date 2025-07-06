@extends('admin.layout')
@section('content')
@section('admin_multiple_tickets_styles')

    <style>
        /* *{
            font-size: 16px; 
            color:black; 
            font-weight:500;
        } */
        
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
	gap: 40px;
	align-items: center;
	}
		
	.swal-button {
            background-color: #ffc107 !important; /* Change to your desired color */
            color: black; !important; /* Change text color */
        }
        .swal-button:hover {
            background-color: #ffc107; !important; /* Change on hover */
        }
		

    .main-text-holder
    {
        font-size: 50px;font-weight: 500;text-align: center;
    }

    @media(max-width:768px)
    {
        .selected-ticket-holder,.main-case-ticket-holder
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

        .main-text-holder
        {
            font-size: 30px;
        }
    }

    @media(max-width:480px)
    {
        .add-extra-tickets
        {
            gap:20px;
        }
    }
    </style>
@stop
        <h2 class="text-center m-4">Get Multiple Ticket Numbers</h2>
    
		<div style="font-size: 16px; 
            color:black; 
            font-weight:500;" class="m-4">
                <label class="d-none">Enter Main Case Number</label>
				<!-- @if($userDetails->role === '1' || $userDetails->role === '3')
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
				@endif -->
                <div class="main-case-ticket-holder ticket-holder11 ticket-holder-user11">
                    <input type="text" class="form-control selected-case-number case-number11" placeholder="Case Number">
                    <button type="button" ticket-number="11" class="btn btn-warning verify-admin-case-number">Check</button>
                    <button type="button" ticket-number="11" class="btn btn-danger d-none remove-ticket11 remove-ticket-btn">Remove</button>
                    <span class="user-name11 error-span"></span>
					<input id="adminTicketHolder" type="hidden" class="ticket-holder extra-ticket ticket-holder-id11 selected-ticket-id">
                </div>
                <!-- <div class="d-none main-case-ticket-holder ticket-holder11 ticket-holder-user{{$userDetails->id}}">
                    <input type="text" class="form-control selected-case-number case-number11" {{ $readonlyAttr }} value="{{ $inputValue }}" placeholder="Case Number">
                    <button type="button" ticket-number="11" style="pointer-events:{{ $customStyle }};" {{ $disabledAttr }} class="btn btn-warning verify-admin-case-number">Check</button>
                    <button type="button" ticket-number="11" class="btn btn-danger d-none remove-ticket11 remove-ticket-btn">Remove</button>
                    <span class="user-name11 error-span {{ $bgColor}}">{{ $nameText }}</span>
					@if($userDetails->role == 1)
						<input id="adminTicketHolder" type="hidden" class="ticket-holder extra-ticket ticket-holder-id11 selected-ticket-id">
					@else
						<input id="adminTicketHolder" type="hidden" class="ticket-holder extra-ticket ticket-holder-id11 selected-ticket-id" user-id="{{ $nameId }}">
					@endif
                </div> -->

                <input type="hidden" class="number-of-tickets">
            </div>
        <div class="add-extra-tickets">
            <span>How Many Additional Ticket Numbers</span>
            <select style="width:30%;" class="form-control text-dark number-of-tickets-id" required>
                <option selected disabled value="">Number Of Additional Tickets</option>
                <option value="1">+1</option>
                <option value="2">+2</option>
                <option value="3">+3</option>
                <option value="4">+4</option>
                <option value="5">+5</option>
                <option value="6">+6</option>
                <option value="7">+7</option>
                <option value="8">+8</option>
                <option value="9">+9</option>
            </select>
        </div>
        <div id="ticketsHolderDiv" style="padding: 10px; margin-top:10px;font-size: 16px; 
            color:black; 
            font-weight:500;"></div>
        <div style="display: flex; justify-content:space-around; align-items:space-between;">
            <a class="get-multiple-ticket-numbers btn btn-warning btn-lg px-5 border border-dark py-1 mb-2 " style="border: 1px solid #343a40; text-decoration: none;">Get Ticket Numbers</a>
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
                    <input type="hidden" name="submission_type" class="submission-type" value="1"/>
                    <div id="getTicketsFormAdmin" class="d-none"></div>
                    <div style="display: flex;justify-content: space-around;align-items: center;margin: 5px;">
                        <a class="btn btn-warning waves-effect border border-dark close-modal">Cancel</a>
                        <button class="btn btn-warning waves-effect border border-dark continue-to-get-tickets-btn" type="submit">Continue</button>
                    </div>
                    <div class="progress-container" id="progressContainer">
                        <div id="progressBar" class="progress-bar"></div>
                    </div>
                    <span style="color: red;font-size: 18px;font-weight: 500;font-style: italic;text-align: center;" class="redirect-text d-none">Ticket/s Is being Processed.Please Wait As you Are being redirected to the Ticket Details</span>
			   </div>
           </div>
        </div>
    </div>

    <div class="modal fade" id="barcodeScanSingleTicketModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
           <div class="modal-content">
                <div class="modal-title" style="text-align: center;">
                   <h4>ID Scan</h4> 
                </div>
                <div class="modal-body">
                    <input id="barcodeScanUserId" type="hidden">
                    <input id="prevBarcodeScanUserId" type="hidden">
                    <table style="width:100%; text-align:center;">
                        <thead>
                            <tr>
                                <th style="border:2px solid rgb(17, 15, 15); padding:5px;">First Name</th>
                                <th style="border:2px solid rgb(17, 15, 15); padding:5px;">Last Name</th>
                                <th style="border:2px solid rgb(17, 15, 15); padding:5px;">Case Number</th>
                                <th style="border:2px solid rgb(17, 15, 15); padding:5px;">Date Of Birth</th>
                                <th style="border:2px solid rgb(17, 15, 15); padding:5px;">Proxy</th>
                            </tr>
                        </thead>
                        <tr style="border:2px solid rgb(17, 15, 15); font-size:20px; font-weight:400; color:black;">
                            <td style="border:2px solid rgb(17, 15, 15); padding:5px;" id="barcodeScanFirstName"></td>
                            <td style="border:2px solid rgb(17, 15, 15); padding:5px;" id="barcodeScanLastName"></td>
                            <td style="border:2px solid rgb(17, 15, 15); padding:5px;" id="barcodeScanDoB"></td>
                            <td style="border:2px solid rgb(17, 15, 15); padding:5px;" id="barcodeScanCaseNumber"></td>
                            <td style="border:2px solid rgb(17, 15, 15); padding:5px;" id="barcodeScanProxy"></td>
                        </tr>
                    </table>
                </div>
           </div>
        </div>
    </div>


@endsection

@section('admin_multiple_tickets_script')
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script> 

    {{-- moment --}}
   <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>

   <script>
        $(document).ready(function() {

            document.getElementById('backToBottom').style.display = "none";
            
            $('.admin-select2').select2();
			
			const timeout = 600000;  // 900000 ms = 5 minutes
			var idleTimer = null;
			$('*').bind('mousemove click mouseup mousedown keydown keypress keyup submit change mouseenter scroll resize dblclick', function () {
				clearTimeout(idleTimer);

				idleTimer = setTimeout(function () {
					window.location.reload(true);
				}, timeout);
			});
            
			if($(".ticket-holder-id11").attr('user-id')) {
				
				var userId=$(".ticket-holder-id11").attr('user-id');
				
				$('#getTicketsFormAdmin').prepend('<div id="uploadAdminSelectedTicket11" class="upload-selected-ticket-holder ticket-holder11" ><input type="text" name="user_id[]" value="'+userId+'" class="ticket-holder selected-user-id ticket-holder-id11"/></div>')
			}

            var barcode = '';
            var interval;

            document.addEventListener('keydown',function(e)
            {
                console.log('123,');
                if(interval)
                     clearInterval(interval);

                if(e.code == 'Enter')
                {
                    if(barcode)

                    handleBarcode(barcode);

                    barcode = '';

                    return;
                }

                if(e.key != 'Shift')

                barcode += e.key;

                interval = setInterval(()=> barcode = '',1000000)
            });

            function handleBarcode(scanned_barcode)
            {
                var loggedInUserId = {{$userDetails->id}}

                let case_number = scanned_barcode.replace(/[^\d]/g, "");

                console.log(case_number);

                // get user details based on case number
                var url = '/admin/get-users-details';

                $.ajax({
                    url: url,
                    method: 'GET',
                    data: {
                        case_number: case_number
                    },
                    success: function(response) {
                        console.log(response);

                        if(response.status == 404)
                        {
                            Swal.fire(response.message);

                            $('#barcodeScanSingleTicketModal').modal('hide');

                        } 
                        else if (response.status == 500)
                        {
                            Swal.fire(response.message);
                            
                            $('#barcodeScanSingleTicketModal').modal('hide');
                        }
                        else if (response.status == 450)
                        {
                            Swal.fire(response.message);
                        }
                        else if (response.status == 200)
                        {
                            var fname = response.data.first_name;
                            var lname = response.data.last_name;
                            var case_number = response.data.case_number;
                            var userId = response.data.id;
                            var proxy = response.data.proxy;
                            var dateOfBirth=moment(response.data.date_of_birth).format("MM/DD/YYYY");

                            var currentTicketUserId=$('#barcodeScanUserId').val();

                            var previousTicketUserId=$('#prevBarcodeScanUserId').val();

                            
                            if($('.ticket-holder-user'+userId).length > 0)
                            {
                                var message = 'The User has already been selected';

                                swal(message);

                                $('#barcodeScanSingleTicketModal').modal('hide');

                            } else {
                                if ($('#barcodeScanSingleTicketModal').hasClass('show') && userId == currentTicketUserId) 
                                {
                                    if($('.case-number11').val() == '' || Number($('.number-of-tickets').val()) == 1 || $('.number-of-tickets').val() == '')
                                    {
                                        $('.case-number11').val(case_number).removeAttr('is-error');
                                        $('.user-name11').removeClass('d-none').removeClass('bg-danger text-white').addClass('bg-success').text('');
                                        $('.remove-ticket11').addClass('d-none');

                                        $('.user-name11').text(fname+"   "+lname);

                                        $('.ticket-holder-id11').attr('user-id',userId).addClass('selected-ticket-id').removeClass('ticket-holder-user'+loggedInUserId).removeClass('ticket-holder-user'+previousTicketUserId).addClass('ticket-holder-user'+userId);

                                        // $('.ticket-holder-id11').attr('user-id',userId).addClass('selected-ticket-id').addClass('ticket-holder-user'+userId);
                                        
                                        if($("#uploadAdminSelectedTicket11").length == 0) {
                                            $('#getTicketsFormAdmin').prepend('<div id="uploadAdminSelectedTicket11" class="upload-selected-ticket-holder ticket-holder11" ><input type="text" name="user_id[]" value="'+userId+'" class="selected-user-id ticket-holder ticket-holder-id11"/></div>')
                                        } else {
                                            $('#uploadAdminSelectedTicket11').remove();
                                            
                                            $('#getTicketsFormAdmin').prepend('<div id="uploadAdminSelectedTicket11" class="upload-selected-ticket-holder ticket-holder11" ><input type="text" name="user_id[]" value="'+userId+'" class="selected-user-id ticket-holder ticket-holder-id11"/></div>')
                                        }

                                        $('#prevBarcodeScanUserId').val(userId);

                                        $('#barcodeScanSingleTicketModal').modal('hide');
                                    } else {
                                        if(proxy == 'Yes')
                                        {
                                            let selectedTicketsArray = $('.selected-ticket-holder').map(function() {
                                                return $(this).attr('ticket-number')
                                            }).get();

                                            let ticketNumber=selectedTicketsArray[0];

                                            $('.user-name'+ticketNumber).removeClass('d-none').removeClass('bg-danger text-white').addClass('bg-success').text('');
                                            $('.remove-ticket'+ticketNumber).addClass('d-none');

                                            

                                            $('.ticket-holder'+ticketNumber).removeAttr('ticket-number').addClass('ticket-holder-user'+userId);

                                            $('.case-number'+ticketNumber).val(case_number);

                                            $('.user-name'+ticketNumber).text(fname+"   "+lname);
                                            $('.ticket-holder-id'+ticketNumber).attr('user-id',userId);

                                            if($('#adminTicketHolder'+ticketNumber).length == 0)
                                            {
                                                
                                            } else {
                                                $('#adminTicketHolder'+ticketNumber).val(userId);
                                            }
                                            $('.ticket-holder-id'+ticketNumber).addClass('selected-ticket-id');

                                            $('.case-number'+ticketNumber).removeAttr('is-error');
                                            
                                        } else {

                                            var noProxyMsg='User has a proxy of No';

                                            Swal.fire(noProxyMsg);
                                        }
                                    }

                                    $('#barcodeScanSingleTicketModal').modal('hide');

                                }

                                if ($('#barcodeScanSingleTicketModal').hasClass('show') && previousTicketUserId == userId) 
                                {
                                    if($('.case-number11').val() == '' || Number($('.number-of-tickets').val()) == 1 || $('.number-of-tickets').val() == '')
                                    {
                                        $('.case-number11').val(case_number).removeAttr('is-error');
                                        $('.user-name11').removeClass('d-none').removeClass('bg-danger text-white').addClass('bg-success').text('');
                                        $('.remove-ticket11').addClass('d-none');

                                        $('.user-name11').text(fname+"   "+lname);

                                        $('.ticket-holder-id11').attr('user-id',userId).addClass('selected-ticket-id').removeClass('ticket-holder-user'+loggedInUserId).removeClass('ticket-holder-user'+previousTicketUserId).addClass('ticket-holder-user'+userId);

                                        // $('.ticket-holder-id11').attr('user-id',userId).addClass('selected-ticket-id').addClass('ticket-holder-user'+userId);
                                        
                                        if($("#uploadAdminSelectedTicket11").length == 0) {
                                            $('#getTicketsFormAdmin').prepend('<div id="uploadAdminSelectedTicket11" class="upload-selected-ticket-holder ticket-holder11" ><input type="text" name="user_id[]" value="'+userId+'" class="selected-user-id ticket-holder ticket-holder-id11"/></div>')
                                        } else {
                                            $('#uploadAdminSelectedTicket11').remove();
                                            
                                            $('#getTicketsFormAdmin').prepend('<div id="uploadAdminSelectedTicket11" class="upload-selected-ticket-holder ticket-holder11" ><input type="text" name="user_id[]" value="'+userId+'" class="selected-user-id ticket-holder ticket-holder-id11"/></div>')
                                        }

                                        $('#prevBarcodeScanUserId').val(userId);

                                        $('#barcodeScanSingleTicketModal').modal('hide');
                                    } else {
                                        if(proxy == 'Yes')
                                        {
                                            let selectedTicketsArray = $('.selected-ticket-holder').map(function() {
                                                return $(this).attr('ticket-number')
                                            }).get();

                                            let ticketNumber=selectedTicketsArray[0];

                                            $('.user-name'+ticketNumber).removeClass('d-none').removeClass('bg-danger text-white').addClass('bg-success').text('');
                                            $('.remove-ticket'+ticketNumber).addClass('d-none');

                                            

                                            $('.ticket-holder'+ticketNumber).removeAttr('ticket-number').addClass('ticket-holder-user'+userId);

                                            $('.case-number'+ticketNumber).val(case_number);

                                            $('.user-name'+ticketNumber).text(fname+"   "+lname);
                                            $('.ticket-holder-id'+ticketNumber).attr('user-id',userId);

                                            if($('#adminTicketHolder'+ticketNumber).length == 0)
                                            {
                                                
                                            } else {
                                                $('#adminTicketHolder'+ticketNumber).val(userId);
                                            }
                                            $('.ticket-holder-id'+ticketNumber).addClass('selected-ticket-id');

                                            $('.case-number'+ticketNumber).removeAttr('is-error');
                                        } else {

                                            var noProxyMsg='User has a proxy of No';

                                            swal(noProxyMsg);
                                        }
                                    }

                                    // $('#barcodeScanSingleTicketModal').modal('hide');

                                } else if($('#barcodeScanSingleTicketModal').hasClass('show') && previousTicketUserId !== userId) 
                                {

                                    $('#barcodeScanFirstName,#barcodeScanLastName,#barcodeScanDoB,#barcodeScanCaseNumber,#barcodeScanUserId,#barcodeScanProxy').text('');
                                    $('#barcodeScanUserId').val(response.data.id);
                                    $('#barcodeScanFirstName').text(response.data.first_name);
                                    $('#barcodeScanLastName').text(response.data.last_name);
                                    $('#barcodeScanDoB').text(dateOfBirth);
                                    $('#barcodeScanCaseNumber').text(response.data.case_number);
                                    $('#barcodeScanProxy').text(response.data.proxy);

                                    $('#prevBarcodeScanUserId').val(response.data.id);
                                } else {
                                    $('#barcodeScanSingleTicketModal').modal('toggle');

                                    $('#barcodeScanFirstName,#barcodeScanLastName,#barcodeScanDoB,#barcodeScanCaseNumber,#barcodeScanUserId,#barcodeScanProxy').text('');
                                    $('#barcodeScanUserId').val(response.data.id);
                                    $('#barcodeScanFirstName').text(response.data.first_name);
                                    $('#barcodeScanLastName').text(response.data.last_name);
                                    $('#barcodeScanDoB').text(dateOfBirth);
                                    $('#barcodeScanCaseNumber').text(response.data.case_number);
                                    $('#barcodeScanProxy').text(response.data.proxy);
                                }
                            }  
                        }
                    }
                });
            }
        });

        $(document).on('click', '.continue-approval-on-expire-btn', function()
        {
            $('#continueApprovalOnExpireModal').modal('hide');

            window.location.reload();
        });

        $(document).on('change', '.number-of-tickets-id', function(e) {
            e.preventDefault();

            var numberOfTicketsId = Number($(this).val());
			
			const currentNumberOfTickets = $('.selected-ticket-holder').length;
			
			const divsToGenerate = Number(numberOfTicketsId)-Number(currentNumberOfTickets);
            
            //$("#ticketsHolderDiv").empty();
			
			console.log(currentNumberOfTickets,numberOfTicketsId,currentNumberOfTickets);
			
			$(".number-of-tickets-id option:selected").prop("selected", false);
			$(".number-of-tickets-id option:first").prop("selected", "selected");

            $('.add-extra-tickets .select2 .selection .select2-selection .select2-selection__rendered').html('').append(
                '<li class="select2-selection__choice" title="Number of Additional Tickets" data-select2-id="21"><span class="select2-selection__choice__remove" role="presentation"></span>Number of Additional Tickets</li>'
            );
			
			if(currentNumberOfTickets == 0 && numberOfTicketsId > currentNumberOfTickets)
			{
				for (var i=0; i<numberOfTicketsId; i++)
				{
					$('#ticketsHolderDiv').append('<div class="selected-ticket-holder ticket-holder'+[i]+'" ticket-number="'+[i]+'"><input type="text" class="form-control selected-case-number case-number'+[i]+'" placeholder="Case Number"><button type="button" ticket-number="'+[i]+'" class="btn btn-warning check-case-number">Check</button><button type="button" ticket-number="'+[i]+'" class="btn btn-danger d-none remove-ticket'+[i]+' remove-ticket-btn">Remove</button><span class="user-name'+[i]+' d-none error-span"></span><input type="hidden" class="ticket-holder extra-ticket ticket-holder-id'+[i]+'"/></div>')
					
					$('#getTicketsFormAdmin').append('<div id="uploadAdminSelectedTicket'+[i]+'" class="upload-selected-ticket-holder ticket-holder'+[i]+'" ><input type="text" name="user_id[]" id="adminTicketHolder'+[i]+'" class="selected-user-id ticket-holder"/></div>')
				}
			} else if (currentNumberOfTickets > 0 && numberOfTicketsId > currentNumberOfTickets)
			{
				const lastDivTicketNumber=Number($('#ticketsHolderDiv div button:last').attr('ticket-number'));
				
				for (var i=0; i<divsToGenerate; i++)
				{
					const nextDivNumber=lastDivTicketNumber + 1 + i;
					
					$('#ticketsHolderDiv').append('<div class="selected-ticket-holder ticket-holder'+[nextDivNumber]+'" ticket-number="'+[nextDivNumber]+'"><input type="text" class="form-control selected-case-number case-number'+[nextDivNumber]+'" placeholder="Case Number"><button type="button" ticket-number="'+[nextDivNumber]+'" class="btn btn-warning check-case-number">Check</button><button type="button" ticket-number="'+[nextDivNumber]+'" class="btn btn-danger d-none remove-ticket'+[nextDivNumber]+' remove-ticket-btn">Remove</button><span class="user-name'+[nextDivNumber]+' d-none error-span"></span><input type="hidden" class="ticket-holder extra-ticket ticket-holder-id'+[nextDivNumber]+'"/></div>')
					
					$('#getTicketsFormAdmin').append('<div id="uploadAdminSelectedTicket'+[nextDivNumber]+'" class="upload-selected-ticket-holder ticket-holder'+[nextDivNumber]+'" ><input type="text" name="user_id[]" id="adminTicketHolder'+[nextDivNumber]+'" class="selected-user-id ticket-holder"/></div>')
				}
			} else if (currentNumberOfTickets > 0 && numberOfTicketsId < currentNumberOfTickets)
			{
				const numberOfDivsToRemove = currentNumberOfTickets-numberOfTicketsId;
				
				$('#ticketsHolderDiv .selected-ticket-holder').slice(-numberOfDivsToRemove).remove();
				
				$('#getTicketsFormAdmin .upload-selected-ticket-holder').slice(-numberOfDivsToRemove).remove();
			}
			
			$('.number-of-tickets').val(Number($('.selected-ticket-holder').length)+1);
        });

        $(document).on('click', '.verify-admin-case-number', function()
        {
            //var ticketNo=$(this).attr('ticket-number');
            var caseNumber=$('.case-number11').val();

            var userIdsObject = JSON.stringify($('.ticket-holder').map(function() {
                return $(this).attr('user-id');
            }).get());

            if(caseNumber == '')
            {
                $('.user-name11').removeClass('d-none').addClass('bg-danger text-white').text('').text('Enter Case Number In The Input').attr('is-error',1);

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
                        $('.user-name11').removeClass('d-none').addClass('bg-danger text-white').text('').text(response.message);
                        $('.case-number11').attr('is-error',1);

                        //$('.remove-ticket'+ticketNo).removeClass('d-none');
                        $('.ticket-holder-id11').removeClass('selected-ticket-id').removeAttr('user-id');
                    } else if(response.status == 450)
                    {
                        // 
                        $('.user-name11').addClass('d-none').text('');
						$('.ticket-holder-id11').removeAttr('user-id').removeClass('selected-ticket-id');
						Swal.fire(response.message);

                        $('.case-number11').attr('is-error',1);

                    }  else if(response.status == 430)
                    {
                        $('.user-name11').text('').addClass('d-none');
						
						Swal.fire(response.message);

                        $('.case-number11').attr('is-error',1);
						$('.ticket-holder-id11').removeAttr('user-id');

                    }  else if (response.status == 201)
                    {
                        $('.user-name11').removeClass('d-none').removeClass('bg-danger text-white').addClass('bg-success').text('');
                        $('.remove-ticket11').addClass('d-none');

                        var fname = response.data.first_name;
                        var lname = response.data.last_name;
                        var userId = response.data.id;

                        $('.user-name11').text(fname+"   "+lname);
                        $('.ticket-holder-id11').attr('user-id',userId).addClass('selected-ticket-id');

                        $('.case-number11').removeAttr('is-error');
						
						if($("#uploadAdminSelectedTicket11").length == 0) {
							$('#getTicketsFormAdmin').prepend('<div id="uploadAdminSelectedTicket11" class="upload-selected-ticket-holder ticket-holder11" ><input type="text" name="user_id[]" value="'+userId+'" class="selected-user-id ticket-holder ticket-holder-id11"/></div>')
						} else {
							$('#uploadAdminSelectedTicket11').remove();
							
							$('#getTicketsFormAdmin').prepend('<div id="uploadAdminSelectedTicket11" class="upload-selected-ticket-holder ticket-holder11" ><input type="text" name="user_id[]" value="'+userId+'" class="selected-user-id ticket-holder ticket-holder-id11"/></div>')
						}
                    }

                }
            });
        });
        // check if case number is valid or the proxy is available for the user
        // the proxy should have a value of yes
        $(document).on('click', '.check-case-number', function()
        {
            var ticketNo=$(this).attr('ticket-number');
            var caseNumber=$('.case-number'+ticketNo+'').val();

            var loggedInUserCaseNumber=$(".case-number").text();

            var userIdsObject = JSON.stringify($('.ticket-holder').map(function() {
                return $(this).attr('user-id');
            }).get());

            if(caseNumber.toUpperCase() === loggedInUserCaseNumber.toUpperCase() && caseNumber !== '' && loggedInUserCaseNumber !== '')
            {
                $('.user-name'+ticketNo).removeClass('d-none').addClass('bg-danger text-white').text('').text('Case Number belongs to the logged In user').attr('is-error',1);

                return;
            }

            if(caseNumber == '')
            {
                $('.user-name'+ticketNo).removeClass('d-none').addClass('bg-danger text-white').text('').text('Enter Case Number In The Input').attr('is-error',1);

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

					//$('.ticket-already-selected'+ticketNo).addClass('d-none');
                    //$('.ticket-already-selected'+ticketNo).removeClass('bg-danger text-white');
					//$('.ticket-already-selected'+ticketNo).text('');
                    if(response.status == 415)
                    {
                        // 
                        $('.user-name'+ticketNo).removeClass('d-none').addClass('bg-danger text-white').text('').text(response.message);
                        $('.case-number'+ticketNo).attr('is-error',1);

                        $('.remove-ticket'+ticketNo).removeClass('d-none');
                        $('.ticket-holder-id'+ticketNo).removeClass('selected-ticket-id');
                        $('.ticket-holder-id'+ticketNo).removeAttr('user-id');
                    } else if (response.status == 201)
                    {
                        $('.user-name'+ticketNo).removeClass('d-none').removeClass('bg-danger text-white').addClass('bg-success').text('');
                        $('.remove-ticket'+ticketNo).addClass('d-none');

                        var fname = response.data.first_name;
                        var lname = response.data.last_name;
                        var userId = response.data.id;

                        $('.user-name'+ticketNo).text(fname+"   "+lname);
                        $('.ticket-holder-id'+ticketNo).attr('user-id',userId);
						
						if($('#adminTicketHolder'+ticketNo).length == 0)
						{
							
						} else {
							console.log('div exists')
							
							$('#adminTicketHolder'+ticketNo).val(userId);
						}
                        $('.ticket-holder-id'+ticketNo).addClass('selected-ticket-id');

                        $('.case-number'+ticketNo).removeAttr('is-error');
						
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
                        $('.remove-ticket'+ticketNo).removeClass('d-none');
                        $('.ticket-holder-id'+ticketNo).removeClass('selected-ticket-id');

                        $('.case-number'+ticketNo).attr('is-error',1);

                    } else if(response.status == 430)
                    {
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
			
			console.log(errorInputsObject.length,emptyInputsObject.length,checkedTicketsObject.length,numberOfTickets);

			if(errorInputsObject.length == 0 && emptyInputsObject.length == 0 && errorInputsObject.length == 0 && numberOfTickets == 1)
			{
				$('#selectNumberofTicketsModal').modal('toggle');
			}
			
			if(errorInputsObject.length == 0 && emptyInputsObject.length > 0 && errorInputsObject.length == 0 && numberOfTickets !== '')
			{
				$('#emptyInputsErrorModal').modal('toggle');
			}
            

            if((errorInputsObject.length > 0 && emptyInputsObject.length == 0 )|| (emptyInputsObject.length > 0 && errorInputsObject.length > 0 && numberOfTickets !==''))
            {
                $('#inputsErrorModal').modal('toggle');
            }

            if(emptyInputsObject.length == 0 && errorInputsObject.length == 0  && checkedTicketsObject.length !== Number(numberOfTickets))
            {
                $('#checkCaseNumberExistenceModal').modal('toggle');
            }

            if(emptyInputsObject.length == 0 && errorInputsObject.length == 0 && checkedTicketsObject.length == Number(numberOfTickets))
            {
                $('.tickets-count-number').text(checkedTicketsObject.length);

                $('#continuetoGetTicketsModal').modal('toggle');
            }
        });

        $(document).on('click', '.one-ticket-modal-continue-btn', function()
        {
            var userId=$(this).attr('user-id');

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            var submission_type= 0;

            var url = '/manage-tickets-generation';

            $('#oneTicketModal').modal('hide');

            $('#savingPopUpModal').modal('toggle');

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    submission_type: submission_type,
                    userId: userId,
                    _token: csrf
                },
                success: function(response) {

                    console.log(response);

                    var redirecturl = '/one-ticket-details/'+response.ticket_id;
                    window.location.href = redirecturl;
                }
            });

        });

        $(document).on('click', '.multiple-ticket-btn', function()
        {
            $('#multipleTicketModal').modal('toggle');
        });
        
        $(document).on('click', '.continue-to-get-tickets-btn', function(e) {
            e.preventDefault();

            $('.get-multiple-ticket-numbers,.continue-to-get-tickets-btn')
                .prop("disabled", true)
                .css("cursor", "not-allowed");
            
            $('#progressContainer').show();
            updateProgress(0);

            var userIdsObject = JSON.stringify($('.selected-user-id').map(function() {
                return $(this).val();
            }).get());

            var submission_type=$('.submission-type').val();
            
            // // Get form values
            // const formData = {
            //     userIdsObject: userIdsObject,
            //     submission_type: submission_type,
            //     _token: document.querySelector('meta[name="csrf-token"]').content
            // };
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route("manage_tickets_generation") }}', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    updateProgress(percent);
                }
            });

            
            xhr.onload = function() {
                try {
                    const response = JSON.parse(xhr.responseText);
                    console.log(response);

                    $('.get-multiple-ticket-numbers,.continue-to-get-tickets-btn')
                        .prop("disabled", false)
                        .css("cursor", "pointer");

                    switch(response.status) {
                        case 429:
                        case 500:
                        case 530:
                        case 550:
                            Swal.fire(response.message);
                            break;
                        case 200:
                            // updateProgress(100);
                             $('.redirect-text').addClass('d-block').removeClass('d-none');
                            window.location.href = '/multiple-users-ticket-details/' + response.id;
                            
                            break;
                        default:
                            Swal.fire('Unexpected response from server');
                    }
                } catch (e) {
                    Swal.fire('Error parsing server response');
                }
            };

            
            xhr.onerror = function() {
                clearInterval(progressCheckInterval);
                $('#progressContainer').hide();
                Swal.fire('Network error occurred');
            };
            
            // Send request
            xhr.send(JSON.stringify({
                userIdsObject: userIdsObject,
                submission_type: submission_type,
                _token: document.querySelector('meta[name="csrf-token"]').content
            }));
        });

        function updateProgress(percent) {
            $('#progressBar').css('width', percent + '%').text(percent + '%');
        }


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