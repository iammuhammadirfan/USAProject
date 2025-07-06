<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GoGo</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" >

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<!-- CSS only -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.0/font/bootstrap-icons.css" rel="stylesheet">

    {{-- date picker css--}}
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">

    <style>
        #ticketsInQueueModal .queue-text-msg
        {
            font-size: 50px;font-weight: 500;text-align: center;
        }
    
        @media(max-width:768px)
        {

            #ticketsInQueueModal .queue-text-msg
            {
                font-size: 30px;
            }
        }
    </style>
</head>

<body class="bg-white ">
    <div class="container mt-5  h-100">
        <div class="row">
            <div class="col-md-12">
                <h1 style="
    text-align: center;
    font-size: 40px;
">The Gift of Groceries</h1>
            </div>
        </div>
        <div class="row" style="justify-content: space-around;">
            <div class="col-md-6">
                <p style="font-size: 20px;font-weight: 500;">
                                    All fields are required.<br>
                If you need further assistance, please visit New Hope Community Church
                (1821 Meadowview Road, Sacramento, CA 95832) on THURSDAYS
                                </p>
                <form class="w-100" action="#" id="newCaseNumber">
                    @csrf
                    <div class="mt-5">
                        <div class="row">
                            <div class="col-12">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" name="first_name" id="first_name"
                                        placeholder="First Name" />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" name="last_name" id="last_name"
                                        placeholder="Last Name"  />
                                </div>
                                @if ($errors->has('last_name'))
                                    <div class="alert alert-danger mt-2" style="width: 100%;">
                                        {{ $errors->first('last_name') }}
                                    </div>
                                @endif
                            </div>
                            <div class="col-12">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" name="date_of_birth"
                                        id="dateOfBirth" placeholder="Date of Birth (MM/DD/YYYY)" />
                                </div>
                            </div>
                            <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                            <div class="col-12 error-message-container alert alert-danger d-none"></div>
                            <div class="col-12">    
                                <div class="d-flex justify-content-center">
                                    <button type="button" id="saveCaseNumber"
                                        class="btn btn-warning btn-sm py-2 btn-block px-5 border border-dark rounded-0">Get
                                        Case Number</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="row py-4">
                    <div class="col-12">
                        <p class="h6 mb-0 text-black">Your Case Number: <span id="case_number"></span></p>
                        @if ($errors->has('case_number'))
                            <div class="alert alert-danger mt-2" style="width: 100%;">
                                {{ $errors->first('case_number') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="row ">
                    <div class="col-12">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <input type="hidden" class="form-control" name="last_name" id="lastname"
                                placeholder="Last Name" />
                            <input type="hidden" class="form-control" name="case_number" id="caseNumber"
                                placeholder="Case Number" />
                            <p id="loginBeingProcessed" class="d-none text-danger"></p>
                            <div class="d-flex justify-content-center">

                                <button type="submit" id="userLoginBtn"
                                    class="btn btn-warning btn-sm py-2 btn-block px-5 border border-dark rounded-0">
                                    Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal top fade"
            id="showFirstWarningMessageModal"
            tabindex="-1"
            aria-hidden="true"
            data-mdb-backdrop="true"
            data-mdb-keyboard="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                <div class="modal-header" style="display: flex;justify-content: flex-end;font-size: 20px;">
                    <button style="background-color: #ffc107;border-radius: 100%;" type="button" class="close-show-warning-msg-modal">X</button>
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
            aria-hidden="true"
            data-mdb-backdrop="true"
            data-mdb-keyboard="true">
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
        <div class="modal fade" id="ticketsInQueueLoginModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-body">
                    <p>We are experiencing heavier than usual demand, please continue to wait.  We apologize for the wait and appreciate your patience.</p>
                </div>
                <div style="display: flex;justify-content: space-around;align-items: center; margin: 5px;">
                    <a class="btn btn-warning btn-warning waves-effect close-ticketsin-queue-modal">Ok</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    {{-- jquery ui plugins --}}
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>

<script type="text/javascript">

    $(document).ready(function() {

        // datepicker for selecting the date of birth
        $( function() {
            $( "#dateOfBirth" ).datepicker(
                {
                    dateFormat: 'mm/dd/yy'
                }
            );
        });
    });

    $(document).on('click', '#userLoginBtn', function()
        {
            const confirm_case_number = $("#ConfirmCaseNumber").val();

            const case_number = $('#caseNumber').val();

            const last_name = $('#lastname').val();

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

                    $('#userLoginBtn').prop("disabled", false).css("cursor", "pointer");

                    $('.error-list').html(" ").addClass('d-none');

                    $('#loginBeingProcessed').addClass('d-none').text('');
                    
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

                    $('#userLoginBtn').prop("disabled", false).css("cursor", "pointer");

                    $('#loginBeingProcessed').addClass('d-none').text('');

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

    $(document).on('click', '#saveCaseNumber', function(e) {
        e.preventDefault();

        var form = $('#newCaseNumber')[0];
        var formdata = new FormData(form);

        $.ajax({
            url: "{{ route('admin.forget.userCaseNumber') }}",
            method: 'POST',
            processData: false,
            contentType: false,
            data: formdata,
            success: function(response) {

                console.log(response);

                $('.error-list').html(" ").addClass('d-none');

                $('.error-message-container').addClass('d-none');


                if (response.status == 200) {
                    $("#case_number").text(response.data.case_number);
                    $("#lastname").val(response.data.last_name);
                    $("#caseNumber").val(response.data.case_number);
                    $("#ConfirmCaseNumber").val(response.data.case_number);

                } else if (response.status == 405) {
                    $('.error-list').html(" ").removeClass('d-none');
                    $.each(response.message, function(key, err_value) {
                        $('.error-list').append('<li>' + err_value + '</li>');
                    })
                } else if (response.status == 400) {
                    $(".error-message-container").text(response.message).removeClass('d-none');
                }

            }
        });
    });
</script>

</body>

</html>