<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>GoGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css"> -->
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

    /* .modal-dialog, .modal-content {
        height: 65%;
    } */

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
        color: black;
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

    /* #ticketsInQueueModal .queue-text-msg
    {
        font-size: 50px;font-weight: 500;text-align: center;
    } */

  @media(max-width:480px)
  {
	.login_btn
	{
		width: 100%;
	}

    .form-container {
        width: 300px;
    }
  }
  
	@media(max-width:768px)
	{
		.view-id-details img
		{
			width: 70%;
		}

        /* #ticketsInQueueModal .queue-text-msg
        {
            font-size: 30px;
        } */
	}

    
    
</style>

</head>

<body class="bg-white">
    <div class="container">
        <div class="row mt-2">
            <div class="col-md-10 mx-auto">
                <div class="top-div">
                    <a href="https://www.newhopemeadowview.com/food-ministry/" target="_blank" rel="noopener noreferrer">
                        <img src="tgoglogo.webp" class="img-fluid nhcc-logo w-100"  lt="NHCC Logo" />
                    </a>
                </div>
                <div class="div-two">
                    <p style="font-size:20px;">Administrator Login</p>
                </div>
            </div>
        </div>
        <!-- <div style="display: flex; justify-content:flex-start">
            <a class="btn btn-warning reset-error-tickets btn-md">Reset Tickets</a>
        </div> -->
        <div class="row d-flex justify-content-center">
            <div class="col-6">
                @if (session('message'))
                    <div class="alert alert-danger">
                        {!! session('message') !!}
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 mt-2 mx-auto">
                <div class="login-form">
                    <!-- <form method="POST"  class="mt-2" id="loginForm"  action="{{ route('login') }}">
                        @csrf -->
                        <div class="form-container">
                            <div class="row mb-4 d-flex justify-content-center">
                                <div class="col-12 ">
                                    <input type="text" class="form-control w-100 u-username" id="username" name="last_name" placeholder="Username" />
                                </div>
                            </div>
                                <!-- @if ($errors->has('last_name'))
                                    <div class="alert alert-danger mt-2" style="width: 100%;">
                                        {{ $errors->first('last_name') }}
                                    </div>
                                @endif -->
                            
                            <div class="row mt-3">
                                <div class="col-12 text-center position-relative ">
                                    <input type="text" class="form-control w-100 u-casenumber" id="caseNumber" name="case_number" placeholder="Passphrase" />
                                    <div style="position:absolute;top:10px; right:40px; width:30px" class="input-group">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="fas fa-eye-slash" id="togglePassword"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <input type="hidden" name="login_type" value="admin_login" />
                                </div>
                            </div>
                            <!-- @if ($errors->has('case_number'))
                            <div class="alert alert-danger mt-2" style="width: 100%;">
                                {{ $errors->first('case_number') }}
                            </div>
                            @endif -->
                            <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                            <p id="loginBeingProcessed" class="d-none text-danger"></p>
                            <div class="text-center"> 
                                <!-- <a  href="forget/user" class="text-center d-block">Forgot Case Number</a> -->
                                <button type="submit"   class="btn btn-warning btn-md admin-login-btn  mt-3 px-4 d-block mx-auto">Login</button>
                            </div>
                        </div>
                        <input type="hidden" class="u-confirmcasenumber" value=" " name="confirm_case_number" placeholder="Confirm Case Number" />
                    <!-- </form> -->

                    <!-- Input field to receive barcode data
                    <input type="text" id="barcode-input" placeholder="Scan barcode here" autofocus />
                    
                     A button to manually trigger processing \
                    <button id="process-barcode-btn">Process Barcode</button>

                    <div id="output"></div> -->

                </div>
            </div>
        </div>

        <!-- <div class="modal fade" id="ticketsInQueueModal" data-backdrop="static" data-keyboard="false" style="background-color:#ffc107;" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <p class="queue-text-msg">Congratulations! You are next in line. There’s a short wait
                time, so please don’t refresh or close this session. Your wait
                time is shown in the countdown, and once it ends, you’ll be
                automatically redirected to get your ticket!</p>
                <p id="countDownToTickets" style="font-size: 50px;text-align: center;"></p>
            </div>
        </div> -->
        
    <!-- <div class="modal fade modal-fullscreen-xl" id="showPopupMessageModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="background-color: rgb(223 235 247);">
                <div class="modal-body">
                        <div class="row section-groups m-2">
                            <div class="col-12">
                                <p class="pop-up-message"></p>
                            </div>
                        </div>
                    </div>
                
                    <div class="d-flex justify-content-around align-items-center mx-auto m-2 w-50">
                    <button type="button" class="btn btn-warning btn-warning waves-effect close-modal" data-dismiss="modal">Ok</button>
                    </div>
                </div> 
            </div>
        </div>
    </div> -->

<!-- Modal -->

    {{-- jquery --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

    {{-- bootstrap --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).on('click', '.admin-login-btn', function()
        {
            const confirm_case_number = $('.u-confirmcasenumber').val();

            const case_number = $('.u-casenumber').val();

            const last_name = $('.u-username').val();

            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            var url = '/login';

            $('#loginBeingProcessed').removeClass('d-none').text('Please wait as login in processed....')

            $('.admin-login-btn').prop("disabled", true).css("cursor", "not-allowed");

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

                    $('.admin-login-btn').prop("disabled", false).css("cursor", "pointer");

                    $('.error-list').html(" ").addClass('d-none');

                    if (response.status == 201)
                    { 
                        var redirecturl = '/admin/dashboard';
                        window.location.href = redirecturl;

                    } else if (response.status == 405) 
                    {
                        $('.error-list').html(" ").removeClass('d-none');

                        $.each(response.message, function(key, err_value) 
                        {
                            $('.error-list').append('<li>' + err_value + '</li>');
                        })
                    } else if (response.status == 421) 
                    {
                         Swal.fire(response.message);
                    }
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

        //$(document).on('click', '.reset-error-tickets', function() 
        // {
        //     $.ajax({
        //         url: '/reset-error-tickets',
        //         method: 'GET',
        //         processData: false,
        //         contentType: false,
        //         success: function(response) {
        //             console.log(response)

        //             swal(response.message);
        //         }
        //     })
        // });


        $(document).ready(function() {

            console.log('<input type="text" class="u-username" id="username" name="last_name" placeholder="Last Name" />');
            console.log('<br>');
            console.log('<input type="text" class="u-casenumber" id="caseNumber" name="case_number" placeholder="Case Number" />');
            console.log('<br>');
            console.log('<input type="hidden" class="u-confirmcasenumber" name="confirm_case_number" placeholder="Confirm Case Number" />');

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
