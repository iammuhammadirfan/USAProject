@extends('admin.layout')
@section('content')
@section('overview_dashboard_styles')
    {{-- datatables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css"/>

    <style>
        /* .overview-dashboard-holder
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
        } */

        /* .details-holder
        {
            width: 30%;
            height: 220px;
            text-align: center;
            margin: 10px;
            padding: 10px;
            font-weight: 500;
            border-radius: 20px;
            word-wrap: break-word;
            overflow-wrap: break-word;
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
        } */

        .dt-length label {
            margin-left: 10px;
        }
        
        /* @media(max-width:768px)
        {
            .details-holder
            {
                width: 40%;
            }

            .now-serving-div{
                font-size: 380px;
            }
        } */

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


    </style>
@stop




{{-- manage projected return times for the tickets --}}
<div class="row mt-5">
    <h3 style="text-align: center;">List of No Shows</h3>
    
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

@endsection

@section('overview_dashboard_script')
    {{-- pusher js --}}
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    {{-- datatable --}}
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script> 
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>

    {{-- moment js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>

   <script>

        $(document).ready(function() {

            document.getElementById('backToBottom').style.display = "none";

            document.getElementById('backToBottom').style.visibility = "visible";
            // var totalOverallSignups=Number($('#allTicketsCount').text())+Number($('#totalVolunteerGroupHomeSignups').text());
            // var totalOverallServed=Number($('#checkedTicketsCount').text())+Number($('#totalVolunteerGroupHomeServed').text());
            // var totalOverallRemaining=Number($('#remainingTicketsCount').text())+Number($('#totalVolunteerGroupHomeunServed').text());
            // $('#totalOverallSignups').text(totalOverallSignups);
            // $('#totalOverallServed').text(totalOverallServed);
            // $('#totalOverallRemaining').text(totalOverallRemaining);
            // Remove .table-striped class
            $('#noShowsTicketsTable').removeClass('table-striped');

            var remainingticketsTable=$('#noShowsTicketsTable').DataTable({
                columnDefs: [{
                    "defaultContent": "-",
                    }],
                "iDisplayLength":25,
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
			
			remainingticketsTable.on('page.dt', function() {
				
				const pageLength = remainingticketsTable.page.len();
				
				   // Reset page length to 25 when navigating pages
				//generatedTickets.page.len(25).draw();
			});

            // get number being served right now
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

            // show the realtime updated ticket
            Pusher.logToConsole = true;

            var pusherAppKey = '{{ env('PUSHER_APP_KEY') }}';

            var pusher = new Pusher(pusherAppKey, {
                cluster: 'eu'
            });

            // show realtime new analytics
                // var channel = pusher.subscribe('tickets-analytics');
                // channel.bind('tickets-analytics-count', function(data) {

                // //    console.log(data.ticketsAnalytics);

                // //    var current_remaining = (data.ticketsAnalytics.total_overall_unserved)-(data.ticketsAnalytics.totalCurrentNoShows);
                
                //     $('#totalNoShows').text(data.ticketsAnalytics.totalCurrentNoShows)
                //     $('#totalWaitingToBeServed').text(data.ticketsAnalytics.currentTicketsRemaining)
                //     $('#allTicketsCount').text(data.ticketsAnalytics.all_tickets_count)
                //     $('#checkedTicketsCount').text(data.ticketsAnalytics.tickets_served_count)
                //     $('#remainingTicketsCount').text(data.ticketsAnalytics.remaining_tickets_count)
                //     $('#totalVolunteerGroupHomeSignups').text(data.ticketsAnalytics.total_volunteer_grouphomes_signups);
                //     $('#totalVolunteerGroupHomeServed').text(data.ticketsAnalytics.total_volunteer_grouphomes_served);
                //     $('#totalVolunteerGroupHomeunServed').text(data.ticketsAnalytics.total_volunteer_grouphomes_unserved);
                //     $('#totalOverallSignups').text(data.ticketsAnalytics.total_overall_signups);
                //     $('#totalOverallServed').text(data.ticketsAnalytics.total_overall_served);
                //     $('#totalOverallRemaining').text(data.ticketsAnalytics.total_overall_unserved);
                // });

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

                remainingticketsTable.ajax.reload(null, false);

                //remainingticketsTable.draw();
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

            // reload tickets table
            var channel = pusher.subscribe('reload-tables');
            channel.bind('reload-tables-data', function(data) {
            
                var ticketData=data.ticketsTableReloadData;

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

                        remainingticketsTable.row
                        .add({
                            ticket: ticketsData[i].ticket_number,
                            fname: ticketsData[i].firstName,
                            lname: ticketsData[i].lastName,
                            case_number: ticketsData[i].caseNumber,
                            created: formattedTime
                        })

                        newTicketsIdsArray.push(ticketsData[i].ticket_number);

                        //remainingticketsTable.draw();
						remainingticketsTable.ajax.reload(null, false);

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
@endsection