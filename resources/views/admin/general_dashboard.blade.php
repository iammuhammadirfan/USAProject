<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GoGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> -->
    <!-- Bootstrap Icons CSS -->

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.0/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.bootstrap.css"/>

    <style>
        .overview-dashboard-holder
        {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .overview-dashboard-holder-2
        {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }

        .overview-dashboard-holder-3
        {
            display: flex;
            justify-content: center;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .details-holder
        {
            width: 30%;
            height: 200px;
            text-align: center;
            margin: 10px;
            padding: 10px;
            font-weight: 500;
            border-radius: 20px;
        }

        .now-serving-div{
            display: flex;
            justify-content: space-around;
            align-items: center;
            color: red;
            height: 400px;
            margin: 0 auto;
            font-size: 450px;
            background-color: black;
        }

        .dt-length label {
            margin-left: 10px;
        }
        
        @media(max-width:992px)
        {
            .overview-dashboard-holder-3
            {
                gap: 5px;
            }
        }

        /* @media(max-width:480px)
        {
            .details-holder
            {
                width: 100%;
            }

            .now-serving-div{
                font-size: 300px;
            }
        } */

        /* @media(max-width:992px)
        {
            .number-control-div {
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
            }
        } */

        @media(max-width:768px)
        {
            .details-holder {
				width: 100%;
				height: 150px;
			}

            .now-serving-div{
                font-size: 380px;
            }
        }

        @media(max-width:480px)
        {

            .now-serving-div{
                font-size: 300px;
            }
        }


    </style>
</head>

<body class="bg-white " style="overflow-x:hidden">

<div class="container">
    <div class="row mt-2">
    <h3 style="text-align: center;">General Dashboard</h3>
    
    <div class="col-md-10 mx-auto">
        <div class="overview-dashboard-holder-3">
            <div class="details-holder" style="background-color: rgb(204, 102, 255)">
                <p style="font-size: 30px;">Total Sign Ups</p>
                <p style="font-size:70px; margin-top: -30px;" id="allTicketsCount">{{ $allTicketsCount }}</p>
            </div>
            <div class="details-holder" style="background-color:rgb(51, 204, 255)">
                <p style="font-size: 30px;">Total Served</p>
                <p style="font-size:70px; margin-top: -30px;" id="checkedTicketsCount">{{ $checkedInTicketsCount }}</p>
            </div>
        </div>
        <div class="overview-dashboard-holder-3">
            <div class="details-holder" style="background-color: #ffffc5">
                <p style="font-size: 30px;">Total No Shows</p>
                <p style="font-size:70px; margin-top: -30px;" id="totalNoShows">{{ $currentNoShows }}</p>
            </div>
            <div class="details-holder" style="background-color: #ffffc5">
                <p style="font-size: 30px;">Total Waiting To Be Served</p>
                <p style="font-size:70px; margin-top: -30px;" id="totalWaitingToBeServed">{{ $currentTicketsRemaining }}</p>
            </div>
        </div>
        <div class="overview-dashboard-holder-2">
            <div class="details-holder" style="color: red;background-color: black;">
                <p style="font-size: 30px;">Now Serving</p>
                <p style="font-size: 70px;margin-top: -15px;" class="ticket-served now-serving-ticket"></p>
            </div>
        </div>
    </div>
</div>

{{-- manage projected return times for the tickets --}}
<div class="row mt-5">
    <h3 style="text-align: center;">List of Remaining</h3>
    
    <div class="col-md-10 mx-auto">
        <table id="noShowsTicketsTable" class="table table-striped table-bordered" style="width:100%;">
            <thead>
            <tr>
                <td>Ticket #</td>
                <td>First Name</td>
                <td>Last Name</td>
                <td>Case Number</td>
                <td>Created</td>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

        {{-- jquery --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    {{-- bootstrap --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>

    {{-- pusher js --}}
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    {{-- datatable --}}
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script> 
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>

    {{-- moment js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>

   <script>

        $(document).ready(function() {
            // Remove .table-striped class
            $('#noShowsTicketsTable').removeClass('table-striped');

            var remainingticketsTable=$('#noShowsTicketsTable').DataTable({
                columnDefs: [{
                    "defaultContent": "-",
                    }],
                lengthMenu:[10,25,50,100,{ label: 'All',value: -1 }],
                processing:true,
                serverside:true,
                responsive:true,
                "ajax": {
                    "url": "/get-no-shows-tickets",
                    "type": "get",
                },
                columns: [
                    {  data: 'ticket',name:'ticket',orderable:true,searchable:true},
                    {  data: 'fname',name:'fname',orderable:true,searchable:true },
                    {  data: 'lname',name:'lname',orderable:true,searchable:true},
                    {  data: 'case_number',name:'case_number',orderable:true,searchable:true },
                    {  data: 'created',name:'created',orderable:true,searchable:true }
                ],
                "createdRow": function(row, data, dataIndex) {
                    console.log(data,data,dataIndex)
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

                            var extraIndexes=[1,2,3,4,5];
                            if (extraIndexes.includes(index)) {
                                $(this).addClass('dt-center');
                            }
                        });
                    }

                    if (data.is_first === 1) 
                    {
                        $(row).children('td').each(function (index) 
                        {
                            var allIndexes=[0,1,2,3,4,5];

                            console.log('Column Index:', index);
                            if (allIndexes.includes(index)) {
                                $(this).css('text-align','center');
                            }
                        });
                    }
                }
            });

            // get number being served right now
            var url = '/get-ticket-served';

            $.ajax({
                url: url,
                method: 'get',
                success: function(response) {
                    console.log(response);
                    
                    if($.isNumeric(response.ticket_number))
                    {
                        $('.ticket-served').text(response.ticket_number);
                    } else {
                        
                        $('.ticket-served').text(response.ticket_number);
                    }
                    
                }
            });

           

            // show the realtime updated ticket
            Pusher.logToConsole = true;

            var pusherAppKey = '{{ env('PUSHER_APP_KEY') }}';

            var pusher = new Pusher(pusherAppKey, {
                cluster: 'eu'
            });

            // show realtime new analytics
            var channel = pusher.subscribe('tickets-analytics');
            channel.bind('tickets-analytics-count', function(data) {

            //    var current_remaining = (data.ticketsAnalytics.total_overall_unserved)-(data.ticketsAnalytics.totalCurrentNoShows);

            //    console.log(current_remaining,data.ticketsAnalytics);
                $('#totalNoShows').text(data.ticketsAnalytics.totalCurrentNoShows)
                $('#totalWaitingToBeServed').text(data.ticketsAnalytics.currentTicketsRemaining)
                $('#allTicketsCount').text(data.ticketsAnalytics.all_tickets_count)
                $('#checkedTicketsCount').text(data.ticketsAnalytics.tickets_served_count)
                $('#remainingTicketsCount').text(data.ticketsAnalytics.total_overall_unserved)
            });

            // remove cancelledtickets
            var channel = pusher.subscribe('tickets-cancelled');
            channel.bind('tickets-cancelled-data', function(data) {

                var ticketsData=data.cancelledTicketsData;

                var ticketRowsArray = [];

                ticketsData.forEach(function(ticket) {
                    var ticketId = ticket;
                    var columnIndex = 0;
                    var rowIndex = remainingticketsTable.column(columnIndex).data().indexOf(ticketId);

                    ticketRowsArray.push(rowIndex);
                });

                ticketRowsArray.forEach(function(id) {
                    remainingticketsTable.row(id).remove();
                });

                // remainingticketsTable.ajax.reload(null, false);

                remainingticketsTable.draw();
            });

             // show realtime ticket being served
            var channel = pusher.subscribe('current-ticket');
                channel.bind('counter-updated', function(data) {

                var ticketNumber=JSON.stringify(data.count);

                    console.log(ticketNumber);
                $('.ticket-served').text(ticketNumber)
            });

            // show the realtime updated ticket
            var channel = pusher.subscribe('uncheckedin-tickets');
            channel.bind('uncheckedin-tickets-data', function(data) {
            
                var ticketData=data.notCheckedInTicketsData;

                // var dataTable = $('#noShowsTicketsTable').DataTable();
                console.log(ticketData);

                var formattedTime= moment(ticketData.created_at).format('hh:mm:ss A');

                remainingticketsTable.ajax.reload(null, false);
            });



            // show realtime added tickets
            var channel = pusher.subscribe('all-tickets');
            channel.bind('all-tickets-data', function(data) {
            
                var ticketsData=data.ticketsTableData;

                console.log(ticketsData);

                var newTicketsIdsArray = [];

                $.each(ticketsData, function(i, item) {

                    var formattedTime= moment(ticketsData[i].created_at).format('hh:mm:ss A')

                    if(ticketsData[i].checked_in == 0)
                    {
                        var checkedInStatus='<span>No</span>';

                        remainingticketsTable.row
                        .add({
                            ticket: ticketsData[i].ticket_number,
                            fname: ticketsData[i].firstName,
                            lname: ticketsData[i].lastName,
                            case_number: ticketsData[i].caseNumber,
                            created: formattedTime,
                        })

                        newTicketsIdsArray.push(ticketsData[i].ticket_number);

                        remainingticketsTable.draw();

                        $.each(newTicketsIdsArray, function(i, item) {
                            remainingticketsTable.rows().eq(0).each(function(index) {
                                var rowData = remainingticketsTable.row(index).data();

                                var row = $(remainingticketsTable.row(index).node());

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
                    }
                });
            });
        });
   </script>
</body>

</html>
