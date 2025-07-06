@extends('admin.layout')
@section('content')
@section('modify_user_styles')
    {{-- date picker css--}}
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">

    {{-- alertify --}}
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>
    <!-- Default theme -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/default.min.css"/>
    <!-- Semantic UI theme -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/semantic.min.css"/>
	
	{{-- datatables --}}
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.bootstrap.css"/>

{{--sweetalert--}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

    <style>
		.modal-dialog {
			max-width: 80% !important;
			margin: 30px auto;
		}


		.dt-search
		{
		  display:none;
		}
		
		.preloader-wrapper { 
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
        min-width: 128px; /* image-width of loader */
        min-height: 128px; /* image-height of loader */
        box-sizing: border-box;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        padding-top: 148px; /* 128px image-height of loader + 20px margin */
        text-align: center;
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

		.get-multiple-tickets
		{
			cursor:pointer;
		}
		/* Dropdown css code */
    .datatable-dropdown
    {
        display: flex;align-items: center;gap: 5px; width:70%; border-radius:5px;
    }
    .dropbtn {
		display: inline-block;
		color: white;
		text-align: center;
		padding: 14px 16px;
		text-decoration: none;
	}

        .dropdown:hover .dropbtn {
			background-color: red;
        }

        .dropdown {
			display: inline-block;
        }

        .dropdown-content {
			display: none;
			position: absolute;
			left: -130px;
			bottom: 40px;
			background-color: #f9f9f9;
			min-width: 160px;
			box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
			z-index: 1;
        }
		

        .dropdown-content a {
			color: black;
			padding: 12px 16px;
			text-decoration: none;
			display: block;
			text-align: left;
        }

        .dropdown-content a:hover {background-color: #f1f1f1;}

        .dropdown:hover .dropdown-content {
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
		
		@media only screen and (max-width: 992px){
			
			
			.datatable-dropdown {
				width: 100%;
			}

			.main-text-holder
			{
				font-size: 30px;
			}
	
        }
    </style>
@stop

<div class="row mt-5">
    <div class="col-md-10 mx-auto">
        <div style="display: flex; justify-content:flex-start; align-items:center;">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-warning btn-lg px-5 border border-dark py-1 mb-2" style="border: 1px solid #343a40;">Main Menu</a>
        </div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-md-6 mx-auto">
        <p style="font-size:18px; font-weight:400; text-align:center;">Search Recipient Record</p>
        <form method="POST"  class="mt-5 search-record-form">
            @csrf
            <div class="row mb-4 d-flex justify-content-center">
                <div class="col-12 ">
                    <label class="visually-hidden" for="firstName">First Name</label>
                    <input type="text" autocomplete="off" class="form-control w-100" id="userFirstName" name="first_name" placeholder="First Name" />
                </div>
            </div>
            
            <div class="row mb-4 d-flex justify-content-center">
                <div class="col-12 ">
                    <label class="visually-hidden" for="lastName">Last Name</label>
                    <input type="text" autocomplete="off" class="form-control w-100" id="userLastName" name="last_name" placeholder="Last Name" />
                </div>
            </div>
            <div class="row mb-4 d-flex justify-content-center">
                <div class="col-12 ">
                    <label class="visually-hidden" for="recordDateOfBirth">Date of Birth (MM/DD/YYYY)</label>
                    <input type="text" autocomplete="off" class="form-control w-100" id="userDateOfBirth" name="date_of_birth" placeholder="Date of Birth (MM/DD/YYYY)" />
                </div>
            </div>
            <div class="row mb-4 d-flex justify-content-center">
                <div class="col-12 ">
                    <label class="visually-hidden" for="recordCaseNumber">Case Number</label>
                    <input type="text" autocomplete="off" class="form-control w-100" id="userCaseNumber" name="case_number" placeholder="Case Number" />
                </div>
            </div>
            <span class="error-check" style="color: red;font-size: 16px;font-weight: 500;"></span>
            <div style="display: flex;justify-content:space-around; align-items:center;padding:auto; width:80%; margin:10px auto;">
				<a href="#" class="btn btn-warning btn-lg px-5 border border-dark py-1 mb-2  clear-search-btn">Clear</a>
				{{--<a href="#" class="btn btn-warning btn-lg px-5 border border-dark py-1 mb-2  search-record-btn">Search</a>--}}
			</div>
        </form>
    </div>
</div>

<div class="row d-none show-table" id="searchedUsersResultsRow">
	<div class="col-md-12 col-lg-12">
		<table id="searchedUsersResultsTable" class="table table-bordered" style="width:100%;">
			<thead class="bg-info text-white">
			<tr>
				 <td>First Name</td>
				 <td>Last Name</td>
				 <td>Date of Birth</td>
				 <td>Case Number</td>
				 <td>Proxy</td>
				 <td>Action</td>
			</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>

<!-- <div class="modal fade" id="ticketsInQueueModal" data-backdrop="static" data-keyboard="false" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <p class="main-text-holder">Congratulations! You are next in line. There’s a short wait
            time, so please don’t refresh or close this session. Your wait
            time is shown in the countdown, and once it ends, you’ll be
            automatically redirected to get your ticket!</p>
            <p id="countDownToTickets" style="font-size: 50px;
    text-align: center;"></p>
        </div>
    </div> -->

<div class="modal fade" id="barcodeScanSingleTicketModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-title" style="text-align: center;">
				<h4>ID Scan</h4> 
			</div>
			<div class="modal-body">
				<input id="barcodeScanUserId" type="hidden">
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
                            <td style="border:2px solid rgb(17, 15, 15); padding:5px;" id="barcodeScanCaseNumber"></td>
							<td style="border:2px solid rgb(17, 15, 15); padding:5px;" id="barcodeScanDoB"></td>
                        </tr>
                    </table>
			</div>
		</div>
	</div>
</div>

@endsection

@section('modify_user_script')
	{{-- alertify --}}
   <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>
   
    {{--sweetalert--}}
	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

   {{-- select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- timepicker --}}
    <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
   
    {{-- jquery ui plugins --}}
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
	
	{{-- datatable --}}
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script> 
   <script type="text/javascript" src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.js"></script>
   <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.js"></script>
   
   {{-- moment --}}
   <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js
"></script> 
   
   <script>

        $(document).ready(function() {
            // datepicker for selecting the date of birth
            $( function() {
                $( "#userDateOfBirth,#updateDateOfBirth,#updateIdCardIssuedDate" ).datepicker(
                    {
                        dateFormat: 'mm/dd/yy'
                    }
                );
            });
			
			$('.admin-select2').select2();
			
			$('#searchedUsersResultsRow').removeClass('show-table');

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

                let case_number = scanned_barcode.replace(/[^\d]/g, "");

                // get user details based on case number
                var url = '/admin/find-user-details';

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

							$('#userFirstName,#userLastName,#userCaseNumber,#userDateOfBirth').val('');

							$('#searchedUsersResultsTable').DataTable().destroy();
				
							console.log($('.show-table').length);
							
							$('#searchedUsersResultsRow').addClass('d-none');

                        }
                        else if (response.status == 200)
                        {
							var userId = response.data.id;
                            var fname = response.data.first_name;
                            var lname = response.data.last_name;
                            var case_number = response.data.case_number;
							var dateOfBirth=moment(response.data.date_of_birth).format("MM/DD/YYYY");

                            if ($('#barcodeScanSingleTicketModal').hasClass('show') && Number($('#barcodeScanUserId').val()) == userId) 
							{
								$('#userFirstName').val(fname)
								$('#userLastName').val(lname)
								$('#userCaseNumber').val(case_number)
								$('#userDateOfBirth').val(dateOfBirth)

								search_user()

								$('#barcodeScanSingleTicketModal').modal('hide');

							} else if($('#barcodeScanSingleTicketModal').hasClass('show') && Number($('#barcodeScanUserId').val()) !== userId) 
							{
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
		
		
		function search_user()
		{
			const fname=$('#userFirstName').val();
			const lname=$('#userLastName').val();
			const date_of_birth=$('#userDateOfBirth').val();
			const case_number=$('#userCaseNumber').val();
			const page_reload_checker=1;
			
			$('.search-record-btn').prop("disabled", true).css("cursor", "not-allowed");
		
			const url = '/admin/modify-users';

			$.ajax({
				url: url,
                type:'get',
                data:{
                    fname: fname,
					lname: lname,
					date_of_birth: date_of_birth,
					case_number: case_number,
					page_reload_checker: page_reload_checker
                },
				success: function(response) 
				{
					console.log(response);
					$('.error-check').text('');
					
					$('.search-record-btn').prop("disabled", false).css("cursor", "pointer");
					
					if(response.status == 500)
					{
						//$('.error-check').text(response.message);
						$('#searchedUsersResultsTable').DataTable().destroy();
						
						$('#searchedUsersResultsRow').addClass('d-none');
					} else {
			
						//$('#searchedUsersResultsTable').ajax.reload(null, false);
						$('#searchedUsersResultsTable').DataTable().destroy();
						$('#searchedUsersResultsTable').removeClass('table-striped');
						
						const fname=$('#userFirstName').val();
						const lname=$('#userLastName').val();
						const date_of_birth=$('#userDateOfBirth').val();
						const case_number=$('#userCaseNumber').val();
						const page_reload_checker=1;
						
						$('#searchedUsersResultsRow').removeClass('d-none');
						
						$('#searchedUsersResultsTable').DataTable({
							stripeClasses: [],
							columnDefs: [{
								//"defaultContent": "-",
								// "targets": "_all",
								// "className": "text-center"
								}],
							"iDisplayLength":25,
							"sDom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
							processing:true,
							serverside:true,
							responsive:true,
							"ajax": {
								"url": "/admin/modify-users",
								"type": "get",
								"data": {
									"fname": fname,
									"lname": lname,
									"date_of_birth": date_of_birth,
									"case_number": case_number,
									"page_reload_checker": page_reload_checker,
								},
							},
							columns: [
								{  data: 'first_name',name:'first_name',orderable:true,searchable:true},
								{  data: 'last_name',name:'last_name',orderable:false,searchable:false },
								{  data: 'date_of_birth',name:'date_of_birth',orderable:false,searchable:false },
								{  data: 'case_number',name:'case_number',orderable:true,searchable:true },
								{  data: 'proxy',name:'proxy',orderable:true,searchable:true},
								{  data: 'action',name:'action',orderable:true,searchable:true },
							],
						});
					}
					
				}
			});
		}

		$(document).on('click', '.print-id-card', function(e) 
		{
			e.preventDefault();

			var userId=$(this).attr('user-id');

			var redirecturl = '/admin/print-id-card-pdf/'+userId;

			window.open(redirecturl, '_blank');
		});
            
			$(document).on('click', '.clear-search-btn', function(e) 
			{
				e.preventDefault();
				
				$('#userFirstName,#userLastName,#userDateOfBirth,#userCaseNumber').val('');
				
				$('#searchedUsersResultsTable').DataTable().destroy();
				
				console.log($('.show-table').length);
				
				$('#searchedUsersResultsRow').addClass('d-none');
			});
			
			
		$(document).on('keyup', '#userFirstName,#userLastName,#userCaseNumber', function(e)
        {
            e.preventDefault();
			
			search_user();
        });

		$(document).on('change', '#userDateOfBirth', function(e)
        {
			e.preventDefault();
			
			search_user();
		});
		
		$(document).on('click', '.get-single-ticket', function(e)
        {
			e.preventDefault();

            $(this).prop("disabled", true).css("cursor", "not-allowed");

            var userId=$(this).attr('user-id');
			
			var generatedBy = 1;

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            var url = '/manage-tickets-generation';
			
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    userId: userId,
					generatedBy:generatedBy,
                    _token: csrf
                },
                success: function(response) {

                    console.log(response);

                    $('.get-single-ticket').prop("disabled", false).css("cursor", "pointer");
					
					if(response.status == 500)
					{
						swal(response.message);
						
					} 

					if(response.status == 505)
					{
						swal(response.message);
						
					} 

					if(response.status == 200)
					{
						// var redirecturl = '/one-ticket-details/'+response.ticket_id;

						// window.open(redirecturl);
					}
                }
				// ,error: function(xhr, status, error) {
                //     console.log(xhr);

                //     if (xhr.responseJSON.status == 429) 
                //     {

                //         $('#ticketsInQueueModal').modal('toggle');

                //         let secondsLeft = xhr.responseJSON.seconds_remaining;

                //         const interval = setInterval(() => {
                //             secondsLeft--;

                //             if (secondsLeft <= 0) {
                //                 clearInterval(interval);
                //                 $('#ticketsInQueueModal').modal('hide');
                //             } else {
                //                 $('#countDownToTickets').html('').text(secondsLeft+' Seconds');
                //             }
                //         }, 1000);
				// 	}
                // }
            });

        });
		
		//check if a user has a ticket
		$(document).on('click', '.get-multiple-tickets', function()
        {
			$(this).prop("disabled", true).css("cursor", "not-allowed");

            var userId=$(this).attr('user-id');

            var url = '/admin/check-user-ticket';

            $.ajax({
                url: url,
                method: 'get',
                data: {
                    userId: userId,
                },
                success: function(response) {

                    console.log(response);

                    $('.get-multiple-tickets').prop("disabled", false).css("cursor", "pointer");
					
					if(response.status == 500)
					{
						swal(response.message);
						
					} else
					{
						var redirecturl = '/admin/multiple-tickets-details/'+userId;
						window.location.href = redirecturl;
					}
                }
            });
		});
		
		$(document).on('click', '.update-user-details', function(e)
        {
			e.preventDefault();
			
            var userId=$(this).attr('user-id');

            var url = '/admin/get-user/'+userId;

			var view_url = '/admin/view-user/'+userId;
			
			$('#updateUserDetailsModal').modal('toggle');

			$('.view-userdetails-btn').attr('href', '').attr('href', view_url);

			$('.update-userdetails-btn').prop("disabled", false).css("cursor", "pointer");

			$('#updateIdCardFile,#updateUserId,#updateFirstName,#updateLastName,#updateDateOfBirth,#updateCaseNumber,.selected-proxy,.selected-disable-option,#updateIdCardIssuedDate').val('');

            $.ajax({
                url: url,
                method: 'get',
                success: function(response) {

                    console.log(response);
					var dateOfBirth=moment(response.date_of_birth,"YYYY-MM-DD").format("MM/DD/YYYY");

					if(response.idcard_issued_date == null)
					{
						var IdCardIssuanceDate='';
					} else {
						var IdCardIssuanceDate=moment(response.idcard_issued_date,"YYYY-MM-DD").format("MM/DD/YYYY");
					}
					
					$('#userLatestNoshowsTable tbody').empty();

					var ticketDetails=response.user_tickets;

					console.log(ticketDetails);

					if(ticketDetails.length > 0)
					{

						$.each(ticketDetails, function(key, value) {

							$('#userLatestNoshowsTable tbody').append('<tr><td>'+ticketDetails[key].ticket_number+'</td><td>'+moment(ticketDetails[key].created_at).format('MM/DD/YYYY')+'</td><td>'+moment(ticketDetails[key].created_at).format('HH:mm:ss')+'</td></tr>');
						});
					} else {
						$('#userLatestNoshowsTable tbody').html('<span>No shows unavailable</span>');
					}

					if(response.id_card == '')
					{
						$('.preview-display-image').attr('src','/images/no_id_card_available.png')
					} else {
						$('.preview-display-image').attr('src','/images/id_cards/'+response.id_card)
					}
					$('#updateIdCardFile').val('');
					$('#updateUserId').val(response.id);
					$('#updateFirstName').val(response.first_name);
					$('#updateLastName').val(response.last_name);
					$('#updateDateOfBirth').val(dateOfBirth);
					$('#updateCaseNumber').val(response.case_number);
					$('.selected-proxy').val(response.proxy);
					$('.selected-disable-option').val(response.is_active);
					$('#updateIdCardIssuedDate').val(IdCardIssuanceDate);
                }
            });
        });

		$(document).on('change', '#updateIdCardFile', function(e)
        {
			e.preventDefault();

			var output = document.querySelector('.preview-display-image');

			console.log(output);
			output.src = URL.createObjectURL(event.target.files[0]);
			output.onload = function() {
				URL.revokeObjectURL(output.src) // free memory
			}
		});

		// var previewimage = function(event) {
		// 	$('.display-img-preview').removeClass('d-none');

		// 	var output = document.querySelector('.preview-display-image');

		// 	console.log(output);
		// 	output.src = URL.createObjectURL(event.target.files[0]);
		// 	output.onload = function() {
		// 		URL.revokeObjectURL(output.src) // free memory
		// 	}
		// };
		
		$(document).on('click', '.update-userdetails-btn', function(e)
        {
			e.preventDefault();
            
            var user_id=$('#updateUserId').val();
			var fname=$('#updateFirstName').val();
			var lname=$('#updateLastName').val();
			var dob=$('#updateDateOfBirth').val();
			var idcard_issued_date=$('#updateIdCardIssuedDate').val();
			var idcard=$('#updateIdCardFile').prop("files")[0];

			console.log(idcard);

            if(idcard == undefined)
            {
                var id_card='';
            } else {
                var id_card=idcard;
            }
			var disabled_user=$('.selected-disable-option').val();
			var case_number=$('#updateCaseNumber').val();
			var proxy=$('.selected-proxy').val();
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            var url = '/admin/update-user';

			if(disabled_user == '0')
			{
				$('#confirmUserDisableModal').modal('toggle')

				$(this).prop("disabled", true).css("cursor", "not-allowed");
			} else {
				$(this).prop("disabled", true).css("cursor", "not-allowed");
				$('#confirmUserDisableModal').modal('hide')
				$.ajax({
					url: url,
					method: 'POST',
					contentType:false,
					processData: false,
					data: function(){
						var data = new FormData();
						data.append('_token' , csrf);
						data.append('user_id' , user_id);
						data.append('proxy' , proxy);
						data.append('disabled_user' , disabled_user);
						data.append('id_card' , id_card);
						data.append('idcard_issued_date' , idcard_issued_date);
						data.append('case_number' , case_number);
						data.append('dob' , dob);
						data.append('lname' , lname);
						data.append('fname' , fname);
						data.append('user_id' , user_id);
						data.append('id_card' , id_card);
						
						return data;
						console.log(data)
					}(),

					success: function(response) {

						console.log(response);

						$('.update-userdetails-btn').prop("disabled", false).css("cursor", "pointer");
						
						if(response.status == 500)
						{
							alertify.set('notifier', 'position', 'top-center');
							alertify.success(response.msg);
						} else if (response.status == 200)
						{
							alertify.set('notifier', 'position', 'top-center');
							alertify.success(response.msg);
							
							$('#updateUserDetailsModal').modal('hide');
							
							$('#searchedUsersResultsTable').DataTable().destroy();
							$('#searchedUsersResultsTable').removeClass('table-striped');
							
							const fname=$('#userFirstName').val();
							const lname=$('#userLastName').val();
							const date_of_birth=$('#userDateOfBirth').val();
							const case_number=$('#userCaseNumber').val();
							const page_reload_checker=1;
							
							$('#searchedUsersResultsRow').removeClass('d-none');
							
							$('#searchedUsersResultsTable').DataTable({
								stripeClasses: [],
								columnDefs: [{
									//"defaultContent": "-",
									// "targets": "_all",
									// "className": "text-center"
									}],
								"iDisplayLength":25,
								"sDom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
								processing:true,
								serverside:true,
								responsive:true,
								"ajax": {
									"url": "/admin/modify-users",
									"type": "get",
									"data": {
										"fname": fname,
										"lname": lname,
										"date_of_birth": date_of_birth,
										"case_number": case_number,
										"page_reload_checker": page_reload_checker,
									},
								},
								columns: [
									{  data: 'first_name',name:'first_name',orderable:true,searchable:true},
									{  data: 'last_name',name:'last_name',orderable:false,searchable:false },
									{  data: 'date_of_birth',name:'date_of_birth',orderable:false,searchable:false },
									{  data: 'case_number',name:'case_number',orderable:true,searchable:true },
									{  data: 'proxy',name:'proxy',orderable:true,searchable:true},
									{  data: 'action',name:'action',orderable:true,searchable:true },
								],
							});
						} else if (response.status == 405)
						{
							$('.error-list').html(" ").removeClass('d-none');
							$.each(response.message, function(key, err_value) {
								$('.error-list').append('<li>' + err_value + '</li>');
							})
						}
					}
				});
			}
        });

		$(document).on('click', '.disable-user-login-btn', function(e)
        {
			e.preventDefault();
			
			var user_id=$('#updateUserId').val();
			var fname=$('#updateFirstName').val();
			var lname=$('#updateLastName').val();
			var dob=$('#updateDateOfBirth').val();
			var idcard_issued_date=$('#updateIdCardIssuedDate').val();
			var idcard=$('#updateIdCardFile').prop("files")[0];

            if(idcard == undefined)
            {
                var id_card='';
            } else {
                var id_card=idcard;
            }
			var disabled_user=$('.selected-disable-option').val();
			var case_number=$('#updateCaseNumber').val();
			var proxy=$('.selected-proxy').val();
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            var url = '/admin/update-user';

			$.ajax({
				url: url,
				method: 'POST',
				contentType:false,
				processData: false,
				data: function(){
					var data = new FormData();
					data.append('_token' , csrf);
					data.append('user_id' , user_id);
					data.append('proxy' , proxy);
					data.append('disabled_user' , disabled_user);
					data.append('id_card' , id_card);
					data.append('idcard_issued_date' , idcard_issued_date);
					data.append('case_number' , case_number);
					data.append('dob' , dob);
					data.append('lname' , lname);
					data.append('fname' , fname);
					data.append('user_id' , user_id);
					data.append('id_card' , id_card);
					
					return data;
					console.log(data)
				}(),
				success: function(response) {

					console.log(response);

					$('.update-userdetails-btn').prop("disabled", false).css("cursor", "pointer");

					$('#confirmUserDisableModal').modal('hide');
					
					if(response.status == 500)
					{
						alertify.set('notifier', 'position', 'top-center');
						alertify.success(response.msg);
					} else if (response.status == 200)
					{
						alertify.set('notifier', 'position', 'top-center');
						alertify.success(response.msg);
						
						$('#updateUserDetailsModal').modal('hide');
						
						$('#searchedUsersResultsTable').DataTable().destroy();
						$('#searchedUsersResultsTable').removeClass('table-striped');
						
						const fname=$('#userFirstName').val();
						const lname=$('#userLastName').val();
						const date_of_birth=$('#userDateOfBirth').val();
						const case_number=$('#userCaseNumber').val();
						const page_reload_checker=1;
						
						$('#searchedUsersResultsRow').removeClass('d-none');
						
						$('#searchedUsersResultsTable').DataTable({
							stripeClasses: [],
							columnDefs: [{
								//"defaultContent": "-",
								// "targets": "_all",
								// "className": "text-center"
								}],
							"iDisplayLength":25,
							"sDom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
							processing:true,
							serverside:true,
							responsive:true,
							"ajax": {
								"url": "/admin/modify-users",
								"type": "get",
								"data": {
									"fname": fname,
									"lname": lname,
									"date_of_birth": date_of_birth,
									"case_number": case_number,
									"page_reload_checker": page_reload_checker,
								},
							},
							columns: [
								{  data: 'first_name',name:'first_name',orderable:true,searchable:true},
								{  data: 'last_name',name:'last_name',orderable:false,searchable:false },
								{  data: 'date_of_birth',name:'date_of_birth',orderable:false,searchable:false },
								{  data: 'case_number',name:'case_number',orderable:true,searchable:true },
								{  data: 'proxy',name:'proxy',orderable:true,searchable:true},
								{  data: 'action',name:'action',orderable:true,searchable:true },
							],
						});
					} else if (response.status == 405)
					{
						$('.error-list').html(" ").removeClass('d-none');
						$.each(response.message, function(key, err_value) {
							$('.error-list').append('<li>' + err_value + '</li>');
						})
					}
				}
			});
        });
		
		$(document).on('click', '.delete-user', function(e)
        {
			e.preventDefault();
			
            var userId=$(this).attr('user-id');
			
			$('#confirmDeleteUserModal').modal('toggle');

            $('.user-id').val(userId);
        });
		
		$(document).on('click', '.delete-user-btn', function(e)
        {
			e.preventDefault();
            $(this).prop("disabled", true).css("cursor", "not-allowed");

            var user_id=$('.user-id').val();
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            var url = '/admin/delete-user';
			
            //$('#savingPopUpModal').modal('toggle');

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    user_id: user_id,
                    _token: csrf
                },
                success: function(response) {

                    console.log(response);

                    $('.delete-user-btn').prop("disabled", false).css("cursor", "pointer");
					
					//$('#savingPopUpModal').modal('hide');
					if(response.status == 404)
					{
						swal(response.message);
						
					} else if (response.status == 200)
					{
						swal(response.msg);
						
						$('#confirmDeleteUserModal').modal('hide');
						
						$('#searchedUsersResultsTable').DataTable().destroy();
						$('#searchedUsersResultsTable').removeClass('table-striped');
						
						const fname=$('#userFirstName').val();
						const lname=$('#userLastName').val();
						const date_of_birth=$('#userDateOfBirth').val();
						const case_number=$('#userCaseNumber').val();
						const page_reload_checker=1;
						
						$('#searchedUsersResultsRow').removeClass('d-none');
						
						$('#searchedUsersResultsTable').DataTable({
							stripeClasses: [],
							columnDefs: [{
								//"defaultContent": "-",
								// "targets": "_all",
								// "className": "text-center"
								}],
							"iDisplayLength":25,
							"sDom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
							processing:true,
							serverside:true,
							responsive:true,
							"ajax": {
								"url": "/admin/modify-users",
								"type": "get",
								"data": {
									"fname": fname,
									"lname": lname,
									"date_of_birth": date_of_birth,
									"case_number": case_number,
									"page_reload_checker": page_reload_checker,
								},
							},
							columns: [
								{  data: 'first_name',name:'first_name',orderable:true,searchable:true},
								{  data: 'last_name',name:'last_name',orderable:false,searchable:false },
								{  data: 'date_of_birth',name:'date_of_birth',orderable:false,searchable:false },
								{  data: 'case_number',name:'case_number',orderable:true,searchable:true },
								{  data: 'proxy',name:'proxy',orderable:true,searchable:true},
								{  data: 'action',name:'action',orderable:true,searchable:true },
							],
						});
					} else if (response.status == 405)
					{
						$('.error-list').html(" ").removeClass('d-none');
						$.each(response.message, function(key, err_value) {
							$('.error-list').append('<li>' + err_value + '</li>');
						})
					}
                }
            });

        });
		
		$(document).on('click', '.close-modal', function(e) {
            e.preventDefault();

            $('.error-list').html(" ").addClass('d-none');
            
            $('#updateUserDetailsModal').modal('hide');
			
			$('.update-userdetails-btn').prop("disabled", false).css("cursor", "pointer");
			
			$('#confirmDeleteUserModal').modal('hide');
			
			$('.delete-user-btn').prop("disabled", false).css("cursor", "pointer");
        });

		$('#confirmUserDisableModal').on('hidden.bs.modal', function (e) {
			$('#confirmUserDisableModal').modal('hide');
			
			$('.update-userdetails-btn').prop("disabled", false).css("cursor", "pointer");
		})

		$(document).on('click', '.close-disablelogin-modal', function(e) {
            e.preventDefault();

            $('#confirmUserDisableModal').modal('hide');
			
			$('.update-userdetails-btn').prop("disabled", false).css("cursor", "pointer");
        });
			
   </script>
@endsection