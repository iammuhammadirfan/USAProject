<!DOCTYPE html>
<html>
    <head>
        <title>Sign Ups Table</title>
        <style>
            *{
                font-size: 18px;
                color:black;
                /* font-weight: 500; */
                padding: 0;
                margin:0;
            }

            .row-1-details
            {
                display: block;
                align-items: center;
                padding: 10px;
            }

            table { border-collapse: collapse; }

        </style>
    </head>
    <body>
        <div style="margin-top:10px;">
            <div class="row-1-details">
                <h2 style="text-align: center;">Current Sign-ups as of {{ $todaysDate }} </h2>
            </div>
            <div class="row-2-details">
                <div class="current-tickets-table" style="padding:10px;">
                    @if(count($currentTicketsData)>0)
                        <table style="width:100%; text-align:center;">
                            <thead>
                                <tr>
                                    <th style="width:30%; border:2px solid rgb(17, 15, 15); padding:7px;">Ticket #</th>
                                    <th style="width:30%; border:2px solid rgb(17, 15, 15); padding:7px;">First Name</th>
                                    <th style="width:30%; border:2px solid rgb(17, 15, 15); padding:7px;">Last Name</th>
                                    <th style="width:30%; border:2px solid rgb(17, 15, 15); padding:7px;">Case Number</th>
									<th style="width:30%; border:2px solid rgb(17, 15, 15); padding:7px;">Checked In</th>
                                    <th style="width:30%; border:2px solid rgb(17, 15, 15); padding:7px;">Created</th>
                                </tr>
                            </thead>
                            @foreach ($currentTicketsData as $ticket)
                                @if ($ticket->is_first == 0)
                                    <tr style="border:2px solid rgb(17, 15, 15); background-color:gray; font-size:20px; font-weight:400;">
                                        <td style="border:2px solid rgb(17, 15, 15); padding:7px; color:white; text-align:right;" class="col-ticket-id">
                                            {{ $ticket->ticket_number }}
                                        </td>
                                        <td style="border:2px solid rgb(17, 15, 15); padding:7px; color:white;" class="col-firstname">
                                            {{ $ticket->firstName }}
                                        </td>
                                        <td style="border:2px solid rgb(17, 15, 15); padding:7px; color:white;" class="col-lastname">
                                            {{ $ticket->lastName }}
                                        </td>
                                        <td style="border:2px solid rgb(17, 15, 15); padding:7px; color:white;" class="col-casenumber">
                                            {{ $ticket->caseNumber }}
                                        </td>
										<td style="border:2px solid rgb(17, 15, 15); padding:7px; color:white;" class="col-checkedin">
                                           @if($ticket->checked_in == 1)
                                            Yes
                                           @else
                                            No
                                           @endif
                                        </td>
                                        <td style="border:2px solid rgb(17, 15, 15); padding:7px; color:white;" class="col-created">
                                            {{ date('H:i:s A', strtotime($ticket->created_at)) }}
                                        </td>
                                    </tr>
                                @elseif ($ticket->is_first == 1)
                                <tr style="border:2px solid rgb(17, 15, 15); font-size:20px; font-weight:400;">
                                    <td style="border:2px solid rgb(17, 15, 15); padding:7px;" class="col-ticket-id">
                                        {{ $ticket->ticket_number }}
                                    </td>
                                    <td style="border:2px solid rgb(17, 15, 15); padding:7px;" class="col-firstname">
                                        {{ $ticket->firstName }}
                                    </td>
                                    <td style="border:2px solid rgb(17, 15, 15); padding:7px;" class="col-lastname">
                                        {{ $ticket->lastName }}
                                    </td>
                                    <td style="border:2px solid rgb(17, 15, 15); padding:7px;" class="col-casenumber">
                                        {{ $ticket->caseNumber }}
                                    </td>
                                    <td style="border:2px solid rgb(17, 15, 15); padding:7px;" class="col-checkedin">
                                    @if($ticket->checked_in == 1)
                                        Yes
                                    @else
                                        No
                                    @endif
                                    </td>
									<td style="border:2px solid rgb(17, 15, 15); padding:7px;" class="col-created">
                                        {{ date('H:i:s A', strtotime($ticket->created_at)) }}
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </table>
                    @else
                        <p style="margin: 50px; text-align:center; font-size:25px;">No Sign-ups Found</p>
                    @endif
                </div>
            </div>
        </div>
    </body>
</html>

