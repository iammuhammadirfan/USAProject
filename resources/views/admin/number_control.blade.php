@extends('admin.layout')
@section('content')
@section('number_control_styles')
    {{-- alertify --}}
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>
    <!-- Default theme -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/default.min.css"/>
    <!-- Semantic UI theme -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/semantic.min.css"/>

    <style>
        .number-control-div
        {
            display: flex;
            justify-content: space-around;
            align-items: center;
            color: red;
            background-color: black;
            height: 600px;
        }

        .ticket-served
        {
            height: inherit;
            display: flex;
            justify-content: space-around;
            align-items: center;
            font-size: 470px;
            text-align: center;
        }

        .number-control-settings
        {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            align-items: center;
            height: 50px;
        }

        .number-control-settings a{
            background: rgb(252, 229, 214);
            color: black;
            font-size: 20px;
            border: none;
            outline: none;
        }

        /* .control-settings-div
        {
            width: 20%;
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 50px;
        } */
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
                height: inherit;
                font-size: 330px;
            }
        }

        @media(max-width:768px)
        {
            .ticket-served {
                height: inherit;
                font-size: 270px;
            }
        }

        @media(max-width:450px)
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
        }
    </style>
@stop

<div class="row mt-5">
    <div class="col-md-10 col-lg-10">
        <div style="display: flex; justify-content:space-between; align-items:center;">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-warning btn-sm border border-dark">Main Menu</a>
            <a href="/admin/overview-dashboard" class="btn btn-warning btn-sm border border-dark">Overview Dashboard</a>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-lg-10">
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
                <a class="btn btn-sm reset-ticket">Reset</a>
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
<br />

<div class="modal fade" id="continueTicketResetModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
       <div class="modal-content">
          <div class="modal-body">
            <p>Do you really want to reset the number to 0?
                Click continue to proceed.</p>
       </div>
       <div style="display: flex;
justify-content: space-around;
align-items: center;
margin: 5px;">
        <a class="btn btn-warning waves-effect border border-dark close-modal">Cancel</a>
        <a class="btn btn-warning waves-effect border border-dark continue-ticket-reset">Continue</a>
     </div>
       </div>
    </div>
</div>

<div class="modal fade" id="resetTicketsModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
       <div class="modal-content">
          <div class="modal-body">
            <p>Are You sure you want to reset and clear the table?Click continue to proceed.</p>
       </div>
       <div style="display: flex;
justify-content: space-around;
align-items: center;
margin: 5px;">
        <a class="btn btn-warning waves-effect border border-dark reset-tickets-modal-btn">Continue</a>
        <a class="btn btn-warning waves-effect border border-dark close-modal">Cancel</a>
        
     </div>
       </div>
    </div>
</div>

@endsection

@section('number_control_script')
    {{-- pusher js --}}
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    
    {{-- alertify --}}
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>

    {{-- moment js --}}
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script> 

    <script>
        $(document).ready(function() {
            var currentNumberServed=$('.ticket-served').text();

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
        })

        $(document).on('click', '.control-number', function(e) {
            e.preventDefault();

            var adjusted_value=$(this).attr('control-number');

            var current_served=$('.ticket-served').text();

            if($.isNumeric(current_served))
            {
                var current_ticket = current_served;
            } else {
                var current_ticket = 0;
            }

            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            var url = '/admin/adjust-served-ticket';

            $.ajax({
                url: url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: function(){
                    var data = new FormData();
                    data.append('adjusted_value',adjusted_value);
                    data.append('current_ticket',current_ticket);
                    data.append('_token',csrf);
                    return data;
                    console.log(data)
                }(),
                success: function(response) {

                    if (response.status == 200)
                    {
                        $('.ticket-served').text(response.ticket_number);
                    }
                }
            });
        });

        $(document).on('click', '.reset-ticket', function(e) {
            e.preventDefault();

            $('#continueTicketResetModal').modal('show');
        });

        $(document).on('click', '.close-modal', function(e) {
            e.preventDefault();

            $('#continueTicketResetModal').modal('hide');
        });

        $(document).on('click', '.continue-ticket-reset', function(e) {
            e.preventDefault();

            var url = '/admin/reset-current-ticket';
            $.ajax({
                url: url,
                method: 'get',
                success: function(response) {
                    $('.ticket-served').text('0');

                    $('#continueTicketResetModal').modal('hide');
                }
            });
        });
    </script>
@endsection