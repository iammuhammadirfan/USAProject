@extends('admin.layout')
            @section('content')
            @section('activity_log_styles')
                {{-- datatables --}}
            <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css"/>
            <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.dataTables.min.css"/>
            <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css"/>
            <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.bootstrap.css"/>

            <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">

            {{-- select2 --}}
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

            <style>

                    /* Styling the select element inside the DataTable */
                    .dt-length label {
                    margin-left: 10px;
                    }

                    .form-group 
                    {
                        width: 30%;
                        text-align: center;
                    }

                    /* .text-center
                    {
                        text-align: center;
                    } */

                    /* .number-control-div
                    {
                        display: flex;
                        justify-content: space-around;
                        align-items: center;
                        color: red;
                        background-color: black;
                        height: 600px;
                    } */

                    /* .ticket-served
                    {
                        height: inherit;
                        display: flex;
                        justify-content: space-around;
                        align-items: center;
                        font-size: 460px;
                        text-align: center;
                    }

                    .number-control-settings
                    {
                        display: flex;
                        justify-content: space-between;
                        margin-top: 20px;
                        align-items: center;
                        height: 50px;
                    } */

                    /* .number-control-settings a{
                        background: rgb(252, 229, 214);
                        color: black;
                        font-size: 20px;
                        border: none;
                        outline: none;
                    }
                    
                    #confirmUserCheckinModal p
                    {
                        text-align:center;
                    } */
                    
                    .dt-search
                    {
                    float:right;
                    }
                    
                    /* Dropdown css code */
                /* .datatable-dropdown
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
                        background-color: #ffc107;
                    }

                    .dropdown-content {
                    display: none;
                    position: absolute;
                    left: -100px;
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

                    #barcodeScanTicketModal .modal-body table{
                        width:100%; text-align:center;
                    }

                    #barcodeScanTicketModal .modal-body table thead tr th{
                        border:1px solid rgb(17, 15, 15); padding:2px;
                    }

                    #barcodeScanTicketModal .modal-body table tr{
                        border:1px solid rgb(17, 15, 15);font-size:15px; font-weight:400;
                    }

                    #barcodeScanTicketModal .modal-body table tr td{
                        border:1px solid rgb(17, 15, 15); padding:3px;
                    }

                    #singleTicketDisplayTable
                    {
                        width:100%; text-align:center;
                    }

                    #singleTicketDisplayTable thead tr th,#singleTicketDisplayTable tbody tr td
                    {
                        border:2px solid rgb(17, 15, 15); padding:5px;  
                    }

                    #singleTicketDisplayTable tbody tr
                    {
                        border:2px solid rgb(17, 15, 15); font-size:20px; font-weight:400; color:black;
                    }

                    .highlight-scanned-row
                    {
                        background-color: rgb(173, 216, 230);
                    } */
                    
                    @media(max-width:992px)
                    {
                        .datatable-dropdown {
                            width: 100%;
                        }
                        
                        /* .number-control-div {
                            display: flex;
                            justify-content: space-around;
                            align-items: center;
                            color: red;
                            background-color: black;
                            height: 400px;
                        }

                        .ticket-served {
                            height: inherit;
                            font-size: 330px;
                        } */
                    }

                    /* @media(max-width:768px)
                    {
                        .ticket-served {
                            height: inherit;
                            font-size: 270px;
                        }
                    } */

                    /* @media(max-width:450px)
                    {
                        .ticket-served {
                            height: inherit;
                            font-size: 210px;
                        }
                    }

                    @media(max-width:380px)
                    {
                        .ticket-served {
                            height: inherit;
                            font-size: 160px;
                        }
                    } */
            </style>
            @stop

                <!-- <nav class="mt-5">
                    <div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
                        <button class="nav-link active nav-sign-up-tab" data-bs-toggle="tab" data-bs-target="#signUpTab" type="button" role="tab" aria-controls="signUpTab" aria-selected="true">View Sign-ups</button>
                        <button class="nav-link nav-memos-tab" data-bs-toggle="tab" data-bs-target="#navMemos" type="button" role="tab" aria-controls="navMemos" aria-selected="false">Number Control</button>
                    </div>
                </nav>
                <div class="tab-content p-3 border bg-light" id="nav-tabContent">
                    <div class="tab-pane fade active show" id="signUpTab" role="tabpanel">
                        <div class="row">
                            <div class="col-md-12 col-lg-12"> -->
                                <div style="display: flex; justify-content:space-around; align-items:center;padding:10px;">
                                    <h4>Activity Log</h4>
                                </div>
                
                        <!-- <div class="row">
                            <div class="col-md-12 col-lg-12"> -->
                                <div style="display: flex;align-items: center;gap: 20px; padding:5px;">
                                    <div class="form-group">
                                        <select style="width:100%;" class="front-select2 text-dark staff-id">
                                            <?php 
                                                $staffs=DB::table('users')
                                                ->where(['role'=>3,'is_active'=>1])
                                                ->select('id','first_name','last_name')
                                                ->get();
                                            ?>
                                            
                                            <option selected value=" ">Choose Staff</option>
                                            @foreach($staffs as $staff)
                                                <option value="{{ $staff->id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="visually-hidden" for="activityDate">Date</label>
				                        <input type="text" autocomplete="off" class="form-control w-100" id="activityDate" placeholder="Date of Activity (MM/DD/YYYY)" />
                                    </div>
                                </div>
                                <table id="staffActivityLog" class="table table-bordered" style="width:100%;">
                                    <thead class="bg-info text-white">
                                    <tr>
                                        <td>Date</td>
                                        <td>Time</td>
                                        <td>Staff Name</td>
                                        <td>Activity</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            <!-- </div>
                        </div>
                    </div> -->
                    <!-- <div class="tab-pane fade" id="navMemos" role="tabpanel">
                        <div class="row mt-5">
                            <div class="col-md-10 col-lg-10">
                                <div class="number-control-div">
                                    <p class="ticket-served"></p>
                                </div>
                                <div class="number-control-settings">
                                    <div class="control-settings-div">
                                        <a class="btn btn-sm control-number" control-number="-1">-1</a>
                                        <a class="btn btn-sm control-number" control-number="-2">-2</a>
                                        <a class="btn btn-sm control-number" control-number="-3">-3</a>
                                        <a class="btn btn-sm control-number" control-number="-4">-4</a>
                                        <a class="btn btn-sm control-number" control-number="-5">-5</a>
                                        <a class="btn btn-sm control-number" control-number="-10">-10</a>
                                    </div>
                                    <div class="control-settings-div">
                                    </div>
                                    <div class="control-settings-div">
                                        <a class="btn btn-sm control-number" control-number="1">+1</a>
                                        <a class="btn btn-sm control-number" control-number="2">+2</a>
                                        <a class="btn btn-sm control-number" control-number="3">+3</a>
                                        <a class="btn btn-sm control-number" control-number="4">+4</a>
                                        <a class="btn btn-sm control-number" control-number="5">+5</a>
                                        <a class="btn btn-sm control-number" control-number="10">+10</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <nav>
                    <div class="nav nav-tabs mb-3" id="nav-tab-bottom" role="tablist">
                        <button class="nav-link active nav-sign-up-tab" data-bs-toggle="tab" data-bs-target="#signUpTab" type="button" role="tab" aria-controls="signUpTab" aria-selected="true">View Sign-ups</button>
                        <button class="nav-link nav-memos-tab" data-bs-toggle="tab" data-bs-target="#navMemos" type="button" role="tab" aria-controls="navMemos" aria-selected="false">Number Control</button>
                    </div>
                </nav> -->


            @endsection

            @section('activity_log_script')
                
                {{-- datatable --}}
                <script type="text/javascript" src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script> 
            <script type="text/javascript" src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.js"></script>


            {{-- timepicker --}}
            <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>

                {{-- moment js --}}
                <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>

                {{-- select2 --}}
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

            {{-- jquery ui plugins --}}
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>

                <script>
                    // $(document).on('click', '.nav-sign-up-tab', function(e) 
                    // {
                    //     e.preventDefault();
                    //     $('.nav-memos-tab').removeClass("active");
                    //     $('.nav-sign-up-tab').addClass("active");
                    //     $('#signUpTab').addClass("active show");
                    //     $('#navMemos').removeClass("active show");
                    // })
                        
                    // $(document).on('click', '.nav-memos-tab', function(e) 
                    // {
                    //     e.preventDefault();
                    //     $('.nav-sign-up-tab').removeClass("active");
                    //     $('.nav-memos-tab').addClass("active");
                    //     $('#signUpTab').removeClass("active show");
                    //     $('#navMemos').addClass("active show");
                    // })

                    // $(document).on('click', '.control-number', function(e) {
                    //     e.preventDefault();

                    //     var adjusted_value=$(this).attr('control-number');

                    //     var current_served=$('.ticket-served').text();

                    //     if($.isNumeric(current_served))
                    //     {
                    //         var current_ticket = current_served;
                    //     } else {
                    //         var current_ticket = 0;
                    //     }

                    //     var csrf = document.querySelector('meta[name="csrf-token"]').content;

                    //     var url = '/admin/adjust-served-ticket';

                    //     $.ajax({
                    //         url: url,
                    //         method: 'POST',
                    //         processData: false,
                    //         contentType: false,
                    //         data: function(){
                    //             var data = new FormData();
                    //             data.append('adjusted_value',adjusted_value);
                    //             data.append('current_ticket',current_ticket);
                    //             data.append('_token',csrf);
                    //             return data;
                    //             console.log(data)
                    //         }(),
                    //         success: function(response) {

                    //             if (response.status == 200)
                    //             {
                    //                 $('.ticket-served').text(response.ticket_number);
                    //             }
                    //         }
                    //     });
                    // });

                    $(document).ready(function() 
                    {
                        document.getElementById('backToBottom').style.display = "none";

                        $( function() {
                            $( "#activityDate").datepicker(
                                {
                                    dateFormat: 'mm/dd/yy'
                                }
                            );
                        });
                        
                        $('.staff-id').select2();

                        $('#staffActivityLog').DataTable({
                            stripeClasses: [],
                            lengthMenu:[10,25,50,100,{ label: 'All',value: -1 }],
                            "sDom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
                            processing:true,
                            serverside:true,
                            responsive:true,
                            "ajax": {
                                "url": "/admin/staff-activity-log",
                                "type": "get",
                            },
                            columns: [
                                {  data: 'date',name:'date',orderable:true,searchable:true},
                                {  data: 'time',name:'time',orderable:false,searchable:false },
                                {  data: 'name',name:'name',orderable:true,searchable:true },
                                {  data: 'activity_id',name:'activity_id',orderable:true,searchable:true},
                            ],
                        });

                        
                    });

                    function filter_activity()
                        {
                            const staff_id=$('.staff-id').val();
                            const activity_date=$('#activityDate').val();
                        
                            const url = '/admin/staff-activity-log';

                            $.ajax({
                                url: url,
                                type:'get',
                                data:{
                                    staff_id: staff_id,
                                    activity_date: activity_date
                                },
                                success: function(response) 
                                {

                                    // staffActivityLog.ajax.reload(null, false);
                                    $('#staffActivityLog').DataTable().destroy();
						            $('#staffActivityLog').removeClass('table-striped');
						
                                    const staff_id=$('.staff-id').val();
                                    const activity_date=$('#activityDate').val();
                                
                                    $('#staffActivityLog').removeClass('d-none');
                                    
                                    $('#staffActivityLog').DataTable({
                                        stripeClasses: [],
                                        lengthMenu:[10,25,50,100,{ label: 'All',value: -1 }],
                                        "sDom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
                                        processing:true,
                                        serverside:true,
                                        responsive:true,
                                        "ajax": {
                                            "url": "/admin/staff-activity-log",
                                            "type": "get",
                                            "data": {
                                                "staff_id": staff_id,
                                                "activity_date": activity_date
                                            },
                                        },
                                        columns: [
                                            {  data: 'date',name:'date',orderable:true,searchable:true},
                                            {  data: 'time',name:'time',orderable:false,searchable:false },
                                            {  data: 'name',name:'name',orderable:true,searchable:true },
                                            {  data: 'activity_id',name:'activity_id',orderable:true,searchable:true},
                                        ],
                                    });

                                    $('#staffActivityLog').removeClass('table-striped');
                                    
                                }
                            });
                        }

                    $(document).on('change', '#activityDate,.staff-id', function(e)
                    {
                        e.preventDefault();
                        
                        filter_activity();
                    });

                </script>
            @endsection