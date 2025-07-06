<!DOCTYPE html>
<html>
    <head>
        <style>
            *{
                font-size: 15px;
                color:black;
                font-weight: 500; 
                padding: 0;
                margin:0;
            }
        </style>
    </head>
    <body>
        <div style="margin-left:70px; margin-top:10%; padding:5px;">
            <div class="row-1-details" style="width:30%;
                font-size: 16px;
                font-weight: 500;
                width:100%; padding-left:10px;">
                <span style="display: inline-block;
                vertical-align: middle;">Issued: {{ date("M d, Y", strtotime($ticketDetails->created_at)) }} at {{ date("h:i:s A", strtotime($ticketDetails->created_at)) }}<br><br></span>
            </div>
            <div class="row-2-details" style="margin-top: 30px;">

                <div class="ticket-user-details" style="width:70%; margin-left:50px; display: inline-block; margin-bottom:40px; padding-left:10px;">
                   
                    <div class="ticket-number" style="display:block; height:100px; text-align:center;padding:20px 10px 0 0; width:110px;">
                        <span class="ticket-span" style="display:block;
                        vertical-align: middle;">Ticket Number #</span>
                        <span style="color: red;font-size: 60px;">{{ $ticketDetails->ticket_number }}</span>
                    </div>
                </div>
                <div style="display: block; padding:10px 10px 0 0; margin-left:13px;">
                    <b style="width:20%; margin-right:5px;">Name:</b>
                    <span class="user-name">{{ $ticketDetails->firstName }} {{ $ticketDetails->lastName }}</span>
                </div>
                <div style="display: block; padding:20px 10px 0 0; margin-left:13px;">
                    <b style="width:20%; margin-right:5px;">Case Number:</b>
                    <span class="case-number">{{ $ticketDetails->caseNumber }}</span>
                </div>
                @if($ticketDetails->projected_return_time !== null)
                <div style="display: block; padding:20px 10px 0 0; margin-left:13px;">
                    <b style="width:20%; margin-right:5px;">Return Time:</b>
                    <span class="return-time">{{ $ticketDetails->projected_return_time }}</span>
                </div>
                @endif
            </div>
        </div>
    </body>
</html>

