@extends('admin.layout')
@section('content')
@section('admin_single_ticket_styles')

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

    .selected-ticket-holder
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

    /* #ticketsInQueueModal
    {
        background-color:#ffc107;
    } */

    .main-text-holder
    {
        font-size: 50px;font-weight: 500;text-align: center;
    }

    @media(max-width:768px)
    {
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


<h2 class="text-center m-5">Get Single Ticket</h2>
<div class="mt-5 " style="font-size: 16px; 
            color:black; font-weight:500;">
    <label>Enter Case Number</label>
    <div class="selected-ticket-holder ticket-holder11" >
        <input type="text" class="form-control selected-case-number case-number11" placeholder="Case Number">
        <button type="button" ticket-number="11" class="btn btn-warning verify-single-case-number">Check</button>
        <span class="user-name11 d-none error-span"></span>
        <input type="hidden" class="ticket-holder extra-ticket ticket-holder-id11"
            />
    </div>

    <input type="hidden" class="logged-in-case-number" value="{{ Auth::user()->case_number }}">
</div>

<div class="mt-4" style="display: flex; justify-content:space-around; align-items:space-between;">
        <a class="get-single-ticket-number btn btn-warning btn-lg px-5 border border-dark py-1 mb-2" is-verified="0" style="border: 1px solid #343a40; text-decoration: none;">Get Ticket Number</a>
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

    $oneTicketModalData = $notificationmsgs->firstWhere('modal_no', 9);
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
                        </tr>
                    </thead>
                    <tr style="border:2px solid rgb(17, 15, 15); font-size:20px; font-weight:400; color:black;">
                        <td style="border:2px solid rgb(17, 15, 15); padding:5px;" id="barcodeScanFirstName"></td>
                        <td style="border:2px solid rgb(17, 15, 15); padding:5px;" id="barcodeScanLastName"></td>
                        <td style="border:2px solid rgb(17, 15, 15); padding:5px;" id="barcodeScanDoB"></td>
                        <td style="border:2px solid rgb(17, 15, 15); padding:5px;" id="barcodeScanCaseNumber"></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="emptyInputsErrorModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
            <p>Kindly Enter the Case Number and verify</p>
        </div>
        <div style="display: flex;justify-content: space-around; align-items: center;margin: 5px;">
        <a class="btn btn-warning waves-effect border border-dark close-modal">close</a>
        
        </div>
        </div>
    </div>
</div>

<div class="modal fade" id="checkCaseNumberExistenceModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
            <p>Verify if the case Number exists</p>
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
        <div style="display: flex;justify-content: space-around;align-items: center; margin: 5px;">
        <a class="btn btn-warning waves-effect border border-dark close-modal">close</a>
        
        </div>
        </div>
    </div>
</div>

<div class="modal fade" id="continuetoGetTicketsModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
            <p>You will receive <span class="tickets-count-number"></span> tickets</p>
            <p>{{ Str::limit(strip_tags($continuetoGetTicketsData->message)) }}</p>
        </div>
        <div style="display: flex;justify-content: space-around;align-items: center;margin: 5px;">
        <a class="btn btn-warning waves-effect border border-dark close-modal">Cancel</a>
        <a class="btn btn-warning waves-effect border border-dark continue-to-get-tickets-btn">Continue</a>
        </div>
        </div>
    </div>
</div>


@endsection

@section('admin_single_ticket_script')
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script> 

    {{-- moment --}}
   <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>

   <script>
        $(document).ready(function() {

            document.getElementById('backToBottom').style.display = "none";
			
			const timeout = 420000;  // 900000 ms = 5 minutes
			var idleTimer = null;
			$('*').bind('mousemove click mouseup mousedown keydown keypress keyup submit change mouseenter scroll resize dblclick', function () {
				clearTimeout(idleTimer);

				idleTimer = setTimeout(function () {
					
					window.location.reload(true);
				}, timeout);
			});
			
			if($(".ticket-holder-id11").attr('user-id')) {
				
				var userId=$(".ticket-holder-id11").attr('user-id');
				
				$('#getTicketsFormAdmin').prepend('<div id="uploadAdminSelectedTicket11" class="upload-selected-ticket-holder ticket-holder11" ><input type="text" name="user_id[]" value="'+userId+'" class="ticket-holder ticket-holder-id11"/></div>')
			}


            var barcode = '';
            var lastKeyTime = Date.now();
            var scanning = false;

            document.addEventListener('keydown', function(e) {
                // Ignore modifier keys
                if (['Shift', 'Control', 'Alt', 'Meta', 'CapsLock'].includes(e.key)) {
                    return;
                }

                var currentTime = Date.now();
                
                // Detect barcode scanner input (fast typing)
                if (currentTime - lastKeyTime < 50) {
                    scanning = true;
                } else if (barcode.length > 0 && !scanning) {
                    // Regular typing, reset barcode
                    barcode = '';
                }
                
                lastKeyTime = currentTime;
                
                if (e.key === 'Enter') {
                    if (barcode.length > 0 && scanning) {
                        handleBarcode(barcode);
                    }
                    barcode = '';
                    scanning = false;
                    return;
                }
                
                // Only append if we're scanning
                if (scanning) {
                    barcode += e.key;
                }
            });

            function handleBarcode(scanned_barcode) {
                let case_number = scanned_barcode.replace(/[^\d]/g, "");
                
                if (!case_number) {
                    Swal.fire('Invalid barcode format');
                    return;
                }

                var url = '/admin/get-users-details';
                
                $.ajax({
                    url: url,
                    method: 'GET',
                    data: { case_number: case_number },
                    success: function(response) {
                        console.log(response);
                        
                        if (response.status == 404 || response.status == 500) {
                            Swal.fire(response.message);
                            $('#barcodeScanSingleTicketModal').modal('hide');
                            return;
                        }
                        
                        if (response.status == 200) {
                            var dateOfBirth = moment(response.data.date_of_birth).format("MM/DD/YYYY");
                            var userId = response.data.id;
                            var prevUserId = Number($('#prevBarcodeScanUserId').val());
                            
                            $('#prevBarcodeScanUserId').val(userId);
                            
                            // Update modal fields
                            $('#barcodeScanUserId').val(userId);
                            $('#barcodeScanFirstName').text(response.data.first_name);
                            $('#barcodeScanLastName').text(response.data.last_name);
                            $('#barcodeScanDoB').text(dateOfBirth);
                            $('#barcodeScanCaseNumber').text(response.data.case_number);
                            
                            if ($('#barcodeScanSingleTicketModal').hasClass('show')) {
                                if (userId === prevUserId) {
                                    generateTicket(userId);
                                }
                            } else {
                                $('#barcodeScanSingleTicketModal').modal('show');
                            }
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error processing barcode');
                    }
                });
            }

            function generateTicket(userId) {
                
                var submission_type = 0;
                var generatedBy = 1;
                var csrf = document.querySelector('meta[name="csrf-token"]').content;
                var url = '/manage-tickets-generation';
                
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        submission_type: submission_type,
                        generatedBy: generatedBy,
                        userId: userId,
                        _token: csrf
                    },
                    success: function(response) {
                        var redirecturl = '/one-ticket-details/' + response.ticket_id;
                        window.location.href = redirecturl;
                    },
                    error: function(xhr) {
                        Swal.fire('Error generating ticket');
                    }
                });
            }
        });

        $(document).on('click', '.verify-single-case-number', function()
        {
            var ticketNo=$(this).attr('ticket-number');
            var caseNumber=$('.case-number'+ticketNo+'').val();

            if(caseNumber == '')
            {
                $('.user-name'+ticketNo).removeClass('d-none').addClass('bg-danger text-white').text('').text('Enter Case Number In The Input');

                $('.get-single-ticket-number').attr('is-error',1);
                return;
            }

            var url = '/verify-single-case-number';

            $.ajax({
                url: url,
                method: 'GET',
                data: {
                    caseNumber: caseNumber
                },
                success: function(response) {
                    console.log(response);

                    if(response.status == 415)
                    {
                        // 
                        $('.user-name'+ticketNo).removeClass('d-none').addClass('bg-danger text-white').text('').text(response.message);

                        $('.remove-ticket'+ticketNo).removeClass('d-none');
                        $('.ticket-holder-id'+ticketNo).removeClass('selected-ticket-id').removeAttr('user-id','');
                        $('.get-single-ticket-number').attr('is-verified',1).attr('is-error',1);

                    }  else if(response.status == 450)
                    {
                        // 
                        $('.user-name'+ticketNo).removeClass('d-none').addClass('bg-danger text-white').text('').text(response.message);
                        $('.remove-ticket'+ticketNo).addClass('d-none');
                        $('.ticket-holder-id'+ticketNo).removeClass('selected-ticket-id');

                        $('.get-single-ticket-number').attr('is-error',1).attr('is-verified',1);

                    }  else if (response.status == 201)
                    {
                        $('.user-name'+ticketNo).removeClass('d-none').removeClass('bg-danger text-white').addClass('bg-success').text('');
                        $('.remove-ticket'+ticketNo).addClass('d-none');

                        var fname = response.data.first_name;
                        var lname = response.data.last_name;
                        var userId = response.data.id;

                        $('.user-name'+ticketNo).text(fname+"   "+lname);
                        $('.get-single-ticket-number').attr('user-id',userId).attr('is-error',0).attr('is-verified',1);

                    }
                } 
            });
        });

        $(document).on('click', '.get-single-ticket-number', function()
        {
            var errorId=$(this).attr('is-error');

            var userId=$(this).attr('user-id');

            var isVerifiedId=$(this).attr('is-verified');
            
            var caseNumber =$('.selected-case-number').val();

            if(errorId == 1)
            {
                $('#inputsErrorModal').modal('toggle');

                return;
            }

            if(caseNumber == '')
            {
                $('#emptyInputsErrorModal').modal('toggle');

                return;
            }

            if(isVerifiedId == 0)
            {
                $('#checkCaseNumberExistenceModal').modal('toggle');

                return;
            }

            if(isVerifiedId == 1 && caseNumber !== '' && errorId == 0 && userId)
            {

                $('#oneTicketModal').modal('toggle');
            }
        });

        $(document).on('click', '.one-ticket-modal-continue-btn', function(e)
        {
            e.preventDefault();

            $('.one-ticket-modal-continue-btn').prop("disabled", false).css("cursor", "pointer");
            
            // Show progress
            //$('#progressContainer').show();
            updateProgress(0);
            
            // Get form values
            const formData = {
                userId: $('.get-single-ticket-number').attr('user-id'),
                generatedBy:1,
                _token: document.querySelector('meta[name="csrf-token"]').content
            };
            
            // AJAX request with object data
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route("manage_tickets_generation") }}', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            
            // Progress tracking
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    updateProgress(percent);
                }
            });
            
            xhr.onload = function() {
                $('#progressContainer').hide();
                
                try {
                    const response = JSON.parse(xhr.responseText);

                    console.log(response);

                    $('#continuetoGetTicketsModal').hide();
                    
                    if (response.status === 429) {
                        Swal.fire(response.message);

                    } else if (response.status === 200) {

                        $('.redirect-text').removeClass('d-none').addClass('d-block');
                        var redirecturl = '/one-ticket-details/'+response.ticket_id;
                        window.location.href = redirecturl;
                    } else if (response.status === 500){
                        Swal.fire(response.message);
                    }
                } catch (e) {
                    Swal.fire('Error passing server response');
                }
            };
            
            xhr.onerror = function() {
                $('#progressContainer').hide();
                $('#responseMessage').text('Network error occurred').addClass('error');
            };
            
            // Send as JSON string
            xhr.send(JSON.stringify(formData));
        });
        
        function updateProgress(percent) {
            $('#progressBar').css('width', percent + '%').text(percent + '%');
        }

       

        $(document).on('click', '.close-modal', function()
        {

            $('#oneTicketModal').modal('hide');

            $('#checkCaseNumberExistenceModal').modal('hide');

            $('#emptyInputsErrorModal').modal('hide');

            $('#inputsErrorModal').modal('hide');
        });
   </script>
@endsection