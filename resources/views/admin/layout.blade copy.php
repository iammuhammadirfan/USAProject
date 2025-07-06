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

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.0/font/bootstrap-icons.css" rel="stylesheet">
	<style>
		#backToTop,#backToBottom
		{
			visibility:hidden;
			font-size: 30px;
			display: flex;
			justify-content: space-around;
			align-items: center;
			width: 40px;
			height: 50px;
			border-radius: 50%;
			background: #ffc107;
			top:calc(100% / 2);
			top:-moz-calc(100% / 2);
			top:-o-calc(100% / 2);
			right: calc(100% / 12);
			position:fixed;
			z-index: 1400;
			cursor:pointer;
			color:black;
		}

		#backToTop .svg-inline--fa,#backToBottom svg-inline--fa
		{
			color: white;
		}

        .card-img-preview
        {
            width: 120px;
            height: 120px;
            border: 2px solid black;
        }

        .preview-display-image
        {
            object-fit: cover;
            width: 100%;
            height: 100%;
        }

        #userLatestNoshowsTable tbody tr 
        {
            border:2px solid rgb(17, 15, 15); font-size:20px; font-weight:400; color:black;
        }

        #userLatestNoshowsTable tbody tr td
        {
            border:2px solid rgb(17, 15, 15); padding:5px;
        }

        #userLatestNoshowsTable tbody span
        {
            display: flex;
            align-items: center;
            justify-content: space-around;
            font-size: 20px;
            font-weight: 600;
            text-align: center;
            padding: 20px;
        }
	</style>
    @yield('admin_tool_styles')
    @yield('index_styles')
    @yield('number_control_styles')
    @yield('overview_dashboard_styles')
    @yield('register_recipient_styles')
    @yield('view_recipients_styles')
    @yield('overview_dashboard_copy_styles')
	@yield('modify_user_styles')

    @yield('view_volunteer_group_signups_styles')
    @yield('view_user_styles')
	
	@yield('error-styles')
	
</head>

<body class="bg-white " style="overflow-x:hidden">

<div class="container">
	<div id='backToBottom'><i class="fa-solid fa-arrow-down"></i></div>
	<div id='backToTop'><i class="fa-solid fa-arrow-up"></i></div>
    @yield('content')
	
	<div class="modal fade" id="updateUserDetailsModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
           <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center" id="custom-width-modalLabel">Update User Details</h4>
            </div>
            <form method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="updateUserId">
                    <div class="row mb-4 d-flex justify-content-center">
						<div class="col-6 ">
							<label for="firstName">First Name</label>
							<input type="text" autocomplete="off" class="form-control w-100" id="updateFirstName" placeholder="First Name" />
						</div>
                        <div class="col-6 ">
							<label for="lastName">Last Name</label>
							<input type="text" autocomplete="off" class="form-control w-100" id="updateLastName" placeholder="Last Name" />
						</div>
					</div>
            
					<div class="row mb-4 d-flex justify-content-center">
						<div class="col-6 ">
							<label for="recordDateOfBirth">Date of Birth (MM/DD/YYYY)</label>
							<input type="text" autocomplete="off" class="form-control w-100" id="updateDateOfBirth" name="date_of_birth" placeholder="Date of Birth (MM/DD/YYYY)" />
						</div>
                        <div class="col-6 ">
							<label for="recordCaseNumber">Case Number</label>
							<input type="text" autocomplete="off" class="form-control w-100" id="updateCaseNumber" name="case_number" placeholder="Case Number" />
						</div>
					</div>
                    <div class="row mb-4 d-flex justify-content-center">
						<div class="col-6">
                            <label for="proxyOption">Proxy</label>
                            <select id="proxyOption" class="form-control text-dark selected-proxy" style="width: 100%;">
                                <option disabled value="">Select Proxy</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
					
						<div class="col-6">
                            <label for="disableUserOption">Disable User</label>
                            <select id="disableUserOption" class="form-control text-dark selected-disable-option" style="width: 100%;">
                                <option value="0">Yes</option>
                                <option value="1">No</option>
                            </select>
                        </div>
					</div>
                    <div class="row mb-4 d-flex justify-content-center">
						<div class="col-12 ">
							<label>User Latest No Shows</label>
							<table style="width:100%; text-align:center;" id="userLatestNoshowsTable">
                                <thead>
                                    <tr>
                                        <th style="border:2px solid rgb(17, 15, 15); padding:5px;">Ticket</th>
                                        <th style="border:2px solid rgb(17, 15, 15); padding:5px;">Date</th>
                                        <th style="border:2px solid rgb(17, 15, 15); padding:5px;">Time</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
						</div>
					</div>
                    <div class="row mb-4 d-flex justify-content-center">
						<div class="col-12 ">
							<label for="updateIdCardIssuedDate">ID Card Issued Date(MM/DD/YYYY)</label>
							<input type="text" autocomplete="off" class="form-control w-100" id="updateIdCardIssuedDate" name="date_of_idcard_issue" placeholder="ID Card Issued Date (MM/DD/YYYY)" />
						</div>
					</div>
                    <div class="row mb-4 d-flex justify-content-center">
						<div class="col-12 ">
							<label for="updateIdCardFile">ID Card</label>
							<input type="file" class="form-control w-100" accept="image/png, image/jpeg" id="updateIdCardFile" name="idcard_file" placeholder="ID Card File" />
                            <div class="card-img-preview">
                                <img class="preview-display-image" src=""/>
                            </div>
						</div>
                    </div>
                <div class="modal-footer">
                    <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                    <a class="btn btn-warning waves-effect view-userdetails-btn" target="_blank" href="">Tickets History</a>
                    <button type="button" class="btn btn-warning waves-effect close-modal" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning waves-effect update-userdetails-btn">Update</button>
                </div>
            </form>   
           </div>
        </div>
    </div>

    <div class="modal fade" id="confirmUserDisableModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
           <div class="modal-content">
             <form method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" class="user-id">
                    <div class="row section-groups m-2">
                        <div class="col-sm-12">
                            <p>Are you sure you want to disable the
                            user login?</p>
                        </div>
                    </div>
                </div>
            
                <div class="modal-footer">
                   <button type="button" class="btn btn-warning waves-effect close-disablelogin-modal" data-dismiss="modal">Close</button>
                   <button type="submit" class="btn btn-danger waves-effect disable-user-login-btn">Yes</button>
                </div>
             </form> 
           </div>
        </div>
    </div>
	
	<div class="modal fade" id="confirmDeleteUserModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
           <div class="modal-content">
             <form method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" class="user-id">
                    <div class="row section-groups m-2">
                        <div class="col-sm-12">
                            <p>Are You sure you want to Delete?</p>
                        </div>
                    </div>
                </div>
            
                <div class="modal-footer">
                    <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                   <button type="button" class="btn btn-warning waves-effect close-modal" data-dismiss="modal">Close</button>
                   <button type="submit" class="btn btn-danger waves-effect delete-user-btn">Delete</button>
                </div>
             </form> 
           </div>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteReturnTimeModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-center confirm-delete-header" id="custom-width-modalLabel">Delete Details</h4>
                 </div>
                 <form method="POST" class="confirm-delete-return-id-form">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="delete_id" class="delete-id">
                        <div class="row section-groups m-2">
                            <div class="col-sm-12">
                                <p>Are You sure you want to Delete?</p>
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

    <div class="modal fade" id="manageUserLoginModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
           <div class="modal-content">
             {{-- <form method="POST" class="manage-user-login-form">
                @csrf --}}
                <div class="modal-body">
                    <div class="row section-groups m-2">
                        <div class="col-sm-12">
                            <p class="manage-user-login-text"></p>
                        </div>
                    </div>
                </div>
            
                <div class="modal-footer">
                   <button type="button" class="btn btn-warning waves-effect manage-user-login-close-modal" data-dismiss="modal">Close</button>
                   <button type="button" class="btn btn-warning waves-effect manage-user-login-btn">Continue</button>
                </div>
             {{-- </form>  --}}
           </div>
        </div>
    </div>
    

    {{-- jquery --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    {{-- bootstrap --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
    
   <script>
		window.onscroll = function () {
			if (pageYOffset >= 200) {
				document.getElementById('backToTop').style.visibility = "visible";
				document.getElementById('backToBottom').style.visibility = "hidden";
			} else {
				document.getElementById('backToTop').style.visibility = "hidden";
				document.getElementById('backToBottom').style.visibility = "visible";
			}
		};

		document.getElementById('backToTop').onclick = function()
		{
			scrollTo(document.body, 0, 0);
		}
		
		
		$("#backToBottom").click(function () {
			$("html, body").animate({
				scrollTop: $(document).height()
			}, 1000);
		});

		function scrollTo(element, to, duration) {
			var start = element.scrollTop,
				change = to - start,
				currentTime = 0,
				increment = 20;

			var animateScroll = function(){        
				currentTime += increment;
				var val = Math.easeInOutQuad(currentTime, start, change, duration);                        
				element.scrollTop = val; 
				if(currentTime < duration) {
					setTimeout(animateScroll, increment);
				}
			};
			animateScroll();
		}

		//t = current time
		//b = start value
		//c = change in value
		//d = duration
		Math.easeInOutQuad = function (t, b, c, d) {
			t /= d/2;
			if (t < 1) return c/2*t*t + b;
			t--;
			return -c/2 * (t*(t-2) - 1) + b;
		};


		$("#backToTop").click(function() {
			$("html").animate({ scrollTop: 0 }, "slow");
		});
        $(document).ready(function() {
			document.getElementById('backToBottom').style.visibility = "visible";
			
			console.log(document.body.scrollHeight);
			
            // show the realtime updated ticket
            Pusher.logToConsole = true;

            var pusherAppKey = '{{ env('PUSHER_APP_KEY') }}';

            var pusher = new Pusher(pusherAppKey, {
                cluster: 'eu'
            });

            var channel = pusher.subscribe('current-ticket');
            channel.bind('counter-updated', function(data) {

                var ticketNumber=JSON.stringify(data.count);

                    $('.ticket-served').text(ticketNumber);

                    console.log($(window).width());
                    
                    if ($(window).width() <= 430) 
                    {
                        console.log("below 430");

                        $('.ticket-served').css("font-size", "180px");

                        $('.now-serving-ticket').css("font-size", "70px");
                    }

                    if ($(window).width() > 430 && $(window).width() <= 480) 
                    {
                        console.log("below 480");

                        $('.ticket-served').css("font-size", "270px");

                        $('.now-serving-ticket').css("font-size", "70px");
                    }

                    if ($(window).width() > 480 && $(window).width() <= 768) 
                    {
                        console.log("below 768");

                        $('.ticket-served').css("font-size", "290px");

                        $('.now-serving-ticket').css("font-size", "70px");
                    }

                    if ($(window).width() > 768) 
                    {
                        console.log("above 768");

                        $('.ticket-served').css("font-size", "470px");

                        $('.now-serving-ticket').css("font-size", "70px");
                    }
                // }
            });
        });
   </script>

    @yield('number_control_script') 
    @yield('overview_dashboard_script')
    @yield('register_recipient_script')
    @yield('view_recipients_script')
	@yield('modify_user_script')
    @yield('overview_dashboard_copy_script')
    @yield('view_volunteer_group_signups_script')
    @yield('view_user_script')
    @yield('admin_tool_script')
</body>

</html>
