<!DOCTYPE html>
<html>
    <head>
        <style>
            *{
                font-size: 26px;
                color:black;
                /* font-weight: 500; */
                padding: 0;
                margin:0;
            }

            .row-1-details
            {
                display: inline-block;
                align-items: center;
                padding: 10px;
                width:70%;
                margin-left:180px;
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
        <div style="margin-top:10px;">
            <div class="row-1-details">
                <span class="date-span">Issued: {{ date("M d, Y", strtotime($generatedTicketDetails->created_at)) }} at {{ date("h:i:s A", strtotime($generatedTicketDetails->created_at)) }}</span>
            </div>
            <div class="row-2-details">
                <div class="ticket-user-details" style="display: block; margin: 10px;">
                    <table class="table" style="width:100%; text-align:left;">
                        <tr>
                            <th style="width:50%"><b>Name</b></th>
                            <th style="width:30%"><b>Case Number</b></th>
                            <th style="width:30%"><b>Ticket #</b></th>
                        </tr>
                        @foreach($ticketDetails as $keyTicket => $ticket)
                        <tr style="text-align: center;">
                            <td style="width:50%"><span style="text-align:left; display:block; margin-left:180px;">{{ $ticket->firstName }} {{ $ticket->lastName }}</span></td>
                            <td style="width:30%"><span>{{ $ticket->caseNumber }}</span></td>
                            <td style="width:30%"><span style="color: red;">{{ $ticket->ticket_number }}</span></td>
                        </tr>
                    @endforeach
                    </table>
                </div>
                {{-- <div class="ticket-number" style="color: red;
                font-size: 50px;width:20%; display: inline-block; padding:auto; padding-bottom:30px; padding-left:20px;">
                    <span>{{ $ticketDetails->ticket_number }}</span>
                </div> --}}
            </div>
        </div>
    </body>
</html>

