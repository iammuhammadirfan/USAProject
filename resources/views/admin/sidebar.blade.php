<aside class="sidebar">
    <header class="sidebar-header">
        <!-- <img src="/tgog/tgoglogo.webp" alt="logo"> -->
        <a href="/" class="header-logo"><img src="/tgoglogo.webp" alt="logo"></a>
        <button class="sidebar-toggler">
            <span class="material-symbols-rounded">chevron_left</span>
        </button>
    </header>
    <nav class="sidebar-nav">
        <ul class="s-nav-list primary-nav">
            <li class="s-nav-item">
                <a href="/admin/dashboard" class="nav-link">
                    <i class="fa-solid fa-home"></i>
                    <span class="nav-label">Home</span>
                </a>
            </li>
            
            <li class="s-nav-item dropdown-container">
    <a href="#" class="nav-link s-dropdown-toggle">
        <i class="fa-solid fa-table"></i>
        <span class="nav-label">Ticket Sign Ups</span>
        <span class="s-dropdown-icon material-symbols-rounded">keyboard_arrow_down</span>
    </a>
    <ul class="s-dropdown-menu">
        <li class="s-nav-item"><a href="/admin/all-signups" class="nav-link dropdown-link">All Sign Ups</a></li>
        <li class="s-nav-item"><a href="/admin/no-shows" class="nav-link dropdown-link">No Shows</a></li>
    </ul>
</li>
            
            
            
          <!--  <li class="s-nav-item dropdown-container">
                <a href="#" class="nav-link s-dropdown-toggle">
                    <i class="fa-solid fa-file-spreadsheet"></i>
                    <span class="nav-label">Ticket Sign Ups</span>
                    <span class="s-dropdown-icon material-symbols-rounded">keyboard_arrow_down</span></a>
                <ul class="s-dropdown-menu">
                    <li class="s-nav-item"><a href="/admin/all-signups" class="nav-link dropdown-link">All Sign Ups</a></li>
                    <li class="s-nav-item"><a href="/admin/no-shows" class="nav-link dropdown-link">No Shows</a></li>
                </ul>
            </li> -->
            
            
            <li class="s-nav-item">
                <a href="/admin/number-control" class="nav-link">
                    <i class="fa-solid fa-hourglass-end"></i>
                    <span class="nav-label">Number Control</span>
                </a>
            </li>
            <li class="s-nav-item dropdown-container">
                <a href="#" class="nav-link s-dropdown-toggle">
                    <i class="fa-solid fa-gauge"></i>
                    <span class="nav-label">Dashboard</span>
                    <span class="s-dropdown-icon material-symbols-rounded">keyboard_arrow_down</span></a>
                <ul class="s-dropdown-menu">
                    <li class="s-nav-item"><a href="/admin/overview-dashboard" class="nav-link dropdown-link">Overview - All</a></li>
                    <li class="s-nav-item"><a href="/admin/tickets-signups-dashboard" class="nav-link dropdown-link">Ticket Sign Ups</a></li>
                    <li class="s-nav-item"><a href="/admin/volunteer-group-dashboard" class="nav-link dropdown-link">Volunteer/Group Homes</a></li>
                </ul>
            </li>
            <li class="s-nav-item dropdown-container">
                <a href="#" class="nav-link s-dropdown-toggle">
                    <i class="fa-solid fa-ticket"></i>
                    <span class="nav-label">Issue Ticket</span>
                    <span class="s-dropdown-icon material-symbols-rounded">keyboard_arrow_down</span></a>
                <ul class="s-dropdown-menu">
                    <li class="s-nav-item"><a href="/admin/get-single-ticket" class="nav-link dropdown-link">Single Ticket</a></li>
                    <li class="s-nav-item"><a href="/admin/multiple-tickets-details/{{ Auth::user()->id }}" class="nav-link dropdown-link">Multiple Tickets</a></li>
                    <li class="s-nav-item"><a href="/admin/get-volunteer-signups" class="nav-link dropdown-link">Volunteer Sign Up</a></li>
                    <li class="s-nav-item"><a href="/admin/get-group-signups" class="nav-link dropdown-link">Group Home Sign Up</a></li>
                </ul>
            </li>
            <li class="s-nav-item dropdown-container">
                <a href="#" class="nav-link s-dropdown-toggle">
                    <i class="fa-solid fa-layer-group"></i>
                    <span class="nav-label">Volunteers/Group</span>
                    <span class="s-dropdown-icon material-symbols-rounded">keyboard_arrow_down</span></a>
                <ul class="s-dropdown-menu">
                    <li class="s-nav-item"><a href="/admin/get-single-ticket" class="nav-link dropdown-link">Volunteer Sign Up</a></li>
                    <li class="s-nav-item"><a href="/admin/multiple-tickets-details/{{ Auth::user()->id }}" class="nav-link dropdown-link">Group Home Sign Up</a></li>
                    <li class="s-nav-item"><a href="/admin/get-volunteer-signups" class="nav-link dropdown-link">Volunteer Checkin</a></li>
                    <li class="s-nav-item"><a href="/admin/volunteer-approved-name" class="nav-link dropdown-link">Approved Name</a></li>
                </ul>
            </li>
            <li class="s-nav-item dropdown-container">
                <a href="#" class="nav-link s-dropdown-toggle">
                   <i class="fa-solid fa-user-plus"></i>
                    <span class="nav-label">Recipients</span>
                    <span class="s-dropdown-icon material-symbols-rounded">keyboard_arrow_down</span></a>
                <ul class="s-dropdown-menu">
                    <li class="s-nav-item"><a href="/admin/register-new-user" class="nav-link dropdown-link">Add New Recipient</a></li>
                    <li class="s-nav-item"><a href="/admin/search-users" class="nav-link dropdown-link">Search Recipient</a></li>
                </ul>
            </li>
            <li class="s-nav-item dropdown-container">
                <a href="#" class="nav-link s-dropdown-toggle">
                    <i class="fa-solid fa-user"></i>
                    <span class="nav-label">Account</span>
                    <span class="s-dropdown-icon material-symbols-rounded">keyboard_arrow_down</span></a>
                <ul class="s-dropdown-menu">
                    @can('access-super-admin')
                    <li class="s-nav-item"><a href="/admin/staff-activity-log" class="nav-link dropdown-link">Activity Log</a></li>
                    @endcan
                    <li class="s-nav-item"><a href="/admin/admin-activity-log" class="nav-link dropdown-link staffs">My Activity Log</a></li>
                    <li class="s-nav-item"><a href="/admin/update-staff-password" class="nav-link dropdown-link">Update Password</a></li>
                </ul>
            </li>
            @can('access-super-admin')
            <li class="s-nav-item dropdown-container">
                <a href="#" class="nav-link s-dropdown-toggle">
                    <i class="fa-solid fa-gear"></i>
                    <span class="nav-label">System Settings</span>
                    <span class="s-dropdown-icon material-symbols-rounded">keyboard_arrow_down</span>
                </a>
                
                <ul class="s-dropdown-menu">
                    <li class="s-nav-item">
                        <a href="/admin/password-db-mgt" class="nav-link dropdown-link">Database Management</a>
                    </li>    
                    <li class="s-nav-item">
                        <a href="/admin/memos" class="nav-link dropdown-link">Memos</a>
                    </li>
                    <li class="s-nav-item">
                        <a href="/admin/login-days" class="nav-link dropdown-link">Login Days</a>
                    </li>
                    <li class="s-nav-item">
                        <a href="/admin/notification-messages" class="nav-link dropdown-link">Notification Messages</a>
                    </li>
                    <li class="s-nav-item">
                        <a href="/admin/ticket-limit" class="nav-link dropdown-link">Ticket Limit</a>
                    </li>
                    <li class="s-nav-item">
                        <a href="/admin/return-times" class="nav-link dropdown-link">Return Times</a>
                    </li>
                    <li class="s-nav-item">
                        <a href="/admin/distribution-times" class="nav-link dropdown-link">Distribution Time</a>
                    </li>
                </ul>
            </li>
            @endcan
            <li class="s-nav-item">
                <a href="/" class="nav-link">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="nav-label">Log Off</span>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </a>
            </li>
        </ul>
    </nav>
</aside>