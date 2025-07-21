@extends('admin.layout')
@section('content')
@section('view_volunteer_group_signups_styles')
{{-- datatables --}}
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css" />

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{{--sweetalert--}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

{{-- select2 --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Styling the select element inside the DataTable */
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
    <!-- Modal -->
    <div class="modal fade" id="recordModal" tabindex="-1" aria-labelledby="recordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="recordForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="recordModalLabel">Add/Edit Volunteer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="recordId">
                        <div class="mb-3">
                            <div class="mb-3">
                                <label for="volunteer_id" class="form-label">Select Volunteer</label>
                                <select id="volunteer_id" name="volunteer_id" class="form-select" required>
                                    <option value="">-- Choose Volunteer --</option>
                                    @foreach($volunteers as $volunteer)
                                    <option value="{{ $volunteer->id }}">{{ $volunteer->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                        <div class="mb-3">
                            <label for="startTime" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="startTime" required name="start_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="endTime" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="endTime" required name="end_time" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Record</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div>
        <h3 style="text-align: center;"> Volunteer Check In</h3>
        <table id="volunteerSignupsTable" class="table table-bordered" style="width:100%;">
            <thead class="bg-info text-white">
                <tr>
                    <td>Name</td>
                    <td>Start Time</td>
                    <td>End Time </td>
                    <td>Duration</td>
                    <td>Action</td>
                </tr>
            </thead>
        </table>

        <span class="new-volunteer-group-signup" sign-up-type="1">Add New Record</span>

        <div class="volunteer-group-signups-dashboard-holder">
            <div class="details-holder" style="background-color: rgb(204, 102, 255)">
                <p style="font-size: 30px;">Number of Volunteer</p>
                <p style="font-size:70px; margin-top: -30px;" id="totalVolunteerSignupsCount">Loding..</p>
            </div>
            <div class="details-holder" style="background-color:rgb(51, 204, 255)">
                <p style="font-size: 30px;">Total Volunteers Hours</p>
                <p style="font-size:70px; margin-top: -30px;" id="totalVolunteersServedCount">Loding..</p>
            </div>

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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function updateDashboardCounts() {
        $.ajax({
            url: '/admin/volunteer-check-in',
            type: 'GET',
            data: {
                get_counts: true,
                _: new Date().getTime() // ⬅ Prevents caching
            },
            success: function(response) {
                $('#totalVolunteerSignupsCount').text(response.totalVolunteers);
                $('#totalVolunteersServedCount').text(response.totalHours);
            },
            error: function() {
                console.log('Error loading counts');
            }
        });
    }
    $(document).ready(function() {
        $('#volunteerSignupsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/admin/volunteer-check-in',
                type: 'GET'
            },
            columns: [{
                    data: 'first_name',
                    name: 'first_name'
                },
                {
                    data: 'start_time',
                    name: 'start_time'
                },
                {
                    data: 'end_time',
                    name: 'end_time'
                },
                {
                    data: 'duration',
                    name: 'duration'
                },
                {
                    data: 'id',
                    render: function(data, type, row) {
                        return ` 
                            <div class="bg-warning datatable-dropdown">
                    <a class="btn btn btn-sm  text-dark update-volunteer-group-details" onclick="editRecord(${data})">Edit</a>
                    <div class="dropdown">
                        <button class="btn dropdown" fdprocessedid="hgm4l">
                            <i class="fa-solid fa-caret-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <a class="dropdown-item text-dark delete-volunteer-group-details" onclick="deleteRecord(${data})">Delete</a>
                        </div>
                    </div>
                </div>
                            
                            
                            
                            
                            
                            
                            `;
                    }
                },
            ]
        });
        // NEW CODE: Function to update dashboard counts


        // NEW CODE: Update counts on page load
        updateDashboardCounts();

        // NEW CODE: Optional - Update counts every 30 seconds
        setInterval(updateDashboardCounts, 30000);

    });
    // Open modal
    $('.new-volunteer-group-signup').on('click', function() {
        $('#recordForm')[0].reset();
        $('#recordId').val('');
        $('#recordModalLabel').text('Add Volunteer');
        new bootstrap.Modal(document.getElementById('recordModal')).show();
    });
    $('#recordForm').submit(function(e) {
        e.preventDefault();

        let formData = $(this).serialize();
        let id = $('#recordId').val();
        let url = id ? `/admin/volunteer-check-in/${id}` : '/admin/volunteer-check-in';
        let method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function() {
                bootstrap.Modal.getInstance(document.getElementById('recordModal')).hide();
                updateDashboardCounts();

                $('#volunteerSignupsTable').DataTable().ajax.reload();
            },
            error: function() {
                alert('Failed to save record.');
            }
        });
    });

    function editRecord(id) {
        $.get(`/admin/volunteer-check-in/${id}/edit`, function(data) {
            $('#recordId').val(data.id);
            $('#volunteer_id').val(data.id); // or load into dropdown if needed
            $('#startTime').val(data.start_time);
            $('#endTime').val(data.end_time);
            $('#recordModalLabel').text('Edit Volunteer');
            new bootstrap.Modal(document.getElementById('recordModal')).show();
        });
    }

    function deleteRecord(id) {
        if (confirm('Are you sure you want to delete this record?')) {
            $.ajax({
                url: `/admin/volunteer-check-in/${id}`,
                type: 'DELETE',
                success: function(response) {
                    alert(response.message);
                    updateDashboardCounts();
                    $('#volunteerSignupsTable').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    alert('Failed to delete record.');
                    console.error(xhr.responseText);
                }
            });
        }
    }
</script>

@endsection