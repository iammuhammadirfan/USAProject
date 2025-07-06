<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>GoGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body {
        background-color: #f8f9fa; /* Light Gray */
    }

    .nhcc-logo {
        max-width: 100%;
        height: auto;
    }

    #todaysDate
    {
        font-size: 50px;
    }

    .now-serving-div{
        display: flex;
        justify-content: space-around;
        align-items: center;
        color: red;
        height: 660px;
        margin: 0 auto;
        font-size: 450px;
        background-color: black;
        flex-direction: column;
    }

    .now-serving-ticket
    {
        font-size: 30px;
        font-weight: 500;
    }

    #yourNumberText
    {
        font-size: 30px;
        font-weight: 500;
    }

    .distribution-countdown-timer 
    {
        width: 100%;
        margin: auto;
        display: flex;
    }

    .distribution-countdown-timer  div
    {
        flex-basis:110px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .distribution-countdown-timer  div p
    {
        font-size:60px;
        margin-bottom:-14px;
    }

    .distribution-countdown-timer  div span
    {
        font-size: 22px;
        font-weight: 600;
    }

    .countdown-timer-holder
    {
        display: flex;
        text-align: center;
        flex-direction: column;
        align-items: center;
        font-weight:600;
    }

    .countdown-timer-holder p
    {
        font-size:40px;
    }

    .ticket-served 
    {
        font-weight: 500;
        text-align: center;
    }

    @media(min-width:1025px)
    {
        .countdown-timer-holder p {
            font-size: 100px;
        }

        .distribution-countdown-timer div p {
            font-size: 200px;
        }

        .distribution-countdown-timer div {
            flex-basis: 250px;
        }

        .distribution-countdown-timer div span {
            font-size: 60px;
        }
    }

    @media(min-width:1441px)
    {
        .countdown-timer-holder p {
            font-size: 120px;
        }

        .distribution-countdown-timer {
            gap: 4px;
        }

        .distribution-countdown-timer div {
            flex-basis: 30%;
        }

        .distribution-countdown-timer div p {
            font-size: 120px;
        }

        .distribution-countdown-timer div span {
            font-size: 60px;
        }
    }

    @media(max-width:992px)
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
                font-size: 400px;
            }

            .distribution-countdown-timer {
                padding-left: 2%;
            }

            .distribution-countdown-timer div {
                flex-basis: 100px;
                padding: 3px;
            }

            .distribution-countdown-timer div:last-of-type {
                padding: -10px;
            }

            /* .distribution-countdown-timer div {
                flex-basis: 100px;
                padding: 3px;
            } */
        }

        @media(max-width:768px)
        {
            .ticket-served {
                font-size: 270px;
            }

            #todaysDateDiv {
                padding: 20px;
            }

            #todaysDate {
                font-size: 30px;
                margin: auto;
            }
        }

        @media(max-width:480px)
        {
            .ticket-served{
                font-size: 270px;
            }

            #yourNumberText
            {
                font-size: 20px;
                margin: auto;
            }

            .user-ticket-number
            {
                font-size: 25px;
            }
        }

        @media(max-width:450px)
        {
            .ticket-served {
                font-size: 220px;
            }

        }

        @media(max-width:360px)
        {
            .ticket-served {
                font-size: 200px;
            }
        }

    
</style>

</head>

<body class="bg-white">
    <div class="container mt-5">
        <div class="row ">
            <div class="col-md-12" id="todaysDateDiv" style="display: flex;
    align-items: center;
    justify-content: space-around;">
                <p id="todaysDate">              
                    {{ $todaysDate }}
                </p>
            </div>
        </div>
        <input type="hidden" class="distribution-start-time" value="{{ $distributionDateStartTime }}"/>

        <input type="hidden" class="distribution-end-time" value="{{ $distributionDateEndTime }}"/>

        <input type="hidden" class="next-distribution-start-time" value="{{ $nextWeekDistributionStartTime }}"/>

        <input type="hidden" class="ticket-being-served" value="{{ $currentTicketServed }}"/>


        <div class="row">
            <div class="col-md-12">
                <div style="display: flex;
    justify-content: space-between;
    align-items: center;">
                <p class="now-serving-ticket">Now Serving Number</p>
                <div id="loggedInUserTicket" class="d-none">
                    <p id="yourNumberText">Your Number</p>
                    <p class="user-ticket-number" style="text-align: center; color:red;font-size: 70px;
                font-weight: 500;
    margin: auto;"></p>
                </div>
                </div>
                <div class="now-serving-div">
                    <div class="countdown-timer-holder d-none">
                        <p>Distribution will start in</p>
                        <div class="distribution-countdown-timer">
                            <div>
                                <p id="distributionCountDays"></p>
                                <span>Days</span>
                            </div>
                            <div>
                                <p id="distributionCountHours"></p>
                                <span>Hours</span>
                            </div>
                            <div>
                                <p id="distributionCountMinutes"></p>
                                <span>Minutes</span>
                            </div>
                            <div>
                                <p id="distributionCountSeconds"></p>
                                <span style="margin-left:5px;">Seconds</span>
                            </div>
                        </div>
                    </div>
                    <p class="ticket-served d-none"></p>
                </div>
                <p id="timeServed" style="text-align: center;
                font-size: 50px;
                font-weight: 400;"></p>
            </div>
        </div>

    <?php
        $notificationmsgs=Session::get('notificationMessages');

        $nowServingErrorData = $notificationmsgs->firstWhere('modal_no', 11);

        $msg = Str::limit(strip_tags($nowServingErrorData->message));
    ?>

    {{-- jquery --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    {{-- pusher js --}}
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>


    {{-- bootstrap --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>

    <script type="text/javascript">

        function formatDateToISO(date) 
        {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            
            return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        }


        $(document).ready(function() 
        {
            // show the realtime updated ticket
            Pusher.logToConsole = true;

            var pusherAppKey = '{{ env('PUSHER_APP_KEY') }}';

            var pusher = new Pusher(pusherAppKey, {
                cluster: 'eu'
            });

           
            
            // fetch ticket number
            var loggedInUserTicket={{ $ticketNumber }}

            console.log(loggedInUserTicket);
            
            if(loggedInUserTicket)
            {
                $('#loggedInUserTicket').removeClass('d-none');

                $('.user-ticket-number').text(loggedInUserTicket);
            }

            var formattedCurrentDate=new Date();

            var dStartTime=$('.distribution-start-time').val();

            var formattedDistributionStartTime=new Date(dStartTime);

            var formattedDistributionStartTimeX = formatDateToISO(formattedDistributionStartTime);


            var dEndTime=$('.distribution-end-time').val();

            var formattedDistributionEndDate=new Date(dEndTime);

            var formattedDistributionEndDateX = formatDateToISO(formattedDistributionEndDate);


            var nextDStartTime=$('.next-distribution-start-time').val();

            var formatedNextDStartTime=new Date(nextDStartTime);

            var formatedNextDStartTimeDateTime = formatDateToISO(formatedNextDStartTime);

            var currentTicketNumber = $('.ticket-being-served').val();;

            // const formattedCurrentDateTimeY = formatDateToISO(new Date());

            var x = setInterval(function()
            {

                // show login form when login time is about to start countdown
                var currentDateX=new Date();

                const formattedCurrentDateX = formatDateToISO(currentDateX);

                const distributionDateTimeDiff = formattedDistributionStartTime - currentDateX;

                const days = Math.floor(distributionDateTimeDiff / (1000*60*60*24));
                const hours = Math.floor((distributionDateTimeDiff % (1000*60*60*24)) / (1000*60*60));
                const minutes = Math.floor((distributionDateTimeDiff % (1000*60*60)) / (1000*60));
                const seconds = Math.floor((distributionDateTimeDiff % (1000*60)) / 1000);

                const endDistributionTimeDiff = formattedDistributionEndDate - currentDateX;

                const endDistributionDays = Math.floor(endDistributionTimeDiff / (1000*60*60*24));
                const endDistributionHours = Math.floor((endDistributionTimeDiff % (1000*60*60*24)) / (1000*60*60));
                const endDistributionMinutes = Math.floor((endDistributionTimeDiff % (1000*60*60)) / (1000*60));
                const endDistributionSeconds = Math.floor((endDistributionTimeDiff % (1000*60)) / 1000);

                const nextDistributionStartDiff = formatedNextDStartTime - currentDateX;

                const nextDistributionStartDays = Math.floor(nextDistributionStartDiff / (1000*60*60*24));
                const nextDistributionStartHours = Math.floor((nextDistributionStartDiff % (1000*60*60*24)) / (1000*60*60));
                const nextDistributionStartMinutes = Math.floor((nextDistributionStartDiff % (1000*60*60)) / (1000*60));
                const nextDistributionStartSeconds = Math.floor((nextDistributionStartDiff % (1000*60)) / 1000);

                if (formattedCurrentDateX >= formattedDistributionEndDateX && distributionDateTimeDiff < 0 && endDistributionTimeDiff < 0)
                {
                    $('#distributionCountDays').text(nextDistributionStartDays);
                    $('#distributionCountHours').text(nextDistributionStartHours);
                    $('#distributionCountMinutes').text(nextDistributionStartMinutes);
                    $('#distributionCountSeconds').text(nextDistributionStartSeconds);

                    $('.ticket-served').hide().addClass('d-none');
                    $('.countdown-timer-holder').show().removeClass('d-none');
                }

                if (formattedCurrentDateX <= formattedDistributionStartTimeX && distributionDateTimeDiff > 0 && endDistributionTimeDiff > 0)
                {
                    $('#distributionCountDays').text(days);
                    $('#distributionCountHours').text(hours);
                    $('#distributionCountMinutes').text(minutes);
                    $('#distributionCountSeconds').text(seconds);

                    $('.ticket-served').hide().addClass('d-none');
                    $('.countdown-timer-holder').show().removeClass('d-none');
                }

                if (formattedCurrentDateX >= formattedDistributionStartTimeX && formattedCurrentDateX <= formattedDistributionEndDateX && distributionDateTimeDiff < 0 && endDistributionTimeDiff > 0)
                {
                    $('.countdown-timer-holder').hide().addClass('d-none');

                    $('.ticket-served').show().removeClass('d-none');
                }

                console.log(currentTicketNumber);

                if(currentTicketNumber == 0)
                {
                    $('.ticket-served').text('Distribution will start momentarily').css({
                        "font-size": "40px",
                        "font-weight": "500",
                        "text-align": "center"
                    });
                } else {
                    clearInterval(x);

                    $('.countdown-timer-holder').hide().addClass('d-none');

                    $('.ticket-served').show().removeClass('d-none');

                    $('.ticket-served').text(currentTicketNumber);

                    // clearInterval(y);

                    // Get the current date and time
                    let currentDate = new Date();

                    // Format the date as "Jan 14, 2025 9:02 PM"
                    let formattedDate = currentDate.toLocaleString('en-US', {
                        weekday: 'short',  // "Mon"
                        year: 'numeric',   // "2025"
                        month: 'short',    // "Jan"
                        day: 'numeric',    // "14"
                        hour: 'numeric',   // "9"
                        minute: '2-digit', // "02"
                        hour12: true       // "AM/PM"
                    });

                    // Format the output to match the exact format
                    let finalFormattedDate = formattedDate.replace(', ', ' ').replace(',', '').replace(':', '');

                    $('.distribution-start-time').val(finalFormattedDate)

                    console.log($(window).width());
                    
                    if ($(window).width() <= 430) 
                    {
                        console.log("below 430");

                        $('.ticket-served').css("font-size", "180px");
                    }

                    if ($(window).width() > 430 && $(window).width() <= 480) 
                    {
                        console.log("below 480");

                        $('.ticket-served').css("font-size", "270px");
                    }

                    if ($(window).width() > 480 && $(window).width() <= 768) 
                    {
                        console.log("below 768");

                        $('.ticket-served').css("font-size", "290px");
                    }

                    if ($(window).width() > 768) 
                    {
                        console.log("above 768");

                        $('.ticket-served').css("font-size", "470px");
                    }
                }

                var channel = pusher.subscribe('current-ticket');
                channel.bind('counter-updated', function(data) {

                    var ticketNumber=JSON.stringify(data.count);

                    console.log(ticketNumber);

                    if(ticketNumber == 0)
                    {
                        $('.ticket-served').text('Distribution will start momentarily').css({
                            "font-size": "40px",
                            "font-weight": "500",
                            "text-align": "center"
                        });
                    } else {
                        $('.countdown-timer-holder').hide().addClass('d-none');

                        $('.ticket-served').show().removeClass('d-none');

                        $('.ticket-served').text(ticketNumber);

                        clearInterval(x);

                        // Get the current date and time
                        let currentDate = new Date();

                        // Format the date as "Jan 14, 2025 9:02 PM"
                        let formattedDate = currentDate.toLocaleString('en-US', {
                            weekday: 'short',  // "Mon"
                            year: 'numeric',   // "2025"
                            month: 'short',    // "Jan"
                            day: 'numeric',    // "14"
                            hour: 'numeric',   // "9"
                            minute: '2-digit', // "02"
                            hour12: true       // "AM/PM"
                        });

                        // Format the output to match the exact format
                        let finalFormattedDate = formattedDate.replace(', ', ' ').replace(',', '').replace(':', '');

                        $('.distribution-start-time').val(finalFormattedDate)

                        console.log($(window).width());
                        
                        if ($(window).width() <= 430) 
                        {
                            console.log("below 430");

                            $('.ticket-served').css("font-size", "180px");
                        }

                        if ($(window).width() > 430 && $(window).width() <= 480) 
                        {
                            console.log("below 480");

                            $('.ticket-served').css("font-size", "270px");
                        }

                        if ($(window).width() > 480 && $(window).width() <= 768) 
                        {
                            console.log("below 768");

                            $('.ticket-served').css("font-size", "290px");
                        }

                        if ($(window).width() > 768) 
                        {
                            console.log("above 768");

                            $('.ticket-served').css("font-size", "470px");
                        }
                    }
                });

            

            },1000);



            

            var nowServingText = "{!! $msg !!}";

            // show time for a time zone
            function updateTime() {
                // Specify the timezone you want to display
                const timezone = 'America/Vancouver';

                // Create a new Date object with the current time in the specified timezone
                const now = new Date().toLocaleTimeString('en-US', { timeZone: timezone });

                // Update the content of the clock div with the current time
                document.getElementById('timeServed').textContent = now;
            }

            // Call updateTime() function every second to update the time
            setInterval(updateTime, 1000);

            // Call updateTime() once initially to display the time immediately
            updateTime();
        });
    </script>
</body>

</html>
