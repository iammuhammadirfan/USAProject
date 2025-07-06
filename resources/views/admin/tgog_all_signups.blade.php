@extends('admin.layout')
            @section('content')
            @section('view_recipients_styles')
                {{-- datatables --}}
            <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css"/>
            <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.dataTables.min.css"/>
            <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css"/>
            <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.bootstrap.css"/>


            {{-- alertify --}}
            <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>
            <!-- Default theme -->
            <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/default.min.css"/>
            <!-- Semantic UI theme -->
            <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/semantic.min.css"/>

            {{--sweetalert--}}
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

            {{-- Font Awesome for spinner icons --}}
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

            <style>

                    /* Styling the select element inside the DataTable */
                    .dt-length label {
                    margin-left: 10px;
                    }

                    .text-center
                    {
                        text-align: center;
                    }

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

                    .number-control-settings a{
                        background: rgb(252, 229, 214);
                        color: black;
                        font-size: 20px;
                        border: none;
                        outline: none;
                    }
                    
                    #confirmUserCheckinModal p
                    {
                        text-align:center;
                    }
                    
                    .dt-search
                    {
                    float:right;
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
                    }
                    
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
                                    <h4 style="margin-left: 20%;">Ticket Sign Ups</h4>
                                    <a href="/admin/view-signups-table-pdf" class="btn btn-warning btn-sm btn-md btn-lg border border-dark rounded">Print</a>
                                </div>
                
                        <!-- <div class="row">
                            <div class="col-md-12 col-lg-12"> -->
                                <table id="generatedTicketsTable" class="table table-bordered" style="width:100%;">
                                    <thead class="bg-info text-white">
                                    <tr>
                                        <td>Ticket #</td>
                                        <td>Checked In</td>
                                        <td>First Name</td>
                                        <td>Last Name</td>
                                        <td>Case Number</td>
                                        <td>Created</td>
                                        <td>Created By</td>
                                        <td>Action</td>
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

                <div class="modal fade" id="confirmTicketDeleteModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-body">
                            <p>Are You sure you want to Delete the Ticket number?Click continue to proceed or cancel to keep.</p>
                    </div>
                    <div style="display: flex;
                justify-content: space-around;
                align-items: center;
                margin: 5px;">
                        <a class="btn btn-warning waves-effect border border-dark close-modal">Cancel</a>
                        <a class="btn btn-warning waves-effect border border-dark continue-ticket-delete-btn">Continue</a>
                    </div>
                    </div>
                    </div>
                </div>
                
                <div class="modal fade" id="confirmUserCheckinModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-body">
                            <p>Do you really want to change the checked-in status to <span class="new-checkin-status"></span><p>
                    </div>
                    <div style="display: flex;
                justify-content: space-around;
                align-items: center;
                margin: 5px;">
                        <a class="btn btn-warning waves-effect border border-dark close-modal">Cancel</a>
                        <a class="btn btn-warning waves-effect border border-dark continue-checkin-confirmation-btn">Continue</a>
                    </div>
                    </div>
                    </div>
                </div>

                <div class="modal fade" id="barcodeScanTicketModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                            <div class="modal-title" style="text-align: center;">
                            <h4>Check In</h4> 
                            </div>
                            <input id="multipleIdUserIdScan" type="hidden">
                            <input id="multipleIdUserTicketScan" type="hidden">
                            <input id="multipleIdUserTicketStatusScan" type="hidden">
                            <input id="previousMultipleIdUserIdScan" type="hidden">
                            <input id="barcodeScanUserId" type="hidden">
                            <input id="previousBarcodeScanUserId" type="hidden">
                            <input id="barcodeScanTicket," type="hidden">
                            <div class="modal-body">
                            </div>
                    </div>
                    </div>
                </div>

            @endsection

            @section('view_recipients_script')
                {{-- pusher js --}}
                <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
                
                {{-- datatable --}}
                <script type="text/javascript" src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script> 
            <script type="text/javascript" src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.js"></script>


            {{-- alertify --}}
            <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>

                {{-- moment js --}}
                <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>

                {{--sweetalert--}}
                <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

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

                    $(document).ready(function() {

                        document.getElementById('backToBottom').style.visibility = "visible";
                        // var currentNumberServed=$('.ticket-served').text();

                        // var url = '/get-ticket-served';

                        // $.ajax({
                        //     url: url,
                        //     method: 'get',
                        //     success: function(response) {
                        //         console.log(response);
                                
                        //         if($.isNumeric(response.ticket_number))
                        //         {
                        //             $('.ticket-served').text(response.ticket_number);
                        //         } else {
                                    
                        //             $('.ticket-served').text(response.ticket_number);
                        //         }
                                
                        //     }
                        // });

                        // Remove .table-striped class
                        $('#generatedTicketsTable').removeClass('table-striped');

                        var generatedTickets = $('#generatedTicketsTable').DataTable({
                            stripeClasses: [],
                            columnDefs: [{
                                //"defaultContent": "-",
                                // "targets": "_all",
                                // "className": "text-center"
                                }],
                            lengthMenu:[10,25,50,100,{ label: 'All',value: -1 }],
                            "sDom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
                            processing:true,
                            serverside:true,
                            responsive:true,
                            "ajax": {
                                "url": "/admin/get-tickets",
                                "type": "get",
                            },
                            columns: [
                                {  data: 'ticket',name:'ticket',orderable:true,searchable:true},
                                {  data: 'checked_in',name:'checked_in',orderable:false,searchable:false },
                                {  data: 'fname',name:'fname',orderable:true,searchable:true },
                                {  data: 'lname',name:'lname',orderable:true,searchable:true},
                                {  data: 'case_number',name:'case_number',orderable:true,searchable:true },
                                {  data: 'created',name:'created',orderable:true,searchable:true },
                                {  data: 'created_by',name:'created_by',orderable:true,searchable:true },
                                {  data: 'action',name:'action',orderable:true,searchable:true },
                            ],
                            "createdRow": function(row, data, dataIndex) {
                                console.log(data,dataIndex)
                                    // Add a CSS class to the first row
                                if (data.is_first === 0) 
                                {
                                    $(row).find('td').css({
                                            'background-color' : 'grey',
                                            'color' : 'white'
                                        });
                                    
                                    $(row).addClass('new-row');
                                    $(row).children('td').each(function (index) {
                                        console.log('Column Index:', index);
                                        // Example manipulation based on column index
                                        if (index === 0) {
                                            $(this).addClass('dt-right');
                                        }

                                        var extraIndexes=[1,2,3,4,5,6,7];
                                        if (extraIndexes.includes(index)) {
                                            $(this).addClass('dt-center');
                                        }
                                    });
                                }

                                if (data.is_first === 1) 
                                {
                                    $(row).children('td').each(function (index) 
                                    {
                                        var allIndexes=[0,1,2,3,4,5,6,7];

                                        console.log('Column Index:', index);
                                        if (allIndexes.includes(index)) {
                                            $(this).css('text-align','center');
                                        }
                                    });
                                }
                                
                                if(data.is_first === 1 && data.multiple_id !== null)
                                {
                                    $(row).find('td').css({
                                        'background-color' : '#ADD8E6',
                                        'color' : 'black'
                                    });
                                }
                                if(data.is_bot === 1)
                                {
                                    $(row).find('td').css({
                                        'background-color' : '#ff0000',
                                        'color' : 'black'
                                    });
                                }
                            }
                        });
                        
                        generatedTickets.on('page.dt', function() {
                            
                            const pageLength = generatedTickets.page.len();
                        });
                        
                        
                        // show the realtime updated ticket
                        Pusher.logToConsole = true;

                        var pusherAppKey = '{{ env('PUSHER_APP_KEY') }}';

                        var pusher = new Pusher(pusherAppKey, {
                            cluster: 'eu'
                        });
                        
                        // show the realtime updated ticket
                        var channel = pusher.subscribe('uncheckedin-tickets');
                        channel.bind('uncheckedin-tickets-data', function(data) {
                        
                            var ticketData=data.notCheckedInTicketsData;

                            // var dataTable = $('#noShowsTicketsTable').DataTable();
                            console.log(ticketData);

                            //var formattedTime= moment(ticketData.created_at).format('hh:mm:ss A');

                            generatedTickets.ajax.reload(null, false);
                        });

                         // reload tickets table
                        var channel = pusher.subscribe('reload-tables');
                        channel.bind('reload-tables-data', function(data) {
                        
                            var ticketData=data.ticketsTableReloadData;

                            generatedTickets.ajax.reload(null, false);
                        });

                        // show the realtime added tickets
                        var channel = pusher.subscribe('all-tickets');
                        channel.bind('all-tickets-data', function(data) {
                        
                            var ticketsData=data.ticketsTableData;

                            console.log(data);

                            var newTicketsIdsArray = [];
                            
                            $.each(ticketsData, function(i, item) {
                                console.log(ticketsData[i].id)

                                var formattedTime= moment(ticketsData[i].created_at).format('hh:mm:ss A')

                                if(ticketsData[i].checked_in == 0)
                                {
                                    var checkedInStatus='<a id="checkInButton'+ticketsData[i].id+'" class="btn btn-sm text-white btn-danger rounded-5 check-in-btn" ticket-id="'+ticketsData[i].id+'">No</a>'

                                } else {
                                    var checkedInStatus='<a id="checkInButton'+ticketsData[i].id+'" class="btn btn-sm text-white btn-success rounded-5 check-in-btn" ticket-id="'+ticketsData[i].id+'">Yes</a>'
                                }
                                
                                // if (ticketsData[i].is_first == 1 && ticketsData[i].is_extra == 0 && ticketsData[i].generated_by !== null && ticketsData[i].multiple_id !== null && ticketsData[i].ticketGeneratorRole == 1 && ticketsData[i].is_bot == 0)
                                // {
                                //     var roleName='TGOG';
                                    
                                // } else if (ticketsData[i].is_first == 0 && ticketsData[i].is_extra == 1 && ticketsData[i].generated_by !== null && ticketsData[i].multiple_id !== null && ticketsData[i].ticketGeneratorRole == 1 && ticketsData[i].is_bot == 0)
                                // {
                                //     var roleName='';
                                    
                                // } else if (ticketsData[i].is_first == 1 && ticketsData[i].is_extra == 0 && ticketsData[i].generated_by !== null && ticketsData[i].multiple_id == null && ticketsData[i].ticketGeneratorRole == 1 && ticketsData[i].is_bot == 0)
                                // {
                                //     var roleName='TGOG';
                                    
                                // } else if (ticketsData[i].is_first == 1 && ticketsData[i].is_extra == 0 && ticketsData[i].generated_by == null && ticketsData[i].multiple_id == null && ticketsData[i].ticketGeneratorRole == null && ticketsData[i].is_bot == 0)
                                // {
                                //     var roleName='Recipient';
                                    
                                // } else if (ticketsData[i].is_first == 1 && ticketsData[i].is_extra == 0 && ticketsData[i].generated_by == null && ticketsData[i].multiple_id == null && ticketsData[i].ticketGeneratorRole == null && ticketsData[i].is_bot == 1)
                                // {
                                //     var roleName='Bot';
                                    
                                // } else if (ticketsData[i].is_first == 1 && ticketsData[i].is_extra == 0 && ticketsData[i].generated_by !== null && ticketsData[i].multiple_id !== null && ticketsData[i].ticketGeneratorRole == 2 && ticketsData[i].is_bot == 0)
                                // {
                                //     var roleName='Recipient';

                                // } else if (ticketsData[i].is_first == 1 && ticketsData[i].is_extra == 0 && ticketsData[i].generated_by !== null && ticketsData[i].multiple_id !== null && ticketsData[i].ticketGeneratorRole == 2 && ticketsData[i].is_bot == 1)
                                // {
                                //     var roleName='Bot';

                                // } else if(ticketsData[i].is_first == 0 && ticketsData[i].is_extra == 1 && ticketsData[i].generated_by !== null && ticketsData[i].multiple_id !== null && ticketsData[i].ticketGeneratorRole == 2 && ticketsData[i].is_bot == 0)
                                // {
                                //     var roleName='';
                                    
                                // }

                                var ticketDetails = ticketsData[i];

                                var roleName = '';

                                var staffName = '';

                                if (ticketDetails.is_first == 1 && ticketDetails.is_extra == 0 && ticketDetails.is_bot == 0) {

                                    var firstName = ticketDetails.ticketGeneratorFname.charAt(0).toUpperCase();

                                    var lastName = ticketDetails.ticketGeneratorLname;

                                    var staffName = firstName+' '+lastName;

                                    if (ticketDetails.generated_by !== null && ticketDetails.multiple_id !== null)
                                    {
                                        // roleName = ''.strtoupper(substr(ticketDetails.ticketGeneratorFname, 0, 1)).''.ticketDetails.ticketGeneratorLname.'';
                                        roleName = (ticketDetails.ticketGeneratorRole == 1 || ticketDetails.ticketGeneratorRole == 3) ? ''+staffName+'' : 
                                                (ticketDetails.ticketGeneratorRole == 2) ? 'Recipient' : '';
                                    } 
                                    else if (ticketDetails.generated_by !== null && ticketDetails.multiple_id == null && ticketDetails.ticketGeneratorRole == 1) {
                                        roleName = ''+staffName+'';
                                    }
                                    else if (ticketDetails.generated_by == null && ticketDetails.multiple_id == null && ticketDetails.ticketGeneratorRole == null) {
                                        roleName = 'Recipient';
                                    }
                                } 
                                else if (ticketDetails.is_first == 1 && ticketDetails.is_extra == 0 && ticketDetails.is_bot == 1) {
                                    if (ticketDetails.generated_by !== null && ticketDetails.multiple_id !== null && ticketDetails.ticketGeneratorRole == 2) {
                                        roleName = 'Bot';
                                    }
                                    else if (ticketDetails.generated_by == null && ticketDetails.multiple_id == null && ticketDetails.ticketGeneratorRole == null) {
                                        roleName = 'Bot';
                                    }
                                }
                                else if (ticketDetails.is_first == 0 && ticketDetails.is_extra == 1) {
                                    roleName = '';
                                }

                                

                                // if(ticketsData[i].is_first == 1 && ticketsData[i].multiple_id !== null)
                                // {
                                //     var printLink='<a href="/view-multiple-users-ticket-size-pdf/'+ticketsData[i].multiple_id+'/card/" target="_blank" class="btn btn-sm btn-warning text-dark m-1">Print</a>'

                                // } else if(ticketsData[i].is_first == 1 && ticketsData[i].multiple_id == null)
                                // {
                                //     var printLink='<a href="/view-ticket-size-pdf/'+ticketsData[i].id+'/card/" target="_blank" class="btn btn-sm btn-warning text-dark m-1">Print</a>'
                                // }
                                // else if (ticketsData[i].is_first == 0 && ticketsData[i].multiple_id !== null)
                                // {
                                //     var printLink=''
                                // }

                                const ticket = ticketsData[i];
                                let printLink = '';

                                if (ticket.is_first == 1) {
                                    printLink = ticket.multiple_id !== null 
                                        ? `<a href="/view-multiple-users-ticket-size-pdf/${ticket.multiple_id}/card/" target="_blank" class="btn btn-sm btn-warning text-dark m-1">Print</a>`
                                        : `<a href="/view-ticket-size-pdf/${ticket.id}/card/" target="_blank" class="btn btn-sm btn-warning text-dark m-1">Print</a>`;
                                }
                                

                                var deleteBtn='<a class="btn btn-sm btn-warning text-dark m-1 withdraw-ticket added-ticket" ticket-id='+ticketsData[i].id+'>Remove Ticket</a>';

                                generatedTickets.row
                                .add({
                                    ticket: ticketsData[i].ticket_number,
                                    fname: ticketsData[i].firstName,
                                    lname: ticketsData[i].lastName,
                                    case_number: ticketsData[i].caseNumber,
                                    created: formattedTime,
                                    checked_in:checkedInStatus,
                                    created_by:roleName,
                                    print:printLink,
                                    ticket_withdrawal:deleteBtn
                                })
                                
                                var addedTicketsIdsArray=newTicketsIdsArray.push(ticketsData[i].ticket_number);
                            });

                            //generatedTickets.draw();
                            generatedTickets.ajax.reload(null, false);

                            $.each(newTicketsIdsArray, function(i, item) {
                                generatedTickets.rows().eq(0).each(function(index) {
                                    var rowData = generatedTickets.row(index).data();

                                    var row = $(generatedTickets.row(index).node());

                                    var ticketId=item+1;

                                    if (rowData.ticket === ticketId) {
                                        
                                        // Change the background color of the row
                                        row.find('td').css({
                                            'background-color' : 'grey',
                                            'color' : 'white'
                                        });

                                        row.addClass('new-row');
                                    } 

                                    $('.new-row').find('td:first-child').css('text-align', 'right');
                                    $('.new-row:not(:first-child)').each(function (index)
                                    {
                                        $(this).css('text-align','center');       
                                    });

                                    $('tr').not('.new-row').find('td').each(function() {
                                        $(this).css('text-align','center')
                                    });
                                });
                            });
                        });

                        // remove cancelledtickets
                        var channel = pusher.subscribe('tickets-cancelled');
                        channel.bind('tickets-cancelled-data', function(data) {

                            var ticketsData=data.cancelledTicketsData;

                            
                            var ticketRowsArray = [];

                            ticketsData.forEach(function(ticket) {
                                var ticketId = ticket;
                                var columnIndex = 0;
                                var rowIndex = generatedTickets.column(columnIndex).data().indexOf(ticketId);

                                ticketRowsArray.push(rowIndex);
                            });


                            ticketRowsArray.forEach(function(id) {
                                generatedTickets.row(id).remove();
                            });

                            //generatedTickets.draw();
                            generatedTickets.ajax.reload(null, false);
                        });

                        // // reload tickets table
                        // var channel = pusher.subscribe('all-tickets');
                        // channel.bind('all-tickets-data', function(data) 
                        // {
                        //     console.log(data);
                        //     generatedTickets.ajax.reload(null, false);
                        // });

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

                            interval = setInterval(()=> barcode = '',1000000)
                        });

                        // function highlightRow(value)
                        // {
                        //     const tds = document.querySelectorAll('.multiple-tickets-cells');
                            
                        //     console.log(tds);
                            
                        //     tds.forEach(td => {
                        //         if (td.textContent.trim() === value) {
                        //         const row = td.closest('tr');
                        //         row.classList.add('highlight-scanned-row');
                        //         }
                        //     });
                        // }

                        function handleBarcode(scanned_barcode)
                        {
                            let case_number = scanned_barcode.replace(/[^\d]/g, "");

                            // get user details based on case number
                            var url = '/admin/get-ticket-details';

                            $.ajax({
                                url: url,
                                method: 'GET',
                                data: {
                                    case_number: case_number
                                },
                                success: function(response)
                                {
                                    console.log(response);

                                    if(response.status == 404)
                                    {
                                        $('#barcodeScanTicketModal').modal('hide');
                                        swal(response.message);

                                    } 
                                    else if (response.status == 200)
                                    {
                                        var fname = response.data.first_name;
                                        var lname = response.data.last_name;
                                        var ticketNumber = response.data.ticket_number;
                                        var ticketId = response.data.id;
                                        var case_number = response.data.case_number;
                                        var userId = response.data.user_id;
                                        var dateOfBirth=moment(response.data.date_of_birth).format("MM/DD/YYYY");

                                        var previousSingleTicketUserId=$('#previousBarcodeScanUserId').val();

                                        $('#previousBarcodeScanUserId').val(userId);

                                        if(response.data.checked_in == '0')
                                        {
                                            var checkedIn = 'No';
                                        } else {
                                            var checkedIn = 'Yes';
                                        }

                                        $('#multipleIdUserIdScan,#multipleIdUserTicketScan,#multipleIdUserTicketStatusScan').val('');

                                        if ($('#barcodeScanTicketModal').hasClass('show') && previousSingleTicketUserId == userId) 
                                        {
                                            if(checkedIn == 'No')
                                            {
                                                var checkedInTicketId = ticketId
                                    
                                                var url = '/admin/check-in-status';

                                                var csrf = document.querySelector('meta[name="csrf-token"]').content;

                                                $.ajax({
                                                    url: url,
                                                    method: 'POST',
                                                    processData: false,
                                                    contentType: false,
                                                    data: function(){
                                                        var data = new FormData();
                                                        data.append('checkedInTicketId',checkedInTicketId);
                                                        data.append('_token' , csrf);
                                                        return data;
                                                        console.log(data)
                                                    }(),
                                                    success: function(response) 
                                                    {
                                                        var status=response.is_status;
                                                        var multiple=response.ticketsIds;

                                                        console.log(status,multiple);

                                                        
                                                    }
                                                });

                                                $('#barcodeScanTicketModal').modal('hide');

                                            } else {
                                                $('#barcodeScanTicketModal').modal('hide');
                                            }

                                        } else if($('#barcodeScanTicketModal').hasClass('show') && previousSingleTicketUserId !== userId) 
                                        {

                                            $('#barcodeScanTicketModal .modal-body').html('').html('<table id="singleTicketDisplayTable"><thead><tr><th>Ticket Number</th><th>Check In Status</th><th>First Name</th><th>Last Name</th><th>Case Number</th><th>Date Of Birth</th></tr></thead><tbody><tr><td id="barcodeScanTicketNumber">'+ticketNumber+'</td><td id="barcodeScanCheckInStatus">'+checkedIn+'</td><td id="barcodeScanFirstName">'+fname+'</td><td id="barcodeScanLastName">'+lname+'</td><td id="barcodeScanCaseNumber">'+case_number+'</td><td id="barcodeScanDoB">'+dateOfBirth+'</td></tr></tbody></table>');

                                            if ($('#barcodeScanCheckInStatus').text() == 'No') 
                                            {
                                                $('#barcodeScanCheckInStatus').css({
                                                    "background-color":"#dc3545",
                                                    "display": "inline-block",
                                                    "color": "white",
                                                    "padding":".25rem .5rem",
                                                    "border-radius": "50px",
                                                    "font-size": ".875rem",
                                                    "border": "none",
                                                    "line-height":"1.5"
                                                });
                                            } else 
                                            {
                                                $('#barcodeScanCheckInStatus').css({
                                                    "background-color":"green",
                                                    "display": "inline-block",
                                                    "color": "white",
                                                    "padding":".25rem .5rem",
                                                    "border-radius": "50px",
                                                    "font-size": ".875rem",
                                                    "border": "none",
                                                    "line-height":"1.5"
                                                });
                                            }
                                            
                                        } else {
                                            $('#barcodeScanTicketModal').modal('toggle');

                                            $('#barcodeScanTicketModal .modal-body').html('').html('<table id="singleTicketDisplayTable"><thead><tr><th>Ticket Number</th><th>Check In Status</th><th>First Name</th><th>Last Name</th><th>Case Number</th><th>Date Of Birth</th></tr></thead><tbody><tr><td id="barcodeScanTicketNumber">'+ticketNumber+'</td><td id="barcodeScanCheckInStatus">'+checkedIn+'</td><td id="barcodeScanFirstName">'+fname+'</td><td id="barcodeScanLastName">'+lname+'</td><td id="barcodeScanCaseNumber">'+case_number+'</td><td id="barcodeScanDoB">'+dateOfBirth+'</td></tr></tbody></table>');

                                            if ($('#barcodeScanCheckInStatus').text() == 'No') 
                                            {
                                                $('#barcodeScanCheckInStatus').css({
                                                    "background-color":"#dc3545",
                                                    "display": "inline-block",
                                                    "color": "white",
                                                    "padding":".25rem .5rem",
                                                    "border-radius": "50px",
                                                    "font-size": ".875rem",
                                                    "border": "none",
                                                    "line-height":"1.5"
                                                });
                                            } else 
                                            {
                                                $('#barcodeScanCheckInStatus').css({
                                                    "background-color":"green",
                                                    "display": "inline-block",
                                                    "color": "white",
                                                    "padding":".25rem .5rem",
                                                    "border-radius": "50px",
                                                    "font-size": ".875rem",
                                                    "border": "none",
                                                    "line-height":"1.5"
                                                });
                                            }
                                        } 
                                    }
                                    else if (response.status == 201)
                                    {
                                        var multipleTicketsData=response.data;

                                        var user_id=response.user_id;

                                        var user_case_number=response.user_case_number;

                                        var ticket_id=response.ticket_id;

                                        var ticket_status=response.ticket_status;

                                        var multipleTicketsLength=multipleTicketsData.length;

                                        // $('#barcodeScanTicketModal').modal('toggle');

                                        var previousMultipleTicketsUserId=$('#previousMultipleIdUserIdScan').val();

                                        $('#multipleIdUserIdScan').val(user_id);

                                        $('#multipleIdUserTicketScan').val(ticket_id);

                                        $('#previousMultipleIdUserIdScan').val(user_id);

                                        $('#multipleIdUserTicketStatusScan').val(ticket_status);

                                        $('#barcodeScanUserId,#previousBarcodeScanUserId').val('');

                                        // var fname = response.data.first_name;
                                        // var lname = response.data.last_name;
                                        // var ticketNumber = response.data.ticket_number;
                                        // var ticketId = response.data.id;
                                        // var case_number = response.data.case_number;
                                        // var userId = response.data.user_id;
                                        // var dateOfBirth=moment(response.data.date_of_birth).format("MM/DD/YYYY");

                                        if(response.data.checked_in == '0')
                                        {
                                            var checkedIn = 'No';
                                        } else {
                                            var checkedIn = 'Yes';
                                        }

                                        console.log(Number($('#multipleIdUserIdScan').val()),user_id)

                                        if ($('#barcodeScanTicketModal').hasClass('show') && previousMultipleTicketsUserId == user_id) 
                                        {
                                            if(Number($('#multipleIdUserTicketStatusScan').val()) == 0)
                                            {
                                                var checkedInTicketId = ticket_id
                                    
                                                var url = '/admin/check-in-barcode-scan';

                                                var csrf = document.querySelector('meta[name="csrf-token"]').content;

                                                $.ajax({
                                                    url: url,
                                                    method: 'POST',
                                                    processData: false,
                                                    contentType: false,
                                                    data: function(){
                                                        var data = new FormData();
                                                        data.append('checkedInTicketId',checkedInTicketId);
                                                        data.append('_token' , csrf);
                                                        return data;
                                                        console.log(data)
                                                    }(),
                                                    success: function(response) 
                                                    {
                                                        var status=response.is_status;
                                                        var multiple=response.ticketsIds;

                                                        console.log(status,multiple);
                                                    }
                                                });

                                                $('#barcodeScanTicketModal').modal('hide');

                                            } else {
                                                $('#barcodeScanTicketModal').modal('hide');
                                            }

                                        } 
                                        else if($('#barcodeScanTicketModal').hasClass('show') && previousMultipleTicketsUserId !== user_id) 
                                        {
                                            const table = $('<table id="barcodeScanMultipleTicketsTable" border="1"></table>');

                                            const thead = $('<thead></thead>');
                                            const headerRow = $('<tr></tr>');
                                            const headers = ["Ticket Number", "Checked In","First Name", "Last Name","Date of Birth", "Case Number"];

                                            headers.forEach(function(header) {
                                                headerRow.append('<th>' + header + '</th>');
                                            });
                                            thead.append(headerRow);
                                            table.append(thead);

                                            const tbody = $('<tbody></tbody>');
                                            multipleTicketsData.forEach(function(item) {
                                                const row = $('<tr></tr>');
                                                
                                                $.each(item, function(key, value) {
                                                    let cell = $('<td></td>').text(value);
                                                    
                                                    if (key === 'checked_in' && value == '0') {
                                                        cell.text("No").css({
                                                            "background-color":"#dc3545",
                                                            "display": "inline-block",
                                                            "color": "white",
                                                            "padding":".25rem .5rem",
                                                            "border-radius": "50px",
                                                            "font-size": ".875rem",
                                                            "border": "none",
                                                            "line-height":"1.5"
                                                        });
                                                    } else if (key === 'checked_in' && value == '1')
                                                    {
                                                        cell.text("Yes").css({
                                                            "background-color":"green",
                                                            "display": "inline-block",
                                                            "color": "white",
                                                            "padding":".25rem .5rem",
                                                            "border-radius": "50px",
                                                            "font-size": ".875rem",
                                                            "border": "none",
                                                            "line-height":"1.5"
                                                        });
                                                    }

                                                    if (key === 'date_of_birth') {
                                                        cell.text(moment(value).format("MM/DD/YYYY"));
                                                    }

                                                    if (key === 'case_number' && value == user_case_number) {
                                                        cell.addClass('scanned-case-number');
                                                    }

                                                    row.append(cell);
                                                });

                                                tbody.append(row);
                                            });

                                            table.append(tbody);

                                            $('#barcodeScanTicketModal .modal-body').html('').append(table);

                                            $('td.scanned-case-number').closest('tr').addClass('highlight-scanned-row');

                                            $('#barcodeScanMultipleTicketsTable tbody tr:first td:first').css('text-align','left');
                                        } 
                                        else 
                                        {
                                            $('#barcodeScanTicketModal').modal('toggle');

                                            $('#multipleIdUserIdScan').val(user_id);

                                            $('#multipleIdUserTicketScan').val(ticket_id);

                                            $('#previousMultipleIdUserIdScan').val(user_id);

                                            $('#multipleIdUserTicketStatusScan').val(ticket_status);

                                            const table = $('<table id="barcodeScanMultipleTicketsTable" border="1"></table>');

                                            const thead = $('<thead></thead>');
                                            const headerRow = $('<tr></tr>');
                                            const headers = ["Ticket Number", "Checked In","First Name", "Last Name","Date of Birth", "Case Number"];

                                            headers.forEach(function(header) {
                                                headerRow.append('<th>' + header + '</th>');
                                            });
                                            thead.append(headerRow);
                                            table.append(thead);

                                            const tbody = $('<tbody></tbody>');
                                            multipleTicketsData.forEach(function(item) {
                                                const row = $('<tr></tr>');
                                                
                                                $.each(item, function(key, value) {
                                                    let cell = $('<td></td>').text(value);
                                                    
                                                    if (key === 'checked_in' && value == '0') {
                                                        cell.text("No").css({
                                                            "background-color":"#dc3545",
                                                            "display": "inline-block",
                                                            "color": "white",
                                                            "padding":".25rem .5rem",
                                                            "border-radius": "50px",
                                                            "font-size": ".875rem",
                                                            "border": "none",
                                                            "line-height":"1.5"
                                                        });
                                                    } else if (key === 'checked_in' && value == '1')
                                                    {
                                                        cell.text("Yes").css({
                                                            "background-color":"green",
                                                            "display": "inline-block",
                                                            "color": "white",
                                                            "padding":".25rem .5rem",
                                                            "border-radius": "50px",
                                                            "font-size": ".875rem",
                                                            "border": "none",
                                                            "line-height":"1.5"
                                                        });
                                                    }

                                                    if (key === 'date_of_birth') {
                                                        cell.text(moment(value).format("MM/DD/YYYY"));
                                                    }

                                                    if (key === 'case_number' && value == user_case_number) {
                                                        cell.addClass('scanned-case-number');
                                                    }

                                                    row.append(cell);
                                                });

                                                tbody.append(row);
                                            });

                                            table.append(tbody);

                                            $('#barcodeScanTicketModal .modal-body').html('').append(table);

                                            $('td.scanned-case-number').closest('tr').addClass('highlight-scanned-row');

                                            $('#barcodeScanMultipleTicketsTable tbody tr:first td:first').css('text-align','left');
                                        }
                                    }
                                }
                            });
                        }
                    });


                    $(document).on('click', '.withdraw-ticket', function(e) {
                        e.preventDefault();

                        var ticketId = $(this).attr('ticket-id');

                        $('#confirmTicketDeleteModal').modal('toggle');
                        $('.continue-ticket-delete-btn').attr('ticket-id', ticketId);
                    });

                    $(document).on('click', '.continue-ticket-delete-btn', function(e) {
                        e.preventDefault();

                        var ticketId = $(this).attr('ticket-id');

                        // Add spinner to the continue button
                        $(this).html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop("disabled", true);
                        $('.close-modal').prop("disabled", true);

                        var url = '/admin/delete-ticket';

                        var csrf = document.querySelector('meta[name="csrf-token"]').content;

                        $.ajax({
                            url: url,
                            method: 'POST',
                            processData: false,
                            contentType: false,
                            data: function(){
                                var data = new FormData();
                                data.append('ticketId',ticketId);
                                data.append('_token' , csrf);
                                return data;
                                console.log(data)
                            }(),
                            success: function(response) {

                                if (response.status == 200) {

                                    $('.continue-ticket-delete-btn').attr('ticket-id','');
                                    $('#confirmTicketDeleteModal').modal('hide');
                                    
                                    $('#generatedTicketsTable').DataTable().ajax.reload(null, false);

                                } else if (response.status == 404) {
                                    alertify.set('notifier', 'position', 'top-center');
                                    alertify.success(response.message);
                                }

                            },
                            error: function() {
                                // Reset button states on error
                                $('.continue-ticket-delete-btn').html('Continue').prop("disabled", false);
                                $('.close-modal').prop("disabled", false);
                            }
                        });
                    });


                    $(document).on('click', '.check-in-btn', function(e) {
                        e.preventDefault();
                        var ticketId = $(this).attr('ticket-id');
                        
                        var checkinStatus=$(this).text();
                         
                        if(checkinStatus == 'No')
                        {
                            var checkedInTicketId = $(this).attr('ticket-id');

                            // Add spinner to the clicked button
                            $(this).html('<i class="fas fa-spinner fa-spin"></i>').prop("disabled", true);
                            $('.check-in-btn').not(this).prop("disabled", true).css("cursor", "not-allowed");

                            console.log(checkedInTicketId);
                
                            var url = '/admin/check-in-status';

                            var csrf = document.querySelector('meta[name="csrf-token"]').content;

                            $.ajax({
                                url: url,
                                method: 'POST',
                                processData: false,
                                contentType: false,
                                data: function(){
                                    var data = new FormData();
                                    data.append('checkedInTicketId',checkedInTicketId);
                                    data.append('_token' , csrf);
                                    return data;
                                    console.log(data)
                                }(),
                                success: function(response) 
                                {
                                    var status=response.is_status;
                                    var multiple=response.ticketsIds;

                                    console.log(status,multiple);

                                    if (status == 1 && multiple !== null) 
                                    {
                                        $.each(multiple, function(i, item) {
                                            $('#checkInButton'+multiple[i]).removeClass('btn-danger').html('').addClass('btn-success').html('Yes');
                                        });

                                        $('.check-in-btn').not(this).prop("disabled", false).css("cursor", "pointer");

                                    } else if (status == 1 && multiple == null)
                                    {
                                        $('#checkInButton'+checkedInTicketId).removeClass('btn-danger').html('').addClass('btn-success').html('Yes');

                                        $('.check-in-btn').not(this).prop("disabled", false).css("cursor", "pointer");
                                    } else if (status == 0 && multiple == null)
                                    {
                                        $('#checkInButton'+checkedInTicketId).removeClass('btn-success').html('').addClass('btn-danger').html('No');

                                        $('.check-in-btn').not(this).prop("disabled", false).css("cursor", "pointer");
                                    } else if (status == 0 && multiple !== null) 
                                    {
                                        $.each(multiple, function(i, item) {
                                            $('#checkInButton'+multiple[i]).removeClass('btn-success').html('').addClass('btn-danger').html('No');
                                        });

                                        $('.check-in-btn').not(this).prop("disabled", false).css("cursor", "pointer");

                                    }
                                },
                                error: function() {
                                    // Reset button state on error
                                    $('#checkInButton'+checkedInTicketId).removeClass('btn-success').html('').addClass('btn-danger').html('No');
                                    $('.check-in-btn').prop("disabled", false).css("cursor", "pointer");
                                }
                            });
                        }
                        else if (checkinStatus == 'Yes')
                        {
                            $('#confirmUserCheckinModal').modal('toggle');
                            
                            $('.new-checkin-status').text('No?');
                            
                            $('.continue-checkin-confirmation-btn').attr('ticket-id', ticketId);
                        }
                    });
                    
                    $(document).on('click', '.continue-checkin-confirmation-btn', function(e) {
                        e.preventDefault();

                        var checkedInTicketId = $(this).attr('ticket-id');

                        $('.check-in-btn').not(this).prop("disabled", true).css("cursor", "not-allowed");

                        console.log(checkedInTicketId);
            
                        var url = '/admin/check-in-status';

                        var csrf = document.querySelector('meta[name="csrf-token"]').content;

                        $.ajax({
                            url: url,
                            method: 'POST',
                            processData: false,
                            contentType: false,
                            data: function(){
                                var data = new FormData();
                                data.append('checkedInTicketId',checkedInTicketId);
                                data.append('_token' , csrf);
                                return data;
                                console.log(data)
                            }(),
                            success: function(response) 
                            {
                                var status=response.is_status;
                                var multiple=response.ticketsIds;

                                console.log(status,multiple);

                                // if (status == 1 && multiple !== null) 
                                // {
                                //     $.each(multiple, function(i, item) {
                                //         $('#checkInButton'+multiple[i]).removeClass('btn-danger').html('').addClass('btn-success').html('Yes');
                                //     });

                                //     $('.check-in-btn').not(this).prop("disabled", false).css("cursor", "pointer");

                                // } else if (status == 1 && multiple == null)
                                // {
                                //     $('#checkInButton'+checkedInTicketId).removeClass('btn-danger').html('').addClass('btn-success').html('Yes');

                                //     $('.check-in-btn').not(this).prop("disabled", false).css("cursor", "pointer");
                                // } else if (status == 0 && multiple == null)
                                // {
                                //     $('#checkInButton'+checkedInTicketId).removeClass('btn-success').html('').addClass('btn-danger').html('No');

                                //     $('.check-in-btn').not(this).prop("disabled", false).css("cursor", "pointer");
                                // } else if (status == 0 && multiple !== null) 
                                // {
                                //     $.each(multiple, function(i, item) {
                                //         $('#checkInButton'+multiple[i]).removeClass('btn-success').html('').addClass('btn-danger').html('No');
                                //     });

                                //     $('.check-in-btn').not(this).prop("disabled", false).css("cursor", "pointer");

                                // }

                                function updateButtonState(buttonId, isCheckedIn) {
                                    const $button = $('#checkInButton' + buttonId);
                                    $button.removeClass(isCheckedIn ? 'btn-danger' : 'btn-success')
                                        .addClass(isCheckedIn ? 'btn-success' : 'btn-danger')
                                        .html(isCheckedIn ? 'Yes' : 'No');
                                }


                                function enableOtherButtons() {
                                    $('.check-in-btn').not(this).prop("disabled", false).css("cursor", "pointer");
                                }

                                if (status == 1) {
                                    if (multiple !== null) {
                                        $.each(multiple, function(i, item) {
                                            updateButtonState(multiple[i], true);
                                        });
                                    } else {
                                        updateButtonState(checkedInTicketId, true);
                                    }
                                } else if (status == 0) {
                                    if (multiple !== null) {
                                        $.each(multiple, function(i, item) {
                                            updateButtonState(multiple[i], false);
                                        });
                                    } else {
                                        updateButtonState(checkedInTicketId, false);
                                    }
                                }

                                enableOtherButtons();
                                
                                $('#confirmUserCheckinModal').modal('hide');
                            },
                            error: function() {
                                // Reset button states on error
                                $('.continue-checkin-confirmation-btn').html('Continue').prop("disabled", false);
                                $('.close-modal').prop("disabled", false);
                                $('.check-in-btn').prop("disabled", false).css("cursor", "pointer");
                            }
                        });
                    });

                    $(document).on('click', '.close-modal', function(e) {
                        e.preventDefault();

                        $('.error-list').html(" ").addClass('d-none');
                        
                        $('#confirmTicketDeleteModal').modal('hide');
                        
                        $('#confirmUserCheckinModal').modal('hide');
                    });

                </script>
            @endsection