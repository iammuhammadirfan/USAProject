@extends('admin.layout')
@section('content')
@section('register_recipient_styles')
    {{-- date picker css--}}
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">

    {{-- alertify --}}
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>
    <!-- Default theme -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/default.min.css"/>
    <!-- Semantic UI theme -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/semantic.min.css"/>

    <style>
        .overview-dashboard-holder
        {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .details-holder
        {
            width: 30%;
            height: 150px;
            text-align: center;
            padding: 20px;
            font-size: 30px;
            font-weight: 500;
            border-radius: 20px;
        }

        .now-serving-div{
            display: flex;
            justify-content: space-around;
            align-items: center;
            color: red;
            width: 60%;
            height:300px;
            margin: 0 auto;
            font-size:70px;
            background-color: black;
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
    </style>
@stop

<div class="row mt-5">
    <div class="col-md-10 mx-auto">
        <div style="display: flex; justify-content:flex-start; align-items:center;">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-warning btn-lg px-5 border border-dark py-1 mb-2" style="border: 1px solid #343a40;">Main Menu</a>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-6 mx-auto">
        <p style="font-size:18px; font-weight:400;">Add a new recipient record to the system. This must be done after
            registering the recipient in Oasis to get the case number. All fields are
            required.</p>
            
        <form method="POST"  class="mt-5 add-new-record-form">
            @csrf
            <div class="row mb-4 d-flex justify-content-center">
                <div class="col-12 ">
                    <label class="visually-hidden" for="firstName">First Name</label>
                    <input type="text" autocomplete="off" class="form-control w-100" id="firstName" name="first_name" placeholder="First Name" />
                </div>
            </div>
            
            <div class="row mb-4 d-flex justify-content-center">
                <div class="col-12 ">
                    <label class="visually-hidden" for="lastName">Last Name</label>
                    <input type="text" autocomplete="off" class="form-control w-100" id="lastName" name="last_name" placeholder="Last Name" />
                </div>
            </div>
            <div class="row mb-4 d-flex justify-content-center">
                <div class="col-12 ">
                    <label class="visually-hidden" for="recordDateOfBirth">Date of Birth (MM/DD/YYYY)</label>
                    <input type="text" autocomplete="off" class="form-control w-100" id="recordDateOfBirth" name="date_of_birth" placeholder="Date of Birth (MM/DD/YYYY)" />
                </div>
            </div>
            <div class="row mb-4 d-flex justify-content-center">
                <div class="col-12 ">
                    <label class="visually-hidden" for="recordCaseNumber">Case Number</label>
                    <input type="text" autocomplete="off" class="form-control w-100" id="recordCaseNumber" name="case_number" placeholder="Case Number" />
                </div>
            </div>
            <div class="row mb-4 d-flex justify-content-center">
                <div class="col-6">
                    <p>Has an approved proxy for pick up?</p>
                    <input type="radio" id="yesProxy" class="allowed-proxy" name="allowed_proxy" value="yes">
                    <label for="yesProxy">Yes</label><br>
                    <input type="radio" id="noProxy" class="allowed-proxy" name="allowed_proxy" value="no">
                    <label for="noProxy">No</label>
                </div>
                <div class="col-6">
                    <p>Disable User</p>
                    <input type="radio" id="yesDisableUser" class="disable-user" name="disable_user" value="0">
                    <label for="yesDisableUser">Yes</label><br>
                    <input type="radio" id="noDisableUser" class="disable-user" name="disable_user" value="1">
                    <label for="noDisableUser">No</label>
                </div>
            </div>
            <div class="row mb-4 d-flex justify-content-center">
                <div class="col-12 ">
                    <input type="text" autocomplete="off" class="form-control w-100" id="idCardIssueDate" name="idcard_issue_date" placeholder="ID Card Issued Date (MM/DD/YYYY)" />
                </div>
            </div>
            <div class="row mb-4 d-flex justify-content-center">
                <div class="col-12 ">
                    <label for="addIdCardFile">ID Card</label>
                    <input type="file" class="form-control w-100" accept="image/png, image/jpeg" id="addIdCardFile" name="idcard_file" placeholder="ID Card File" />
                    <div class="card-img-preview">
                        <img class="preview-display-image" src="/images/no_id_card_available.png"/>
                    </div>
                </div>
            </div>
            <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
            <div style="display: flex;gap: 7px;flex-direction: column; width:80%; margin:10px auto;">
				<a href="#" class="btn btn-warning btn-md waves-effect border border-dark  add-new-record-btn" redirect="0">Add Record</a>
				<a href="#" class="btn btn-warning btn-md waves-effect border border-dark add-new-record-btn" redirect="1">Add Record and Get Single Ticket</a>
				<a href="#" class="btn btn-warning btn-md waves-effect border border-dark add-new-record-btn" redirect="2">Add Record and Get Multiple Tickets</a>
			</div>
        </form>
    </div>
</div>

@endsection

@section('register_recipient_script')
    {{-- alertify --}}
   <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>

   {{-- select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- timepicker --}}
    <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
   
    {{-- jquery ui plugins --}}
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>

   <script>

        $(document).ready(function() {
            // datepicker for selecting the date of birth
            $( function() {
                $( "#recordDateOfBirth,#idCardIssueDate" ).datepicker(
                    {
                        dateFormat: 'mm/dd/yy'
                    }
                );
            });

            $("#noDisableUser").prop("checked", true);
        });

        $(document).on('change', '#addIdCardFile', function(e)
        {
			e.preventDefault();

			var output = document.querySelector('.preview-display-image');

			console.log(output);
			output.src = URL.createObjectURL(event.target.files[0]);
			output.onload = function() {
				URL.revokeObjectURL(output.src) // free memory
			}
		});

        $(document).on('click', '.add-new-record-btn', function(e) 
			{
                e.preventDefault();

                var first_name=$('#firstName').val();

                var last_name=$('#lastName').val();

                var date_of_birth=$('#recordDateOfBirth').val();

                var idcard_issue_date=$('#idCardIssueDate').val();

                var idcard=$('#addIdCardFile').prop("files")[0];

                if(idcard == undefined)
                {
                    var id_card='';
                } else {
                    var id_card=idcard;
                }

                var case_number=$('#recordCaseNumber').val();

                var proxy = $("input[type='radio'][name='allowed_proxy']:checked").val();

                var disable_login = $("input[type='radio'][name='disable_user']:checked").val();

                var csrf = document.querySelector('meta[name="csrf-token"]').content;
				
				var redirectUser=$(this).attr("redirect");
				
				var generatedBy = '';
				
				if(redirectUser == 1)
				{
					var url = '/register-record-single-ticket';
					var generatedBy = 1;
				} else {
					var url = '/register';
					var generatedBy = '';
				}
                
				
				$('.add-new-record-btn').prop("disabled", true).css("cursor", "not-allowed");

                $.ajax({
                    url: url,
                    method: 'POST',
                    processData: false,
                    contentType: false,
                    data: function()
					{
                        var data = new FormData();
                        data.append('first_name',first_name);
                        data.append('last_name',last_name);
                        data.append('_token' , csrf);
                        data.append('case_number' ,case_number);
                        data.append('date_of_birth' ,date_of_birth);
                        data.append('proxy' ,proxy);
                        data.append('id_card' ,id_card);
                        data.append('disable_login' ,disable_login);
                        data.append('idcard_issue_date' ,idcard_issue_date);
                        data.append('idcard_issue_date' ,idcard_issue_date);
						data.append('generatedBy' ,generatedBy);
                        return data;
                        console.log(data)
                    }(),
                    success: function(response) 
					{
                        console.log(response);
				
						$('.add-new-record-btn').prop("disabled", false).css("cursor", "pointer");
						
						const userId=response.user_id;
				
                        if (response.status == 200) 
						{
                            $('.error-list').html(" ").addClass('d-none');

                            $('#firstName,#addIdCardFile,#lastName,#caseNumber,#recordCaseNumber,#recordDateOfBirth,#idCardIssueDate').val('');

                            $(".allowed-proxy").prop("checked", false);

                            $("#noDisableUser").prop("checked", true);

                            $('.preview-display-image').attr('src','/images/no_id_card_available.png')

                            alertify.set('notifier', 'position', 'top-center');
                            alertify.success(response.message);
							
							if(redirectUser == 2)
							{
								var redirectUrlLink = '/admin/multiple-tickets-details/'+userId;
								window.open(redirectUrlLink, "_blank")
							} else if (redirectUser == 1)
							{
								var redirectUrlLink = '/one-ticket-details/'+response.ticket_id;
								window.open(redirectUrlLink, "_blank")
							}

                        } else if (response.status == 405) 
						{
                            $('.error-list').html(" ").removeClass('d-none');
                            $.each(response.message, function(key, err_value) {
                                $('.error-list').append('<li>' + err_value + '</li>');
                            })
                        } else if (response.status == 500) 
						{
                            $('.error-list').html(" ").addClass('d-none');
                            
                            alertify.set('notifier', 'position', 'top-center');
                            alertify.success(response.message);
                        }
                    }
                });
            });
   </script>
@endsection