<!DOCTYPE html>
<html>
    <head>
        <style>
            *{
                font-size: 16px;
                color:black;
                /* font-weight: 500; */
                padding: 0;
                margin:0;
            }

            .row-1-details
            {
                display: inline-block;
                align-items: center;
                padding: 4px;
                margin-top:20px;
            }

            .ticket-span
            {
                display: block;
                margin: 10px;
                /* margin-right: 20%; */
            }

            .date-span
            {
                display: block;
                margin: 10px;
                /* margin-right: 20%; */
            }
        </style>
    </head>
    <body>
        <div style="margin:10px 0 0 100px;">
            <div class="row-1-details">
                <span class="date-span">Issued: {{ date("M d, Y", strtotime($generatedTicketDetails->created_at)) }} at {{ date("h:i:s A", strtotime($generatedTicketDetails->created_at)) }}</span>
                @if($generatedTicketDetails->projected_return_time !== null)
                <span class="date-span">Return Time: {{ $generatedTicketDetails->projected_return_time }}</span>
                @endif
            </div>
            <div class="row-1-details">
                <p style="margin-bottom:13px;">Total Tickets: {{ $ticketDetails->count() }}</p>
            </div>
            <div class="row-2-details" style="margin-top:2px;">
                <div class="ticket-user-details">
                    <table class="table" style="width:100%; text-align:left;">
                        <tr>
                            <th style="width:30%"><b>Name</b></th>
                            <th style="width:30%"><b>Case Number</b></th>
                            <th style="width:30%"><b>Ticket #</b></th>
                        </tr>
                        @foreach($ticketDetails as $ticketkey=>$ticket)
                            <tr style="text-align: center; border:1px solid black;">
                                <td style="width:70%"><span style="text-align:left; display:block;">{{ $ticket->firstName }} {{ $ticket->lastName }}</span></td>
                                <td style="width:20%"><span>{{ $ticket->caseNumber }}</span></td>
                                <td style="width:10%"><span style="color: red;">{{ $ticket->ticket_number }}</span></td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>

