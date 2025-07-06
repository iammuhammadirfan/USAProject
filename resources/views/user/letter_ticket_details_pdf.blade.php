<!DOCTYPE html>
<html>
    <head>
        <style>
        </style>
    </head>
    <body>
        <div style="margin:10%;">
            <div class="row-1-details" style="
            font-size: 24px;
            font-weight: 500;
            width:100%; ">
                <span style="display: inline-block;
                vertical-align: middle; ">Issued: {{ date("M d, Y", strtotime($ticketDetails->created_at)) }} at {{ date("h:i:s A", strtotime($ticketDetails->created_at))</span>
                <span class="ticket-span" style="display: inline-block;
                vertical-align: middle; margin-left:43%; margin-bottom:-110px;">Ticket Number #</span>
            </div>
            <div class="row-2-details" style="width:100%; margin-top: 30px;">
                <div class="ticket-user-details" style="width:70%; display: inline-block; font-size: 28px;">
                    <div style="display: block; padding:20px 10px 0 0;">
                        <b style="width:35%; display: inline-block; margin-right:10px; padding:3px;">Name:</b>
                        <span class="user-name" style=" display: inline-block; text-align:center;">{{ $ticketDetails->firstName }} {{ $ticketDetails->lastName }}</span>
                    </div>
                    <div style="display: block; padding:20px 10px 0 0;">
                        <b style="width:35%; display: inline-block; margin-right:10px; padding:3px;">Case Number:</b>
                        <span class="case-number" style=" display: inline-block; text-align:center;">{{ $ticketDetails->caseNumber }}</span>
                    </div>
                </div>
                <div class="ticket-number" style="color: red;
                font-size: 60px;width:20%; display: inline-block; padding:auto;text-align:center; vertical-align: middle; margin-top:5px; margin-left:66px; font-weight: 500;">
                    <span>{{ $ticketDetails->ticket_number }}</span>
                </div>
            </div>
        </div>
    </body>
</html>

