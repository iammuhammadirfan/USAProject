@extends('admin.layout')
@section('content')
@section('view_volunteer_group_signups_styles')
{{-- datatables --}}
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css" />


{{--sweetalert--}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

{{-- select2 --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Override link color
                    a {
                        color: #000 !important;
                        /* Black color 
                        text-decoration: none;
                    }

                    @media (min-width: 1200px) {
                        .container, .container-lg, .container-md, .container-sm, .container-xl {
                            max-width: 1400px;
                        }
                    } */

    /* Styling the select element inside the DataTable */
      .case-numbers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            min-height: 40px;
        }
        
        .case-number-item {
            background-color: #e9ecef;
            border: 1px solid #adb5bd;
            border-radius: 4px;
            padding: 8px 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
        }
        
        .case-number-item:hover {
            background-color: #dee2e6;
            border-color: #6c757d;
        }
        
        .case-number-item.selected {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
        }
        
        .case-number-item.selected:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        
        .no-case-numbers-message {
            color: #6c757d;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }
    .dt-length label {
        margin-left: 10px;
    }

    td,
    .dt-type-numeric {
        text-align: center;
    }

    .select2-selection__choice {
        list-style-type: none;
    }

    .new-volunteer-group-user,
    .new-volunteer-group-signup {
        background-color: #ffc107;
        padding: 10px;
        margin: 20px;
        border-radius: 7px;
        display: inline-block;
        text-decoration: none;
        cursor: pointer;
    }

    .dt-length label {
        margin-left: 10px;
    }

    .text-center {
        text-align: center;
    }

    .dt-search {
        float: right;
    }

    /* Dropdown css code */
    .datatable-dropdown {
        display: flex;
        align-items: center;
        gap: 5px;
        width: 60%;
        border-radius: 5px;
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
        background-color: #ffc107;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        left: -50px;
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

    .dropdown-content a:hover {
        background-color: #f1f1f1;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .volunteer-group-signups-dashboard-holder {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }

    .details-holder {
        width: 30%;
        height: 230px;
        text-align: center;
        margin: 10px;
        padding: 10px;
        font-weight: 500;
        border-radius: 20px;
    }

    @media(max-width:1024px) {
        .details-holder {
            width: 100%;
            height: 170px;
        }
    }

    /* @media(max-width:768px)
                    {
                        .details-holder {
                            width: 100%;
                            height: 170px;
                        }
                    } */
</style>
@stop
<div class="row m-2">
    <div>
        <div class="title-holder" style="display:flex;align-items:center;justify-content:space-between;">
            <h4 style="margin-left:20px;">Approved Names</h4>
            <button type="button" id="clearSearch" class="btn btn-warning btn-sm">Clear</button>
        </div>

        <table id="volunteerGrouphomeUsersTable" class="table table-bordered">
            <thead class="bg-info text-white">
                <tr>
                    <td>First Name</td>
                    <td>Last Name</td>
                    <td>Action</td>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

    </div>
    <div class="modal fade" id="confirmUserServingModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <p>Do you really want to change the serve status to <span class="new-serve-status"></span>
                    <p>
                </div>
                <div style="display: flex;justify-content: space-around;align-items: center;margin: 5px;">
                    <a class="btn btn-warning waves-effect border border-dark close-modal">Cancel</a>
                    <a class="btn btn-warning waves-effect border border-dark continue-serve-confirmation-btn">Continue</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmSignUpDeleteModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <p>Are You sure you want to Delete the Sign Up?Click continue to proceed or cancel to keep.</p>
                </div>
                <div style="display: flex;
                    justify-content: space-around;
                    align-items: center;
                    margin: 5px;">
                    <a class="btn btn-warning waves-effect border border-dark close-modal">Cancel</a>
                    <a class="btn btn-warning waves-effect border border-dark continue-signup-delete-btn">Continue</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmUserDeleteModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <p>Are You sure you want to Delete the User?Click continue to proceed or cancel to keep.</p>
                </div>
                <div style="display: flex;
                    justify-content: space-around;
                    align-items: center;
                    margin: 5px;">
                    <a class="btn btn-warning waves-effect border border-dark close-modal">Cancel</a>
                    <a class="btn btn-warning waves-effect border border-dark continue-user-delete-btn">Continue</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="newVolunteerGroupUserModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-title" style="text-align: center;">
                    <h4 id="newVolunteerGroupUserTItle"></h4>
                </div>
                <input class="user-id" type="hidden">
                <input class="volunteer-group-btn-type" type="hidden">
                <div class="modal-body">
                    <div class="form-group">
                        <input class="first-name form-control" type="text" placeholder="First Name">
                    </div>
                    <div class="form-group">
                        <input class="last-name form-control" type="text" placeholder="Last Name">
                    </div>
                    <!-- Add this case numbers section -->
                   <div class="form-group case-numbers-section" style="display: none;">
                        <label for="case-numbers-display">Case Numbers:</label>
                        <div class="case-numbers-display case-numbers-grid">
                            <span class="no-case-numbers-message">No case numbers available</span>
                        </div>
                    </div>
                </div>
                <ul style="list-style-type: none;text-align:left;" class="alert alert-warning d-none error-list col-12"></ul>
                <div style="display: flex;justify-content: space-around;align-items: center;margin: 5px;">
                    <a class="btn btn-warning waves-effect border border-dark close-modal">Cancel</a>
                    <a class="btn btn-warning waves-effect border border-dark" id="newVolunteerGroupUserBtn">Save</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="newVolunteerGroupSignupModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-title" style="text-align: center;">
                    <h4 id="newVolunteerGroupSignupTItle"></h4>
                </div>
                <input class="volunteer-group-id" type="hidden">
                <div class="modal-body">
                    <div class="form-group select-user-holder">
                        <label for="selectUser">User</label>
                        <select id="selectUser" class="form-control text-dark admin-select2 select-user" style="width: 100%;">
                        </select>
                    </div>
                    <div class="form-group">
                        <input class="volunteer-group-amount form-control" type="number" placeholder="Amount">
                    </div>
                    <input class="volunteer-group-type" type="hidden">
                </div>
                <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                <div style="display: flex;justify-content: space-around;align-items: center;margin: 5px;">
                    <a class="btn btn-warning waves-effect border border-dark close-modal">Cancel</a>
                    <a class="btn btn-warning waves-effect border border-dark newVolunteerGroupSignupBtn">Save</a>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editVolunteerGroupSignupModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-title" style="text-align: center;">
                    <h4 id="editVolunteerGroupSignupTItle">Edit Sign Up</h4>
                </div>
                <input class="edit-volunteer-group-id" type="hidden">
                <div class="modal-body">
                    <div class="form-group select-user-holder">
                        <label for="selectEditUser">User</label>
                        <select id="selectEditUser" class="form-control text-dark admin-select2 select-user" style="width: 100%;"></select>
                    </div>

                    <div class="form-group">
                        <input class="edit-volunteer-group-amount form-control" type="number" placeholder="Amount">
                    </div>
                </div>
                <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                <div style="display: flex;justify-content: space-around;align-items: center;margin: 5px;">
                    <a class="btn btn-warning waves-effect border border-dark close-modal">Cancel</a>
                    <a class="btn btn-warning waves-effect border border-dark editVolunteerGroupSignupBtn">Save</a>
                </div>
            </div>
        </div>
    </div>

    @endsection

    @section('view_volunteer_group_signups_script')
    <!-- pusher js -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    {{-- select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- datatable --}}
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.js"></script>


    {{--sweetalert--}}
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script> -->

    <script>
        // $(document).on('click', '.nav-volunteers-tab', function(e) 
        // {
        //     e.preventDefault();
        //     $('.nav-group-homes-tab').removeClass("active");
        //     $('.nav-volunteers-tab').addClass("active");
        //     $('#volunteersTab').addClass("active show");
        //     $('#groupHomesTab').removeClass("active show");
        // })

        // $(document).on('click', '.nav-group-homes-tab', function(e) 
        // {
        //     e.preventDefault();
        //     $('.nav-volunteers-tab').removeClass("active");
        //     $('.nav-group-homes-tab').addClass("active");
        //     $('#volunteersTab').removeClass("active show");
        //     $('#groupHomesTab').addClass("active show");
        // }) 

        // $(document).on('click', '.close-modal', function(e) {
        //     e.preventDefault();



        //     // $('.update-userdetails-btn').prop("disabled", false).css("cursor", "pointer");
        // });

        $(document).on('click', '#clearSearch', function(e) {
            e.preventDefault();

            var table = $('#volunteerGrouphomeUsersTable').DataTable();
            table.search('').draw();

            $('.dataTables_filter input').val('');

        });

        $('#newVolunteerGroupSignupModal').on('show.bs.modal', function() {
            $('#selectUser').select2('destroy').select2({
                dropdownParent: $(this),
                minimumResultsForSearch: 0
            });
        });

        $('#editVolunteerGroupSignupModal').on('show.bs.modal', function() {
            $('#selectEditUser').select2('destroy').select2({
                dropdownParent: $(this),
                minimumResultsForSearch: 0
            });
        });

        $(document).ready(function() {

            document.getElementById('backToBottom').style.display = "none";

            // show the realtime updated ticket
            Pusher.logToConsole = true;

            var pusherAppKey = '{{ env('
            PUSHER_APP_KEY ') }}';

            var pusher = new Pusher(pusherAppKey, {
                cluster: 'eu'
            });

            $('.admin-select2').select2();

            // reload tickets table
            var channel = pusher.subscribe('updated-volunteergrouphomes');
            channel.bind('updated-volunteergrouphomes-data', function(data) {

                console.log(data);

                $('#volunteerSignupsTable').DataTable().ajax.reload(null, false);
                // $('#groupHomesSignupsTable').DataTable().ajax.reload(null, false);

                $('#totalVolunteerSignupsCount').text(" ").text(data.updatedVolunteerGroupHomesData.volunteer_signups_count);

                $('#totalVolunteersServedCount').text(" ").text(data.updatedVolunteerGroupHomesData.volunteer_signups_served);

                $('#totalVolunteerRemainingCount').text(" ").text(data.updatedVolunteerGroupHomesData.volunteer_signups_unserved);

                // $('#totalGroupHomeSignupsCount').text(" ").text(data.updatedVolunteerGroupHomesData.group_homes_signups_count);

                // $('#totalGroupHomesServedCount').text(" ").text(data.updatedVolunteerGroupHomesData.group_homes_signups_served);

                // $('#totalGroupHomesRemainingCount').text(" ").text(data.updatedVolunteerGroupHomesData.group_homes_signups_unserved);
            });

            $('#volunteerGrouphomeUsersTable').removeClass('table-striped');

            var volunteersGrouphomeUsers = $('#volunteerGrouphomeUsersTable').DataTable({
                stripeClasses: [],
                lengthMenu: [10, 25, 50, 100, {
                    label: 'All',
                    value: -1
                }],
                // "sDom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
                processing: true,
                serverside: true,
                responsive: true,
                "ajax": {
                    "url": "/admin/volunteer-group-home-users",
                    "type": "get",
                },
                columns: [{
                        data: 'first_name',
                        name: 'first_name',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'last_name',
                        name: 'last_name',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true
                    }
                ]
            });

            // Remove .table-striped class
            $('#volunteerSignupsTable').removeClass('table-striped');

            var volunteerSignupsTickets = $('#volunteerSignupsTable').DataTable({
                stripeClasses: [],
                columnDefs: [{
                    //"defaultContent": "-",
                    // "targets": "_all",
                    // "className": "text-center"
                }],
                lengthMenu: [10, 25, 50, 100, {
                    label: 'All',
                    value: -1
                }],
                "sDom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
                processing: true,
                serverside: true,
                responsive: true,
                "ajax": {
                    "url": "/admin/volunteer-signups",
                    "type": "get",
                },
                columns: [{
                        data: 'ticket_number',
                        name: 'ticket_number',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'name',
                        name: 'name',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'served',
                        name: 'served',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true
                    }
                ]
            });

        });

        $(document).on('click', '.new-volunteer-group-user', function(e) {
            e.preventDefault();

            $('#newVolunteerGroupUserModal').modal('toggle');

            var btnTypeAttr = $(this).attr('button-type');

            $('#newVolunteerGroupUserTItle').text('New User Details');

            $('#newVolunteerGroupUserBtn').prop("disabled", false).css("cursor", "pointer");

            $('.first-name,.last-name,.user-id,.volunteer-group-btn-type').val('');

            $('.volunteer-group-btn-type').val(btnTypeAttr);

            $('.error-list').html(" ").addClass('d-none');
        });

        $(document).on('click', '.update-volunteer-group-user', function(e) {
            e.preventDefault();

            var id = $(this).attr('user-id');

            var fName = $(this).attr('first-name');

            var lName = $(this).attr('last-name');
            var caseNumbers = $(this).attr('case-numbers');
            var volunteerIds = $(this).attr('volunteer-ids');

            var btnTypeAttr = $(this).attr('button-type');

            $('#newVolunteerGroupUserModal').modal('toggle');

            $('#newVolunteerGroupUserTItle').text('Edit User Details');

            $('#newVolunteerGroupUserBtn').prop("disabled", false).css("cursor", "pointer");

            $('.first-name,.last-name,.user-id,.volunteer-group-btn-type').val('');

            $('.volunteer-group-btn-type').val(btnTypeAttr);

            $('.user-id').val(id);

            $('.first-name').val(fName);

            $('.last-name').val(lName);

            $('.error-list').html(" ").addClass('d-none');
            // Show case numbers section for edit mode
            $('.case-numbers-section').show();

                 // Reset selected case numbers
            selectedCaseNumbers = [];
            
            // Display case numbers in grid format
            if (caseNumbers && caseNumbers.trim() !== '') {
                var caseNumbersArray = caseNumbers.split(', ');
                var gridHtml = '';
                
                caseNumbersArray.forEach(function(caseNumber, index) {
                    if (caseNumber.trim() !== '') {
                        gridHtml += '<div class="case-number-item" data-case-number="' + caseNumber.trim() + '">';
                        gridHtml += caseNumber.trim();
                        gridHtml += '</div>';
                    }
                });
                
                $('.case-numbers-display').html(gridHtml);
            } else {
                $('.case-numbers-display').html('<span class="no-case-numbers-message">No case numbers available</span>');
            }
        });
// Handle case number selection
        $(document).on('click', '.case-number-item', function() {
            var caseNumber = $(this).data('case-number');
            
            if ($(this).hasClass('selected')) {
                // Deselect case number
                $(this).removeClass('selected');
                selectedCaseNumbers = selectedCaseNumbers.filter(function(num) {
                    return num !== caseNumber;
                });
            } else {
                // Select case number
                $(this).addClass('selected');
                if (selectedCaseNumbers.indexOf(caseNumber) === -1) {
                    selectedCaseNumbers.push(caseNumber);
                }
            }
            
            console.log('Selected case numbers:', selectedCaseNumbers);
        });

        $(document).on('click', '#newVolunteerGroupUserBtn', function(e) {
            e.preventDefault();

            $(this).prop("disabled", true).css("cursor", "not-allowed");

            var user_id = $('.user-id').val();

            var first_name = $('.first-name').val();

            var last_name = $('.last-name').val();

            var url = '/admin/manage-volunteer-group-home-users';

            var btnType = $('.volunteer-group-btn-type').val();

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            $.ajax({
                url: url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: function() {
                    var data = new FormData();
                    data.append('user_id', user_id);
                    data.append('first_name', first_name);
                    data.append('last_name', last_name);
                    data.append('btnType', btnType);
                    data.append('_token', csrf);
                    return data;
                    console.log(data)
                }(),
                success: function(response) {

                    console.log(response);

                    $('#newVolunteerGroupUserBtn').prop("disabled", false).css("cursor", "pointer");

                    if (response.status == 200) {

                        $('#newVolunteerGroupUserModal').modal('hide');
                        swal(response.message);

                        if (btnType == 'add-new') {
                            $('#selectUser')
                                .append('<option value="' + response.id + '">' + first_name + ' ' + last_name + '</option>')
                                .val(response.id)
                                .trigger('change');
                        }

                        $('#volunteerGrouphomeUsersTable').DataTable().ajax.reload(null, false);

                    } else if (response.status == 405) {
                        $('.error-list').html(" ").removeClass('d-none');
                        $.each(response.message, function(key, err_value) {
                            $('.error-list').append('<li>' + err_value + '</li>');
                        })
                    } else if (response.status == 500) {
                        alert(response.message);
                    }

                }
            });
        });

        $(document).on('click', '.delete-volunteer-group-user', function(e) {
            e.preventDefault();

            var userId = $(this).attr('user-id');

            $('#confirmUserDeleteModal').modal('toggle');

            $('.continue-user-delete-btn').prop("disabled", false).css("cursor", "pointer");

            $('.continue-user-delete-btn').attr('user-id', userId);
        });

        $(document).on('click', '.continue-user-delete-btn', function(e) {
            e.preventDefault();

            $(this).prop("disabled", true).css("cursor", "not-allowed");

            var userId = $(this).attr('user-id');

            var url = '/admin/delete-volunteer-group-user';

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            $.ajax({
                url: url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: function() {
                    var data = new FormData();
                    data.append('userId', userId);
                    data.append('_token', csrf);
                    return data;
                    console.log(data)
                }(),
                success: function(response) {
                    $('.continue-user-delete-btn').removeAttr('user-id');

                    $('#confirmUserDeleteModal').modal('hide');

                    if (response.status == 200) {

                        $('#volunteerGrouphomeUsersTable').DataTable().ajax.reload(null, false);

                    } else if (response.status == 404) {

                        swal(response.message);
                    }
                }
            });
        });


        $(document).on('click', '.new-volunteer-group-signup', function(e) {
            e.preventDefault();

            var signUpType = $(this).attr('sign-up-type');

            $('#newVolunteerGroupSignupModal').modal('toggle');

            $('.volunteer-group-user-name,.volunteer-group-amount,.volunteer-group-type,#selectUser').val('');

            $('.error-list').html(" ").addClass('d-none');

            $('.newVolunteerGroupSignupBtn').prop("disabled", false).css("cursor", "pointer");

            if (signUpType == 1) {
                $('.volunteer-group-type').val(1);

                $('#newVolunteerGroupSignupTItle').html('').html('New Volunteer');

            } else {

                $('.volunteer-group-type').val(2);
                $('#newVolunteerGroupSignupTItle').html('').html('New Group Home');

            }

            var url = '/admin/get-users-without-tickets';

            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    var users = response.users_ids;

                    console.log(users);

                    $('#selectUser').html('');

                    $.each(users, function(i, item) {
                        $('#selectUser').append('<option value="' + users[i].id + '">' + users[i].first_name + ' ' + users[i].last_name + '</option>');
                    });

                    $('#selectUser').append('<option disabled selected>Select</option>');

                    // $('.select-user-holder .select2 .selection .select2-selection .select2-selection__rendered').html('').append('<li class="select2-selection__choice" title="Select" data-select2-id="21"><span class="select2-selection__choice__remove" role="presentation"></span>Select</li>');
                }
            });
        });

        $(document).on('click', '.update-volunteer-group-details', function(e) {
            e.preventDefault();

            var id = $(this).attr('volunteer-group-id');

            var url = '/admin/get-volunteer-group-signup-details/' + id;

            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    console.log(response);

                    if (response.status == 404) {
                        swal(response.message);

                    } else if (response.status == 200) {
                        var userID = response.data.userID;

                        var fName = response.data.first_name;

                        var lName = response.data.last_name;

                        var amount = response.data.amount;

                        var users = response.users_ids;

                        $('#editVolunteerGroupSignupModal').modal('toggle');

                        $('.editVolunteerGroupSignupBtn').prop("disabled", false).css("cursor", "pointer");

                        $('.edit-volunteer-group-amount').val('');

                        $('.edit-volunteer-group-id').val(id);

                        $('#selectEditUser').html('');

                        $.each(users, function(i, item) {
                            $('#selectEditUser').append('<option value="' + users[i].id + '">' + users[i].first_name + ' ' + users[i].last_name + '</option>');
                        });

                        $('#selectEditUser').append('<option value="' + userID + '">' + fName + ' ' + lName + '</option>');

                        $('#selectEditUser').val(userID).prop('selected', true).trigger('change');

                        $('.edit-volunteer-group-amount').val(amount);

                        $('.error-list').html(" ").addClass('d-none');
                    }
                }
            });

        });

        // $(document).on('click', '.update-volunteer-group-details', function(e) {
        //     e.preventDefault();

        //     var id = $(this).attr('volunteer-group-id');

        //     var url = '/admin/get-volunteer-group-signup-details/'+id;

        //     $.ajax({
        //         url: url,
        //         method: 'GET',
        //         success: function(response)
        //         {
        //             console.log(response);

        //             if(response.status == 404)
        //             {
        //                 swal(response.message);

        //             } 
        //             else if (response.status == 200)
        //             {
        //                 var first_name = response.data.first_name;
        //                 var userID = response.data.userID;
        //                 var last_name = response.data.last_name;
        //                 var amount = response.data.amount;

        //                 $('#newVolunteerGroupSignupModal').modal('toggle');

        //                 $('.newVolunteerGroupSignupBtn').prop("disabled", false).css("cursor", "pointer");

        //                 $('.volunteer-group-user-name,.volunteer-group-amount,.volunteer-group-type,#selectUser').val('');

        //                 $('.volunteer-group-id').val(id);

        //                 $('#selectUser').val(userID).select2();

        //                 $('.volunteer-group-amount').val(amount);

        //                 $('.error-list').html(" ").addClass('d-none');
        //             }
        //         }
        //     });

        // });

        $(document).on('click', '.editVolunteerGroupSignupBtn', function(e) {
            e.preventDefault();

            $(this).prop("disabled", true).css("cursor", "not-allowed");

            var volunteer_group_id = $('.edit-volunteer-group-id').val();

            var amount = $('.edit-volunteer-group-amount').val();

            var user_name = JSON.stringify($('#selectEditUser').val());

            var user_id = $('#selectEditUser').val();

            var url = '/admin/manage-volunteer-group-signup';

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            $.ajax({
                url: url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: function() {
                    var data = new FormData();
                    data.append('amount', amount);
                    data.append('user_name', user_name);
                    data.append('volunteer_group_id', volunteer_group_id);
                    data.append('_token', csrf);
                    return data;
                    console.log(data)
                }(),
                success: function(response) {

                    console.log(response);

                    $('.editVolunteerGroupSignupBtn').prop("disabled", false).css("cursor", "pointer");

                    if (response.status == 200) {

                        $('#editVolunteerGroupSignupModal').modal('hide');

                    } else if (response.status == 405) {
                        $('.error-list').html(" ").removeClass('d-none');
                        $.each(response.message, function(key, err_value) {
                            $('.error-list').append('<li>' + err_value + '</li>');
                        })
                    }

                }
            });
        });

        $(document).on('click', '.newVolunteerGroupSignupBtn', function(e) {
            e.preventDefault();

            $(this).prop("disabled", true).css("cursor", "not-allowed");

            var volunteer_group_id = $('.volunteer-group-id').val();

            var user_name = JSON.stringify($('#selectUser').val());

            var user_id = $('#selectUser').val();

            var amount = $('.volunteer-group-amount').val();

            var type_id = $('.volunteer-group-type').val();

            var url = '/admin/manage-volunteer-group-signup';

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            $.ajax({
                url: url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: function() {
                    var data = new FormData();
                    data.append('user_name', user_name);
                    data.append('amount', amount);
                    data.append('type_id', type_id);
                    data.append('user_id', user_id);
                    data.append('volunteer_group_id', volunteer_group_id);
                    data.append('_token', csrf);
                    return data;
                    console.log(data)
                }(),
                success: function(response) {

                    console.log(response);

                    $('.newVolunteerGroupSignupBtn').prop("disabled", false).css("cursor", "pointer");

                    if (response.status == 200) {

                        $('#newVolunteerGroupSignupModal').modal('hide');

                        $('#editVolunteerGroupSignupModal').modal('hide');

                        $('#selectUser option[value="' + user_id + '"]').remove();

                        $('#selectUser').trigger('change');

                    } else if (response.status == 405) {
                        $('.error-list').html(" ").removeClass('d-none');
                        $.each(response.message, function(key, err_value) {
                            $('.error-list').append('<li>' + err_value + '</li>');
                        })
                    }

                }
            });
        });

        $(document).on('click', '.delete-volunteer-group-details', function(e) {
            e.preventDefault();

            var signUpId = $(this).attr('volunteer-group-id');

            $('#confirmSignUpDeleteModal').modal('toggle');

            $('.continue-signup-delete-btn').prop("disabled", false).css("cursor", "pointer");

            $('.continue-signup-delete-btn').attr('signup-id', signUpId);
        });

        $(document).on('click', '.continue-signup-delete-btn', function(e) {
            e.preventDefault();

            $(this).prop("disabled", true).css("cursor", "not-allowed");

            var signUpId = $(this).attr('signup-id');

            var url = '/admin/delete-volunteer-group-signup';

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            $.ajax({
                url: url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: function() {
                    var data = new FormData();
                    data.append('signUpId', signUpId);
                    data.append('_token', csrf);
                    return data;
                    console.log(data)
                }(),
                success: function(response) {
                    console.log(response);

                    $('#confirmSignUpDeleteModal').modal('hide');

                    swal(response.message);

                    if (response.status == 200) {
                        console.log(response.user.id);

                        $('#selectUser')
                            .append('<option value="' + response.user.id + '">' + response.user.first_name + ' ' + response.user.last_name + '</option>')
                            .val(response.user.id)
                            .trigger('change');

                        // $('#selectUser').val('').append('option val="'+response.user.id+'">'+response.user.first_name+' '+response.user.last_name+'</option>').select2();
                    }
                }
            });
        });


        $(document).on('click', '.is-served-btn', function(e) {
            e.preventDefault();

            var ticketId = $(this).attr('ticket-id');

            var servedInStatus = $(this).text();

            if (servedInStatus == 'No') {
                $('.is-served-btn').not(this).prop("disabled", true).css("cursor", "not-allowed");

                var url = '/admin/served-in-status';

                var csrf = document.querySelector('meta[name="csrf-token"]').content;

                $.ajax({
                    url: url,
                    method: 'POST',
                    processData: false,
                    contentType: false,
                    data: function() {
                        var data = new FormData();
                        data.append('ticketId', ticketId);
                        data.append('_token', csrf);
                        return data;
                        console.log(data)
                    }(),
                    success: function(response) {
                        console.log(response);

                        $('.is-served-btn').not(this).prop("disabled", false).css("cursor", "pointer");

                    }
                });
            } else if (servedInStatus == 'Yes') {
                $('#confirmUserServingModal').modal('toggle');

                $('.new-serve-status').text('No?');

                $('.continue-serve-confirmation-btn').attr('ticket-id', ticketId);
            }
        });

        $(document).on('click', '.continue-serve-confirmation-btn', function(e) {
            e.preventDefault();

            var ticketId = $(this).attr('ticket-id');

            $('.is-served-btn').not(this).prop("disabled", true).css("cursor", "not-allowed");

            var url = '/admin/served-in-status';

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            $.ajax({
                url: url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: function() {
                    var data = new FormData();
                    data.append('ticketId', ticketId);
                    data.append('_token', csrf);
                    return data;
                    console.log(data)
                }(),
                success: function(response) {
                    console.log(response);

                    $('.is-served-btn').not(this).prop("disabled", false).css("cursor", "pointer");

                    $('#confirmUserServingModal').modal('hide');
                }
            });
        });

        // $(document).on('click', '.close-modal', function(e) {
        //     e.preventDefault();

        //     $('.error-list').html(" ").addClass('d-none');


        // });
    </script>
    @endsection