<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GoGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons CSS -->

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<!-- CSS only -->
{{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.0/font/bootstrap-icons.css" rel="stylesheet">

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
    /* .preloader-wrapper { 
        width: 100%; height: 100%; 
        position: fixed; 
        top: 0;
        left: 0;
        z-index: 10;
        background: antiquewhite;
    }

    .preloader-wrapper > .preloader { 
        background: transparent url(https://graphiclineweb.files.wordpress.com/2013/10/ajaxloader.gif?w=604) no-repeat center top;
        position: absolute;
        min-width: 128px; /* 
        min-height: 128px; /*
        box-sizing: border-box;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        padding-top: 148px; /* 128px image-height of loader + 20px margin 
        text-align: center;
    } */
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

    #ticketsInQueueModal
    {
        background-color:#ffc107;
    }

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
</head>

@vite(['resources/sass/app.scss', 'resources/js/bootstrap.js'])

<body class="bg-white " style="overflow-x:hidden">
    <div class="container" style="font-size: 16px; color:black; font-weight:500;">
        <h2 class="text-center m-5">Get Single Ticket</h2>
        <div class="row mt-5 ">
            <div class="col-md-12">
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
        </div>
    
        <div class="row mt-5">
            <div class="col-md-12">
                <div style="display: flex; justify-content:space-around; align-items:space-between;">
                    <a class="get-single-ticket-number btn btn-warning btn-lg px-5 border border-dark py-1 mb-2" is-verified="0" style="border: 1px solid #343a40; text-decoration: none;">Get Ticket Number</a>
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
        <span style="color:red;" class="redirect-text d-none">Please Wait As you Are being redirected to the Ticket Details</span>
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
           <div style="display: flex;
    justify-content: space-around;
    align-items: center;
    margin: 5px;">
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
           <div style="display: flex;
    justify-content: space-around;
    align-items: center;
    margin: 5px;">
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
           <div style="display: flex;
    justify-content: space-around;
    align-items: center;
    margin: 5px;">
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
           <div style="display: flex;
    justify-content: space-around;
    align-items: center;
    margin: 5px;">
            <a class="btn btn-warning waves-effect border border-dark close-modal">Cancel</a>
            <a class="btn btn-warning waves-effect border border-dark continue-to-get-tickets-btn">Continue</a>
         </div>
           </div>
        </div>
    </div>

    <!-- <div class="modal fade" id="savingPopUpModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"> -->
        <!-- <div class="modal-dialog modal-lg">
            <div class="preloader-wrapper">
                <div class="preloader">
                    <p style="
                    font-size: 25px;
                    font-weight: 500;
                ">{{ Str::limit(strip_tags($savingPopUpData->message)) }}</p>
                </div>
            </div>
        </div>
    </div> -->

    <!-- <div class="modal fade" id="ticketsInQueueModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <p class="main-text-holder">Congratulations! You are next in line. There’s a short wait
            time, so please don’t refresh or close this session. Your wait
            time is shown in the countdown, and once it ends, you’ll be
            automatically redirected to get your ticket!</p>
            <p id="countDownToTickets" style="font-size: 50px;
    text-align: center;"></p>
        </div>
    </div> -->
 
    {{-- jquery --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    {{-- bootstrap --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
    

    {{-- datatable --}}
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script> 
   <script type="text/javascript" src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>
   <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>

   {{-- select2 --}}
   <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

   {{-- alertify --}}
   <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>
   
    {{-- jquery ui plugins --}}
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script> 

    {{-- moment --}}
   <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js
"></script> 

   <script>
		
		$(document).ready(function() {

            new bootstrap.Modal(document.getElementById('ticketsInQueueModal'), {
                backdrop: 'static',
                keyboard: false
            });
			
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
            var interval;

            document.addEventListener('keydown',function(e)
            {

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

                interval = setInterval(()=> barcode = '',10)
            });

            function handleBarcode(scanned_barcode)
            {
                let case_number = scanned_barcode.replace(/[^\d]/g, "");

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
                            alertify.set('notifier', 'position', 'top-center');
                            alertify.success(response.message);

                            $('#barcodeScanSingleTicketModal').modal('hide');
                        } 
                        else if (response.status == 500)
                        {
                            alertify.set('notifier', 'position', 'top-center');
                            alertify.success(response.message);

                            $('#barcodeScanSingleTicketModal').modal('hide');
                        } 
                        else if (response.status == 200)
                        {
                            var dateOfBirth=moment(response.data.date_of_birth).format("MM/DD/YYYY");
                            var userId = response.data.id;
                            var prevUserId=Number($('#prevBarcodeScanUserId').val());

                            // alert(prevUserId+','+userId);

                            $('#prevBarcodeScanUserId').val(userId);

                            if ($('#barcodeScanSingleTicketModal').hasClass('show') && userId == prevUserId) 
                            {
                                $('#savingPopUpModal').modal('toggle');

                                var userId = $('#barcodeScanUserId').val();
			
                                var generatedBy = 1;

                                var csrf = document.querySelector('meta[name="csrf-token"]').content;

                                var url = '/manage-tickets-generation';

                                $.ajax({
                                    url: url,
                                    method: 'POST',
                                    data: {
                                        generatedBy:generatedBy,
                                        userId: userId,
                                        _token: csrf
                                    },
                                    success: function(response) {

                                        console.log(response);

                                        var redirecturl = '/one-ticket-details/'+response.ticket_id;
                                        window.location.href = redirecturl;
                                    }
                                });
                            } else if ($('#barcodeScanSingleTicketModal').hasClass('show') && userId !== prevUserId) 
                            {
                                // $('#barcodeScanSingleTicketModal').modal('toggle');

                                $('#barcodeScanFirstName,#barcodeScanLastName,#barcodeScanDoB,#barcodeScanCaseNumber,#barcodeScanUserId').text('');
                                $('#barcodeScanUserId').val(response.data.id);
                                $('#barcodeScanFirstName').text(response.data.first_name);
                                $('#barcodeScanLastName').text(response.data.last_name);
                                $('#barcodeScanDoB').text(dateOfBirth);
                                $('#barcodeScanCaseNumber').text(response.data.case_number);
                            } else {
                                $('#barcodeScanSingleTicketModal').modal('toggle');

                                $('#barcodeScanFirstName,#barcodeScanLastName,#barcodeScanDoB,#barcodeScanCaseNumber,#barcodeScanUserId').text('');
                                $('#barcodeScanUserId').val(response.data.id);
                                $('#barcodeScanFirstName').text(response.data.first_name);
                                $('#barcodeScanLastName').text(response.data.last_name);
                                $('#barcodeScanDoB').text(dateOfBirth);
                                $('#barcodeScanCaseNumber').text(response.data.case_number);
                            }
                        }
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

            // console.log(emptyInputsObject.length,
            // errorInputsObject.length,
            // Number(numberOfTickets),
            // checkedTicketsObject.length)

            // // check if user has selected another option
            // // if(userIdsObject.length == 1)
            // // {
            // //     $('#selectNumberofTicketsModal').modal('toggle');
            // // }
            // if(emptyInputsObject.length == 0 && errorInputsObject.length == 0 && numberOfTickets==''  && checkedTicketsObject.length==1)
            // {
            //     $('#selectNumberofTicketsModal').modal('toggle');

            //     return;
            // }

            // if(emptyInputsObject.length > 0 && errorInputsObject.length == 0 && checkedTicketsObject.length)
            // {
            //     $('#emptyInputsErrorModal').modal('toggle');

            //     return;
            // }

            // if(errorInputsObject.length > 0 && emptyInputsObject.length == 0 || emptyInputsObject.length > 0 && errorInputsObject.length > 0 && numberOfTickets !=='')
            // {
            //     $('#inputsErrorModal').modal('toggle');

            //     return;
            // }

            // if(emptyInputsObject.length == 0 && errorInputsObject.length == 0  && checkedTicketsObject.length !== Number(numberOfTickets))
            // {
            //     $('#checkCaseNumberExistenceModal').modal('toggle');

            //     return;
            // }

            // if(emptyInputsObject.length == 0 && errorInputsObject.length == 0 && checkedTicketsObject.length == Number(numberOfTickets))
            // {
            //     $('.tickets-count-number').text(checkedTicketsObject.length);

            //     $('#continuetoGetTicketsModal').modal('toggle');
            // }
        });

        $(document).on('click', '.one-ticket-modal-continue-btn', function(e)
        {
            e.preventDefault();

            $('.one-ticket-modal-continue-btn').prop("disabled", false).css("cursor", "pointer");
            
            // Show progress
            $('#progressContainer').show();
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
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.message);

                    } else if (response.status === 200) {

                        $('.redirect-text').addClass('d-block').removeClass('d-none');
                        var redirecturl = '/one-ticket-details/'+response.ticket_id;
                        window.location.href = redirecturl;
                    } else if (response.status === 500){
                        alertify.set('notifier', 'position', 'top-center');
                        alertify.success(response.message);
                    }
                } catch (e) {
                    alertify.set('notifier', 'position', 'top-center');
                    alertify.success('Error parsing server response');
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

        // $(document).on('click', '.one-ticket-modal-continue-btn', function()
        // {
        //     var userId = $('.get-single-ticket-number').attr('user-id');
			
		// 	var generatedBy = 1;

        //     var csrf = document.querySelector('meta[name="csrf-token"]').content;

        //     var url = '/manage-tickets-generation';

        //     $('#savingPopUpModal').modal('toggle');

        //     $.ajax({
        //         url: url,
        //         method: 'POST',
        //         data: {
		// 			generatedBy:generatedBy,
        //             userId: userId,
        //             _token: csrf
        //         },
        //         success: function(response) {

        //             console.log(response);

        //             var redirecturl = '/one-ticket-details/'+response.ticket_id;
        //             window.location.href = redirecturl;
        //         }
        //         // ,error: function(xhr, status, error) {
        //         //     console.log(xhr);

        //             // $('#savingPopUpModal').hide();

        //             // // $('#continuetoGetTicketsModal').hide();

        //             // if (xhr.responseJSON.status == 429) 
        //             // {

        //             //     $('#ticketsInQueueModal').modal('toggle');

        //             //     let secondsLeft = xhr.responseJSON.seconds_remaining;

        //             //     const interval = setInterval(() => {
        //             //         secondsLeft--;

        //             //         if (secondsLeft <= 0) {
        //             //             clearInterval(interval);
        //             //             $('#ticketsInQueueModal').modal('hide');
        //             //         } else {
        //             //             $('#countDownToTickets').html('').text(secondsLeft+' Seconds');
        //             //         }
        //             //     }, 1000);

        //             //     $('#ticketsInQueueModal').modal('hide');
		// 			// }

        //             // $('#savingPopUpModal').modal('hide');

        //             // setTimeout(function () {
        //             //     if ($('#savingPopUpModal').is(':visible')) {
        //             //         $('#savingPopUpModal').modal('hide');
        //             //     }
        //             // }, 1000)

        //             // $('#savingPopUpModal').on('hidden.bs.modal', function () {

        //             //     if (xhr.responseJSON && xhr.responseJSON.status == 429) {
        //             //         // Show ticketsInQueueModal with static backdrop

        //             //         $('#ticketsInQueueModal').modal({
        //             //             backdrop: 'static',
        //             //             keyboard: false
        //             //         });

        //             //         $('#ticketsInQueueModal').modal('show');

        //             //         // Start countdown
        //             //         let secondsLeft = xhr.responseJSON.seconds_remaining;
        //             //         const interval = setInterval(() => {
        //             //             secondsLeft--;

        //             //             if (secondsLeft <= 0) {
        //             //                 clearInterval(interval);
        //             //                 $('#ticketsInQueueModal').modal('hide');
        //             //             } else {
        //             //                 $('#countDownToTickets').text(secondsLeft + ' Seconds');
        //             //             }
        //             //         }, 1000);
        //             //     }
        //             // });
        //         // }
        //     });

        // });

        // $(document).on('click', '.multiple-ticket-btn', function()
        // {
        //     $('#multipleTicketModal').modal('toggle');
        // });

        $(document).on('click', '.close-modal', function()
        {

            $('#oneTicketModal').modal('hide');

            $('#checkCaseNumberExistenceModal').modal('hide');

            $('#emptyInputsErrorModal').modal('hide');

            $('#inputsErrorModal').modal('hide');
        });
   </script>
</body>

</html>