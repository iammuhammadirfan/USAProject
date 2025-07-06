<div class="top-bar">
    <div class="menu-container">
        <?php
            $current_url = Route::currentRouteName();
        ?>

        @if (Auth::check())
            @if(Auth::user()->role != 2 && $current_url == 'multiple_users_ticket_details' || 
                Auth::user()->role != 2 && $current_url == 'one_ticket_details')
                <?php $toggle_nav = 'd-none'; ?>
            @else
                <?php $toggle_nav = 'd-block'; ?>
            @endif
        @else
            <?php $toggle_nav = 'd-block'; ?>
        @endif

        

        <button class="navbar-toggler {{ $toggle_nav }}" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse" id="navbarNav">
            <div class="p-2" id="menuWrapper">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link no-wrap" href="{{ url('/number-served') }}">View Number Being Served</a>
                    </li>
                    <li class="nav-item">
                        <!-- check if a user is logged in and has a ticket for today -->
                        @guest
                            <?php
                                $clickable_class = "check-ticket";
                                $userID = "";
                                $url ="#"
                            ?>
                        @else
                            <?php
                                $ticketDetails=DB::table('generated_tickets')
                                ->where(['status'=>1,'is_cancelled'=>0,'user_id'=>Auth::user()->id])
                                ->select('id','multiple_id')
                                ->first();
                            ?>
                            

                            @if($ticketDetails == Null)
                            <?php
                                $clickable_class = "check-ticket";
                                 $userID = "{{ Auth::user()->id }}";
                                $url ="#";
                            ?>
                            @elseif(Auth::user()->role == '1' && $ticketDetails !== Null)
                            <?php
                                $clickable_class = "pe-none";
                                 $userID = "";
                                $url ="#";
                            ?>
                            @elseif ($ticketDetails->multiple_id == Null)
                            <?php
                                $clickable_class = "";
                                $userID = "";
                                $url ='/one-ticket-details/'.$ticketDetails->id;
                            ?> 
                            @elseif ($ticketDetails->multiple_id !== Null)
                            <?php
                                $clickable_class = "";
                                $userID = " ";
                                $url ='/multiple-users-ticket-details/'.$ticketDetails->multiple_id;
                            ?>
                            @endif
                           
                        @endguest
                        <a class="nav-link {{ $clickable_class }}" href="{{ $url }}" user-id="{{ $userID }}">My Ticket</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/faq') }}">F.A.Q.</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://www.newhopemeadowview.com/food-ministry/" target="_blank">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/contact-us') }}">Contact Us</a>
                    </li>

                    <!-- Log Off -->
                    <li class="nav-item mt-3 border-top pt-2">
                        @guest
                            <a class="nav-link text-danger" href="/">Log In</a>
                        @else
                            <a class="nav-link text-danger" href="/logout"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log Off</a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                        @endguest
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>