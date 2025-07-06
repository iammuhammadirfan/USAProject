<!DOCTYPE html>
                    <html lang="en">

                    <head>
                        <meta charset="UTF-8">
                        <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
                        <meta name="csrf-token" content="{{ csrf_token() }}">
                        <title>GoGo</title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

                        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
                        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.0/font/bootstrap-icons.css" rel="stylesheet">

                        <!-- sweetalert -->
                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

                    <style>
                        body {
                            background-color: #f8f9fa; /* Light Gray */
                        }

                        .nhcc-logo {
                            max-width: 100%;
                            height: auto;
                        }

                        .form-control {
                            border-radius: 20px; /* Rounded corners */
                            width: 100%; /* Full width on smaller screens */
                        }

                        .btn-login {
                            border-radius: 20px; /* Rounded corners */
                        }

                        .serving-number {
                        color:red; font-size: 32em;
                        }

                    .input-group {
                        position: relative;
                    }

                    .input-group input[type="password"] {
                        border-radius: 20px 0 0 20px; /* Rounded corners */
                    }

                        .input-group button {
                            border-radius: 0 20px 20px 0; /* Rounded corners */
                            position: absolute;
                            top: 0;
                            bottom: 0;
                            right: 0;
                            z-index: 1;
                        }

                        .login-form 
                        {
                            display: flex;
                            justify-content: space-around;
                            align-items: center;
                        }

                        #loginForm 
                        {
                            display: block;
                            border: 3px solid #ccc;
                            padding: 20px;
                            border-radius: 10px;
                            margin: 0 auto;
                            width: 500px;
                            padding:10px auto;
                        }

                        .form-container .form-control {
                            width: 100%;  
                            height: 50px;  
                            font-size: 16px; 
                        }

                        .form-container {
                            border: 3px solid #ccc;
                            padding: 20px;
                            border-radius: 10px;
                            margin: 0 auto;
                            width: 500px;
                        }

                        .input-group {
                            width: 100%; /* Full width */
                            height: 50px; /* Set height as needed */
                            font-size: 16px;
                            border-radius: 20px; /* Rounded corners */
                        }

                        .login-form p 
                        {
                            font-size: 22px;
                            font-weight: 600;
                            text-align: center;
                            margin-top: 10px;
                        }

                        .pop-up-message
                        {
                            font-size: 23px;
                            color: black;
                        }

                        .login_btn
                        {
                            width: 40%;
                        }

                        .top-div{
                            width: 100px;
                            margin: auto;
                        }

                        .div-two
                        {
                            font-size: 20px;
                            text-align: center;
                        }

                        .div-two > span:first-of-type
                        {
                            font-weight: 700;
                            color: red;
                        }

                        .ticket-limit-span
                        {
                            font-weight: 700;
                            color: red;
                            font-size:25px;
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

                        #showPopupMessageModal p,#showFirstWarningMessageModal p,#showSecondWarningMessageModal p 
                        {
                            text-align: center;
                            font-size: 20px;
                            font-weight: 600;
                        }

                        .login-time 
                        {
                            width: 100%;
                            margin: auto;
                            display: flex;
                            flex-wrap: wrap;
                        }

                        .login-time  div
                        {
                            flex-basis:110px;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                        }

                        .login-time  div p
                        {
                            font-size:60px;
                            margin-bottom:-14px;
                        }

                        .login-time  div span
                        {
                            font-size: 25px;
                            font-weight: 600;
                        }

                        .login-unavailable-div
                        {
                            border: 3px solid #ccc;
                            padding: 20px;
                            border-radius: 10px;
                            margin: 0 auto;
                            width: 500px;
                        }

                        #ticketsInQueueModal .queue-text-msg
                        {
                            font-size: 50px;font-weight: 500;text-align: center;
                        }

                        .modal-backdrop {
                            display: none !important;
                        }

                    @media(max-width:480px)
                    {
                        .login_btn
                        {
                            width: 100%;
                        }

                        .form-container,.login-unavailable-div {
                            width: 300px;
                        }
                    }
                    
                        @media(max-width:768px)
                        {
                            .view-id-details img
                            {
                                width: 70%;
                            }

                            .login-time {
                                padding-left: 10%;
                            }

                            .login-time div {
                                flex-basis: 100px;
                                padding: 3px;
                            }

                            #ticketsInQueueModal .queue-text-msg
                            {
                                font-size: 30px;
                            }
                        }

                        
                        
                    </style>

                    </head>

                    <?php 
                        $loginUnavailableNotificationData = Session::get('notificationMessages')->firstWhere('modal_no', 13);

                        $hideMemoSessionData = Session::get('first_page_visit');

                        $maXTicketsData = Session::get('maxTicketsReachedId');

                        $maXTicketsToday = Session::get('maXTicketsToday');

                        $maxTicketsMessage = Session::get('notificationMessages')->firstWhere('modal_no', 12)->message
                    ?>

                    @if ($maXTicketsData == 1)
                        <?php 
                            $maXTicketsDataMsg=Session::get('notificationMessages')->firstWhere('modal_no', 12)->message;
                        ?>
                    @else 
                        <?php 
                            $maXTicketsDataMsg='';
                        ?>
                    @endif

                    @if (session('user_name') && session('case_number'))
                        <?php 
                            $user_name=session('user_name');

                            $case_number=session('case_number');
                        ?>
                    @else 
                        <?php 
                            $user_name='';

                            $case_number='';
                        ?>
                    @endif



                    <body class="bg-white">
                        <div class="container">
                            <div class="row mt-2">
                                <div class="col-md-10 mx-auto">
                                    <div class="top-div">
                                        <a href="https://www.newhopemeadowview.com/food-ministry/" target="_blank" rel="noopener noreferrer">
                                            <img src="/tgog/tgoglogo.webp" class="img-fluid nhcc-logo w-100"  lt="NHCC Logo" />
                                        </a>
                                    </div>
                                    <div class="div-two">
                                        <p style="font-size:20px;">Enter your last name and case number to recieve your ticket number(s).</p>
                                    @if ($ticketLimitChecker->ticket_limit_status == 1)
                                        <span class="ticket-limit-span">Online ticket sign-up is available only for the first {{ $maXTicketsToday }} people.</span>
                                    @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-10 mt-2 mx-auto">
                                    <div class="row d-flex justify-content-center">
                                        <div class="col-6">
                                            @if (session('message'))
                                                <div class="alert alert-danger">
                                                    {!! session('message') !!}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <input type="hidden" id="maxTicketsReached" value="{{ $maXTicketsData }}"/>

                                    <input type="hidden" id="showHideMemo" value="{{ $hideMemoSessionData }}"/>
                                    
                                    <input type="hidden" class="users-login-start-date" value="{{ $loginUsersDateTime }}"/>

                                    <input type="hidden" class="users-login-end-date" value="{{ $loginUsersDateEndTime }}"/>

                                    <input type="hidden" class="next-week-login-start-date" value="{{ $nextWeekLoginStartTime }}"/>

                                    <input type="hidden" class="system-reset-date" value="{{ $systemResetingTime }}"/>

                                    <div class="login-form">
                                        <div class="login-unavailable-div d-none">
                                            <p id="maxTicketsReachedMsg">{{ strip_tags($maXTicketsDataMsg) }}</p>
                                            <p> {{ strip_tags($loginUnavailableNotificationData->message) }}</p>
                                            <p style="margin-bottom: -10px;">Login will be available in</p>
                                            <div class="login-time">
                                                <div>
                                                    <p id="days"></p>
                                                    <span>Days</span>
                                                </div>
                                                <div>
                                                    <p id="hours"></p>
                                                    <span>Hours</span>
                                                </div>
                                                <div>
                                                    <p id="minutes"></p>
                                                    <span>Minutes</span>
                                                </div>
                                                <div>
                                                    <p id="seconds"></p>
                                                    <span style="margin-left:5px;">Seconds</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- <form method="POST"  class="mt-2 d-none" id="loginForm"  action="{{ route('login') }}">
                                            @csrf -->
                                            <div class="mt-2 d-none" id="loginForm">
                                                <div class="row mb-4 d-flex justify-content-center">
                                                    <div class="col-12 ">
                                                        <label class="visually-hidden" for="username">Last Name</label>
                                                        <input type="text" class="form-control w-100 u-username" value="{{ $user_name }}" id="username" name="last_name" placeholder="Last Name" />
                                                        
                                                    </div>
                                                </div>
                                                    @if ($errors->has('last_name'))
                                                        <div class="alert alert-danger mt-2" style="width: 100%;">
                                                            {{ $errors->first('last_name') }}
                                                        </div>
                                                    @endif
                                                
                                                <div class="row mt-3">
                                                    <div class="col-12 text-center position-relative ">
                                                        <label class="visually-hidden" for="username">Case Number</label>
                                                        <input type="text" class="form-control w-100 u-casenumber" value="{{ $case_number }}" id="caseNumber" name="case_number" placeholder="Case Number" />
                                                    
                                                        <div style="position:absolute;top:10px; right:40px; width:30px" class="input-group">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">
                                                                    <i class="fas fa-eye-slash" id="togglePassword"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if ($errors->has('case_number'))
                                                <div class="alert alert-danger mt-2" style="width: 100%;">
                                                    {{ $errors->first('case_number') }}
                                                </div>
                                                @endif
                                                <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                                                <p id="loginBeingProcessed" class="d-none text-danger"></p>
                                                <div class="text-center"> 
                                                    <a  href="forget/user" class="text-center d-block">Forgot Case Number</a>
                                                    <button type="submit" id="userLoginBtn" class="btn btn-warning btn-md login_btn  mt-3 px-4 d-block mx-auto">Login</button>
                                                </div>
                                            </div>
                                            
                                            <input type="hidden" class="u-confirmcasenumber" value=" " name="confirm_case_number" placeholder="Confirm Case Number" />
                                        <!-- </form> -->
                                        <input type="hidden" id="checkEnabledMsg" value="{{ $enabledMsg }}">

                                    </div>
                                </div>
                            </div>
                            <input type="hidden" class="second_warning_notification_msg" value="{!! session()->get('second_warning_notification_msg') !!}">
                            <input type="hidden" class="first_warning_notification_msg" value="{!! session()->get('first_warning_notification_msg') !!}">
                            
                            <div class="bottom-row">
                                <div class="row">
                                    <div class="col-md-10 mx-auto mb-2"  style="margin-top:20px;">
                                    <p style="display: block; text-align:center; font-size:20px;">Check the current number being called before you return for your groceries.</p>
                                        <a href="/number-served" target="_blank" class="btn btn-warning btn-md login_btn  mt-3 px-4 d-block mx-auto">Check Number Being Served</a>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="view-id-details">
                                            <span style="text-transform:uppercase;">You Must Have Your Id Card to pick up your groceries</span>
                                            <img src="tgog/idcard.jpg" class="img-fluid">
                                            <p style="color:red; text-align:center;">Sample Card for Illustration</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal top fade"
                                id="showFirstWarningMessageModal"
                                tabindex="-1"
                                aria-hidden="true">
                                <div class="modal-dialog modal-fullscreen">
                                    <div class="modal-content">
                                    <div class="modal-header" style="display: flex;justify-content: flex-end;font-size: 20px;">
                                        <button style="background-color: #ffc107;border-radius: 100%;" type="button" class="proceed-to-create-tickets">X</button>
                                    </div>
                                    <div class="modal-body"><p></p></div>
                                    <div class="modal-footer" style="display: flex;justify-content: space-around;align-items: center;">
                                        <button type="button" style="width: 25%;" class="btn btn-warning btn-warning waves-effect proceed-to-create-tickets" data-dismiss="modal">Ok</button>
                                    </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal top fade"
                                id="showSecondWarningMessageModal"
                                tabindex="-1"
                                aria-hidden="true">
                                <div class="modal-dialog modal-fullscreen">
                                    <div class="modal-content">
                                    <div class="modal-header" style="display: flex;justify-content: flex-end;font-size: 20px;">
                                        <button style="background-color: #ffc107;border-radius: 100%;" type="button" class="close-show-warning-msg-modal">X</button>
                                    </div>
                                    <div class="modal-body"><p></p></div>
                                    <div class="modal-footer" style="display: flex;justify-content: space-around;align-items: center;">
                                        <button type="button" style="width: 25%;" class="btn btn-warning btn-warning waves-effect close-show-warning-msg-modal" data-dismiss="modal">Ok</button>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="modal fade" id="ticketsInQueueLoginModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-body">
                                        <p>We are experiencing heavier than usual demand, please continue to wait.  We apologize for the wait and appreciate your patience.</p>
                                    </div>
                                    <div style="display: flex;justify-content: space-around;align-items: center; margin: 5px;">
                                        <a class="btn btn-warning btn-warning waves-effect close-ticketsin-queue-modal">Ok</a>
                                    </div>
                                </div>
                            </div> -->

                    <!-- Modal -->
                            <div class="modal top fade"
                                id="showPopupMessageModal"
                                tabindex="-1"
                                aria-hidden="true">
                                <div class="modal-dialog modal-fullscreen">
                                    <div class="modal-content">
                                    <div class="modal-header" style="display: flex;justify-content: flex-end;font-size: 20px;">
                                        <button style="background-color: #ffc107;border-radius: 100%;" type="button" class="close-modal">X</button>
                                    </div>
                                    <div class="modal-body"><p class="pop-up-message"></p></div>
                                    <div class="modal-footer" style="display: flex;justify-content: space-around;align-items: center;">
                                        <button type="button" style="width: 25%;" class="btn btn-warning btn-warning waves-effect close-modal" data-dismiss="modal">Ok</button>
                                    </div>
                                    </div>
                                </div>
                            </div>

                        <!-- pusher js -->
                        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

                        <!-- jquery -->
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

                        <!-- bootstrap -->
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>

                        <!-- sweetalert -->
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
                        

                        <script>
                            $(document).on('click', '#userLoginBtn', function()
                            {
                                const confirm_case_number = $('.u-confirmcasenumber').val();

                                const case_number = $('.u-casenumber').val();

                                const last_name = $('.u-username').val();

                                const csrf = document.querySelector('meta[name="csrf-token"]').content;

                                var url = '/login';

                                $('#loginBeingProcessed').removeClass('d-none').text('Please wait as login in processed....')

                                $('#userLoginBtn').prop("disabled", true).css("cursor", "not-allowed");

                                $.ajax({
                                    url: url,
                                    method: 'POST',
                                    data: {
                                        last_name: last_name,
                                        case_number: case_number,
                                        confirm_case_number: confirm_case_number,
                                        _token: csrf
                                    },
                                    success: function(response) {

                                        console.log(response);

                                        $('#loginBeingProcessed').addClass('d-none').text('');

                                        $('#userLoginBtn').prop("disabled", false).css("cursor", "pointer");

                                        $('.error-list').html(" ").addClass('d-none');
                                        
                                        if (response.status == 201)
                                        { 
                                            var redirecturl = '/admin/dashboard';
                                            window.location.href = redirecturl;

                                        } else if (response.status == 415)
                                        {
                                            const htmlParser= new DOMParser().parseFromString(response.message, 'text/html');
                                            const textString= htmlParser.body.textContent;

                                            $('#showSecondWarningMessageModal').modal('show');

                                            $('#showSecondWarningMessageModal p').html('').html(textString)
                                            
                                        } else if (response.status == 416)
                                        {
                                            const htmlParser= new DOMParser().parseFromString(response.message, 'text/html');
                                            const textString= htmlParser.body.textContent;

                                            $('#showFirstWarningMessageModal').modal('show');

                                            $('#showFirstWarningMessageModal p').html('').html(textString)
                                            
                                        } else if (response.status == 203)
                                        {
                                            var redirecturl = '/multiple-users-ticket-details/'+response.id;
                                            window.location.href = redirecturl;
                                            
                                        } else if (response.status == 204)
                                        {
                                            var redirecturl = '/one-ticket-details/'+response.id;
                                            window.location.href = redirecturl;
                                            
                                        } else if (response.status == 200)
                                        {
                                            var redirecturl = '/user/get-ticket-options/';
                                            window.location.href = redirecturl;
                                            
                                        } else if (response.status == 410 || 
                                        response.status == 417 || 
                                        response.status == 418 || 
                                        response.status == 420 || 
                                        response.status == 421)
                                        {
                                            swal(response.message);

                                        } else if (response.status == 405) 
                                        {
                                            $('.error-list').html(" ").removeClass('d-none');

                                            $.each(response.message, function(key, err_value) 
                                            {
                                                $('.error-list').append('<li>' + err_value + '</li>');
                                            })
                                        }
                                    },error: function(xhr, status, error) {
                                        console.log(xhr);

                                        $('#loginBeingProcessed').addClass('d-none').text('');

                                        $('#userLoginBtn').prop("disabled", false).css("cursor", "pointer");

                                        if (xhr.responseJSON.status == 429) 
                                        { 
                                             $('#ticketsInQueueLoginModal').modal('toggle');
                                        }
                                    }
                                });

                            });

                            
                            $(document).on('click', '.close-ticketsin-queue-modal', function()
                            {
                                const confirm_case_number = $('.u-confirmcasenumber').val();

                                const case_number = $('.u-casenumber').val();

                                const last_name = $('.u-username').val();

                                const csrf = document.querySelector('meta[name="csrf-token"]').content;

                                var url = '/permit-login';

                                console.log(last_name,case_number,confirm_case_number);

                                $.ajax({
                                    url: url,
                                    method: 'POST',
                                    data: {
                                        last_name: last_name,
                                        case_number: case_number,
                                        confirm_case_number: confirm_case_number,
                                        _token: csrf
                                    },
                                    success: function(response) 
                                    {
                                        var redirecturl = '/user/get-ticket-options/';
                                        window.location.href = redirecturl;
                                    }
                                });

                            });


                            document.addEventListener('DOMContentLoaded', function() {
                                document.getElementById('togglePassword').addEventListener('click', function() {
                                    var icon = document.getElementById("togglePassword");
                                    var passwordField = document.getElementById('caseNumber');
                                    if (passwordField.type === 'password') 
                                    {
                                        passwordField.type = 'text';
                                        icon.classList.remove("fa-eye-slash");
                                        icon.classList.add("fa-eye");
                                    } else {
                                        passwordField.type = 'password';
                                        icon.classList.remove("fa-eye");
                                        icon.classList.add("fa-eye-slash");
                                    }
                                });

                            });

                            $(document).on('click', '.close-modal', function(e) {
                                e.preventDefault();

                                $('#showPopupMessageModal').modal('hide');
                            });

                            $(document).on('click', '.close-show-warning-msg-modal', function(e) {
                                e.preventDefault();

                                $('#showFirstWarningMessageModal').modal('hide');
                                $('#showSecondWarningMessageModal').modal('hide');
                            });

                            function formatDateToISO(date) {
                                const year = date.getFullYear();
                                const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
                                const day = String(date.getDate()).padStart(2, '0');
                                const hours = String(date.getHours()).padStart(2, '0');
                                const minutes = String(date.getMinutes()).padStart(2, '0');
                                const seconds = String(date.getSeconds()).padStart(2, '0');
                                
                                return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                            }


                            $(document).on('click', '.proceed-to-create-tickets', function(e) {
                                e.preventDefault();
                                const case_number = $('.u-casenumber').val();
                                const last_name = $('.u-username').val();
                                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                                
                                $.ajax({
                                    url: '/custom-login',
                                    method: 'POST',
                                    processData: false,
                                    contentType: false,
                                    data: function(){
                                        var data = new FormData();
                                        data.append('last_name' , last_name);
                                        data.append('case_number' , case_number);
                                        data.append('_token' , csrf);
                                        return data;
                                        console.log(data)
                                    }(),
                                    success: function(response) {

                                        console.log(response);

                                        if (response.url_type == 'multiple_tickets') {
                                            window.location.href = '/multiple-users-ticket-details/'+response.id;

                                        } else if (response.url_type == 'single_ticket') {
                                            window.location.href = '/one-ticket-details/'+response.id;
                                        } else if (response.url_type == 'get_ticket_options') {
                                            window.location.href = '/user/get-ticket-options';
                                        } 
                                    }
                                });
                            });

                            $(document).ready(function() 
                            {
                                console.log('<input type="text" class="u-username" id="username" name="last_name" placeholder="Last Name" />');
                                console.log('<br>');
                                console.log('<input type="text" class="u-casenumber" id="caseNumber" name="case_number" placeholder="Case Number" />');
                                console.log('<br>');
                                console.log('<input type="hidden" class="u-confirmcasenumber" name="confirm_case_number" placeholder="Confirm Case Number" />');
                            

                                // show modal pop up if there is a message
                                var memoStatus={{ $enabledMsgStatus }}

                                var memoMsg = $('#checkEnabledMsg').val()

                                var memoShowHide = $('#showHideMemo').val();

                                if(memoStatus == 1 && memoShowHide == 1)
                                {
                                    $('#showPopupMessageModal').modal('show');

                                    $('.pop-up-message').html(memoMsg)
                                }

                                var firstWarningMsg=$('.first_warning_notification_msg').val();

                                var secondWarningMsg=$('.second_warning_notification_msg').val();

                                if(firstWarningMsg !== '')
                                {
                                    $('#showFirstWarningMessageModal').modal('show');

                                    $('#showFirstWarningMessageModal p').html('').html(firstWarningMsg)
                                }

                                if(secondWarningMsg !== '')
                                {
                                    $('#showSecondWarningMessageModal').modal('show');

                                    $('#showSecondWarningMessageModal p').html('').html(secondWarningMsg)
                                }

                                var formattedCurrentDate=new Date();

                                var loginTime=$('.users-login-start-date').val();

                                var formattedLoginDate=new Date(loginTime);

                                var formattedLoginDateX = formatDateToISO(formattedLoginDate);


                                var loginEndDate=$('.users-login-end-date').val();

                                var formattedloginEndDate=new Date(loginEndDate);

                                var formattedloginEndDateX = formatDateToISO(formattedloginEndDate);


                                var nextLoginStartTime=$('.next-week-login-start-date').val();

                                var formattedNextLoginStartTime=new Date(nextLoginStartTime);

                                var formattedNextLoginStartTimeDateTime = formatDateToISO(formattedNextLoginStartTime);

                                var currentDateTimeNow = new Date();

                                const formattedCurrentDateTimeY = formatDateToISO(new Date());

                                var x = setInterval(function()
                                {

                                    // show login form when login time is about to start countdown
                                    var formattedCurrentDateX=new Date();

                                    const formattedCurrentDateTimeX = formatDateToISO(formattedCurrentDateX);

                                    const loginDateTimeDifference = formattedLoginDate - formattedCurrentDateX;

                                    const days = Math.floor(loginDateTimeDifference / (1000*60*60*24));
                                    const hours = Math.floor((loginDateTimeDifference % (1000*60*60*24)) / (1000*60*60));
                                    const minutes = Math.floor((loginDateTimeDifference % (1000*60*60)) / (1000*60));
                                    const seconds = Math.floor((loginDateTimeDifference % (1000*60)) / 1000);

                                    const endLoginTimeDifference = formattedloginEndDate - formattedCurrentDateX;

                                    const endLoginTimeDays = Math.floor(endLoginTimeDifference / (1000*60*60*24));
                                    const endLoginTimeHours = Math.floor((endLoginTimeDifference % (1000*60*60*24)) / (1000*60*60));
                                    const endLoginTimeMinutes = Math.floor((endLoginTimeDifference % (1000*60*60)) / (1000*60));
                                    const endLoginTimeSeconds = Math.floor((endLoginTimeDifference % (1000*60)) / 1000);

                                    // console.log(formattedloginEndDate,endLoginTimeDifference);

                                    const nextLoginDateTimeDifference = formattedNextLoginStartTime - formattedCurrentDateX;

                                    const nextLoginDateTimeDays = Math.floor(nextLoginDateTimeDifference / (1000*60*60*24));
                                    const nextLoginDateTimeHours = Math.floor((nextLoginDateTimeDifference % (1000*60*60*24)) / (1000*60*60));
                                    const nextLoginDateTimeMinutes = Math.floor((nextLoginDateTimeDifference % (1000*60*60)) / (1000*60));
                                    const nextLoginDateTimeSeconds = Math.floor((nextLoginDateTimeDifference % (1000*60)) / 1000);

                                    console.log(new Date(),nextLoginDateTimeDays,nextLoginDateTimeHours,nextLoginDateTimeMinutes,nextLoginDateTimeSeconds);

                                    if (formattedCurrentDateTimeX >= formattedLoginDateX && loginDateTimeDifference < 0 && endLoginTimeDifference < 0)
                                    {

                                        $('#days').text(nextLoginDateTimeDays);
                                        $('#hours').text(nextLoginDateTimeHours);
                                        $('#minutes').text(nextLoginDateTimeMinutes);
                                        $('#seconds').text(nextLoginDateTimeSeconds);

                                        $('#loginForm').hide().addClass('d-none');

                                        $('.login-unavailable-div').show().removeClass('d-none');

                                        $('.login-unavailable-div p').eq(0).hide().addClass('d-none');

                                        $('.login-unavailable-div p').eq(1).show().removeClass('d-none');
                                    }

                                    if (formattedCurrentDateTimeX <= formattedLoginDateX && loginDateTimeDifference > 0 && endLoginTimeDifference > 0)
                                    {
                                        $('#days').text(days);
                                        $('#hours').text(hours);
                                        $('#minutes').text(minutes);
                                        $('#seconds').text(seconds);

                                        $('#loginForm').hide().addClass('d-none');

                                        $('.login-unavailable-div').show().removeClass('d-none');

                                        $('.login-unavailable-div p').eq(0).hide().addClass('d-none');
                                    }

                                    if (formattedCurrentDateTimeX >= formattedLoginDateX && 
                                    formattedCurrentDateTimeX <= formattedloginEndDateX && 
                                    loginDateTimeDifference < 0 && endLoginTimeDifference > 0)
                                    {
                                        $('#loginForm,.div-two').show().removeClass('d-none');

                                        $('.login-unavailable-div').hide().addClass('d-none');

                                        // Get current date
                                        let currentDate = new Date();

                                        // Set the time to 23:59:00
                                        currentDate.setHours(23);
                                        currentDate.setMinutes(59);
                                        currentDate.setSeconds(0);
                                        currentDate.setMilliseconds(0);

                                        // Format the date to YYYY-MM-DD 23:59:00
                                        let formattedCurrentDate = currentDate.toISOString().slice(0, 19).replace("T", " ");

                                        $('.previous-issue-date').val(formattedCurrentDate);

                                        var maxTicketsReached = $('#maxTicketsReached').val();

                                        if(maxTicketsReached == 1 && formattedCurrentDateTimeX >= formattedLoginDateX && 
                                        formattedCurrentDateTimeX <= formattedloginEndDateX)
                                        {
                                            $('#loginForm,.login-time').hide().addClass('d-none');

                                            $('.login-unavailable-div').show().removeClass('d-none');

                                            $('.login-unavailable-div p').eq(1).hide().addClass('d-none');

                                            $('.login-unavailable-div p').eq(2).hide().addClass('d-none');

                                            const max_tickets_msg_html = {!! json_encode($maxTicketsMessage) !!};

                                            const max_tickets_msg_stripped = max_tickets_msg_html.replace(/<\/?[^>]+(>|$)/g, "");

                                            $('#maxTicketsReachedMsg').text(max_tickets_msg_stripped).show().removeClass('d-none');
                                        }
                                    }

                                },1000);

                                var rawSystemResetTime=$('.system-reset-date').val();

                                var formattedSystemResetTime=new Date(rawSystemResetTime);  

                                var y = setInterval(function()
                                {
                                    const systemResetTimeDifference = formattedSystemResetTime - currentDateTimeNow;

                                    const reset_days = Math.floor(systemResetTimeDifference / (1000*60*60*24));
                                    const reset_hours = Math.floor((systemResetTimeDifference % (1000*60*60*24)) / (1000*60*60));
                                    const reset_minutes = Math.floor((systemResetTimeDifference % (1000*60*60)) / (1000*60));
                                    const reset_seconds = Math.floor((systemResetTimeDifference % (1000*60)) / 1000);
                                    
                                    if(rawSystemResetTime !== '')
                                    {
                                        if(systemResetTimeDifference < 0 || rawSystemResetTime == formattedCurrentDateTimeY)
                                        {
                                            clearInterval(y);

                                            $('.system-reset-date').val('');
                                        } else {
                                            console.log('system to reset in '+reset_days+','+reset_hours+','+reset_minutes+','+reset_seconds+','+systemResetTimeDifference)
                                        }
                                        
                                    }

                                },1000);

                                // hide show ticket limit message
                                Pusher.logToConsole = true;

                                var pusherAppKey = '{{ env('PUSHER_APP_KEY') }}';

                                var pusher = new Pusher(pusherAppKey, {
                                    cluster: 'eu'
                                });

                                var channel = pusher.subscribe('max_tickets');
                                channel.bind('max_tickets_data', function(data) {
                                
                                    var maxTicketsData=data.max_tickets;

                                    console.log(maxTicketsData);

                                    if(maxTicketsData == 1)
                                    {
                                        $('#maxTicketsReached').val(1);

                                        $('#loginForm,.login-time').hide().addClass('d-none');

                                        $('.login-unavailable-div').show().removeClass('d-none');

                                        $('.login-unavailable-div p').eq(1).hide().addClass('d-none');

                                        $('.login-unavailable-div p').eq(2).hide().addClass('d-none');

                                        const max_tickets_msg_html = {!! json_encode($maxTicketsMessage) !!};

                                        const max_tickets_msg_stripped = max_tickets_msg_html.replace(/<\/?[^>]+(>|$)/g, "");

                                        $('#maxTicketsReachedMsg').text(max_tickets_msg_stripped);
                                    }
                                });

                                
                            });
                            
                            $(document).on('keyup', '.u-username', function()
                            {
                                var typedUsername = $(this).val();

                            console.log(typedUsername)
                            })

                            $(document).on('keyup', '.u-casenumber', function()
                            {
                                var typedCaseNumber = $(this).val();

                                console.log(typedCaseNumber)
                            })
                        </script>
                    </body>

                    </html>
