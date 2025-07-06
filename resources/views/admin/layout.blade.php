   <!DOCTYPE html>
   <html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <title>GoGo</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

      <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
      <!-- Bootstrap Icons CSS -->

      <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> 

      <!-- google fonts -->
     <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0"/>

      <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.0/font/bootstrap-icons.css" rel="stylesheet">
      <style>
         #backToTop,#backToBottom
         {
            visibility:hidden;
            font-size: 30px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            width: 40px;
            height: 50px;
            border-radius: 50%;
            background: #ffc107;
            top:calc(100% / 2);
            top:-moz-calc(100% / 2);
            top:-o-calc(100% / 2);
            right: calc(100% / 24);
            position:fixed;
            z-index: 1400;
            cursor:pointer;
            color:black;
         }

         #backToTop .svg-inline--fa,#backToBottom svg-inline--fa
         {
            color: white;
         }

         .card-img-preview
         {
               width: 120px;
               height: 120px;
               border: 2px solid black;
         }

         .preview-display-image
         {
               object-fit: cover;
               width: 100%;
               height: 100%;
         }

         #userLatestNoshowsTable tbody tr 
         {
               border:2px solid rgb(17, 15, 15); font-size:20px; font-weight:400; color:black;
         }

         #userLatestNoshowsTable tbody tr td
         {
               border:2px solid rgb(17, 15, 15); padding:5px;
         }

         #userLatestNoshowsTable tbody span
         {
               display: flex;
               align-items: center;
               justify-content: space-around;
               font-size: 20px;
               font-weight: 600;
               text-align: center;
               padding: 20px;
         }

         i {
            font-size: 20px;
         }

         /* Sidebar */
         .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1002;
            width: 270px;
            height: 100vh;
            background: #151A2D;
            transition: all 0.4s ease;
         }
         .sidebar.collapsed {
            width: 85px;
         }
         .sidebar .sidebar-header {
            display: flex;
            position: relative;
            padding: 25px 20px;
            align-items: center;
            justify-content: space-between;
         }
         .sidebar-header .header-logo img {
            width: 50px;
            height: 50px;
            display: block;
            object-fit: contain;
            border-radius: 40%;
         }

         .sidebar-toggler
         {
            position: absolute;
            right: 20px;
         }

         .sidebar-menu-button {
            z-index: 1002;
         }

         .sidebar-header .sidebar-toggler,
         .sidebar-menu-button {
            /* position: absolute;
            left: 20px; */
            height: 35px;
            width: 35px;
            color: #151A2D;
            border: none;
            cursor: pointer;
            display: flex;
            background: #EEF2FF;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: 0.4s ease;
         }
         .sidebar.collapsed .sidebar-header .sidebar-toggler {
         transform: translate(-4px, 65px);
         }
         .sidebar-header .sidebar-toggler span,
         .sidebar-menu-button span {
         font-size: 1.75rem;
         transition: 0.4s ease;
         }
         .sidebar.collapsed .sidebar-header .sidebar-toggler span {
         transform: rotate(180deg);
         }
         .sidebar-header .sidebar-toggler:hover {
         background: #d9e1fd;
         }
         .sidebar-nav .s-nav-list {
            list-style: none;
            display: flex;
            gap: 4px;
            padding: 0 15px;
            flex-direction: column;
            transform: translateY(15px);
            transition: 0.4s ease;
         }
         .sidebar .sidebar-nav .primary-nav {
            overflow-y: auto;
            scrollbar-width: thin;
            padding-bottom: 20px;
            height: calc(100vh - 227px);
            scrollbar-color: transparent transparent;
         }
         .sidebar .sidebar-nav .primary-nav:hover {
            scrollbar-color: #EEF2FF transparent;
         }
         .sidebar.collapsed .sidebar-nav .primary-nav {
            overflow: unset;
            transform: translateY(65px);
         }
         .sidebar-nav .s-nav-item .nav-link {
            color: #fff;
            display: flex;
            gap: 12px;
            white-space: nowrap;
            border-radius: 8px;
            padding: 11px 15px;
            align-items: center;
            text-decoration: none;
            border: 1px solid #151A2D;
            transition: 0.4s ease;
         }
         .sidebar-nav .s-nav-item:is(:hover, .open)>.nav-link:not(.dropdown-title)
         {
            color: #151A2D;
            background: #EEF2FF;
         }

         .sidebar-nav .s-nav-item .current-page,
         .current-page
         {
            color: #151A2D;
            background: #EEF2FF;
         }

         .sidebar .nav-link .nav-label {
            transition: opacity 0.3s ease;
         }
         .sidebar.collapsed .nav-link :where(.nav-label, .s-dropdown-icon) {
            opacity: 0;
            pointer-events: none;
         }
         .sidebar.collapsed .nav-link .s-dropdown-icon {
            transition: opacity 0.3s 0s ease;
         }
         .sidebar-nav .secondary-nav {
            position: absolute;
            bottom: 35px;
            width: 100%;
            background: #151A2D;
         }
         .sidebar-nav .s-nav-item {
            position: relative;
         }
         /* Dropdown Stylings */
         .sidebar-nav .dropdown-container .s-dropdown-icon {
            margin: 0 -4px 0 auto;
            transition: transform 0.4s ease, opacity 0.3s 0.2s ease;
         }
         .sidebar-nav .dropdown-container.open .s-dropdown-icon {
            transform: rotate(180deg);
         }
         .sidebar-nav .s-dropdown-menu {
            height: 0;
            overflow-y: hidden;
            list-style: none;
            padding-left: 15px;
            transition: height 0.4s ease;
         }
         .sidebar.collapsed .s-dropdown-menu {
            position: absolute;
            top: -10px;
            left: 100%;
            opacity: 0;
            height: auto !important;
            padding-right: 10px;
            overflow-y: unset;
            pointer-events: none;
            border-radius: 0 10px 10px 0;
            background: #151A2D;
            transition: 0s;
         }
         .sidebar.collapsed .s-dropdown-menu:has(.dropdown-link) {
            padding: 7px 10px 7px 24px;
         }
         .sidebar.sidebar.collapsed .s-nav-item:hover>.s-dropdown-menu {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(12px);
            transition: all 0.4s ease;
         }
         .sidebar.sidebar.collapsed .s-nav-item:hover>.s-dropdown-menu:has(.dropdown-link) {
            transform: translateY(10px);
         }
         .s-dropdown-menu .s-nav-item .nav-link {
            /* color: #F1F4FF; */
            padding: 9px 15px;
         }
         .sidebar.collapsed .s-dropdown-menu .nav-link {
            padding: 7px 15px;
         }
         .s-dropdown-menu .s-nav-item .nav-link.dropdown-title {
         display: none;
         color: #fff;
         padding: 9px 15px;
         }
         .s-dropdown-menu:has(.dropdown-link) .s-nav-item .dropdown-title {
         font-weight: 500;
         padding: 7px 15px;
         }
         .sidebar.collapsed .s-dropdown-menu .s-nav-item .dropdown-title {
         display: block;
         }
         .toggle-sidebar-menu {
            opacity: 1;
         }

         .main.collapsed
         {
            width: calc(100% - 85px);
            left: 85px;
         }
                              
         /* navbar */
         .main{
            position: absolute;
            width: calc(100% - 270px);
            left: 270px;
            min-height: 100vh;
            background: var(--white);
            transition: 0.5s;
         }

         .mainactive{
            width: calc( 100% - 200px );
            left: 200px;
         }

         .topbar{
            /* width: 90%; */
            height: 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 35px 19px;
         }

        .main.collapsed
        {
            width: calc(100% - 85px);
            left: 85px;
        }
                            
        /* navbar */
        .main{
            position: absolute;
            width: calc(100% - 270px);
            left: 270px;
            min-height: 100vh;
            background: var(--white);
            transition: 0.5s;
        }

        .mainactive{
            width: calc( 100% - 200px );
            left: 200px;
        }

        @media (max-width: 768px)
        {
            .sidebar-menu-button {
                position: fixed;
                left: 20px;
                top: 20px;
                height: 40px;
                width: 42px;
                display: flex;
                color: #F1F4FF;
                background: #151A2D;
            }
            .sidebar.collapsed {
                width: 270px;
                left: -270px;
            }
            .sidebar.collapsed .sidebar-header .sidebar-toggler {
                transform: none;
            }
            .sidebar.collapsed .sidebar-nav .primary-nav {
                transform: translateY(15px);
            }

            .main.collapsed
            {
                width: calc(100% - 0px);
                left: 0;
            }

            .topbar {
                position: relative;
            }

            .topbar-end {
                position: absolute;
                right: -40px;
            }
        } 

        @media(max-width:991px)
        {

            .main{
                width: 100vw;
                left: 0px;
            }

            .mainactive{
                left: 200px;
            }
        }

        @media(max-width:480px)
        {
            aside .navigationactive{
                width: 100%;
                left:0;
            }
        }

        @media(max-width:320px)
        {
            .navigationactive {
                width: 200px;
                left: 0;
            }

            .mainactive {
                left: 100px;
            }
        }
      </style>
      @yield('admin_tool_styles')
      @yield('index_styles')
      @yield('number_control_styles')
      @yield('overview_dashboard_styles')
      @yield('register_recipient_styles')
      @yield('view_recipients_styles')
      @yield('overview_dashboard_copy_styles')
      @yield('modify_user_styles')

      @yield('view_volunteer_group_signups_styles')
      @yield('view_user_styles')
      @yield('activity_log_styles')
      @yield('admin_activity_log_styles')
      @yield('admin_staffs_styles')
      @yield('update_pwd_styles')

      @yield('tickets_signups_dashboard_styles')
      @yield('admin_single_ticket_styles')
      @yield('admin_multiple_tickets_styles')
      @yield('multiple_ticket_details_styles')
      @yield('s_ticket_details_styles')
      
      @yield('error-styles')
      
   </head>
      <body>
         <div class="container">
            <div id='backToBottom'><i class="fa-solid fa-arrow-down"></i></div>
            <div id='backToTop'><i class="fa-solid fa-arrow-up"></i></div>
            @include('admin.sidebar')
            <div class="main">
               @include('admin.navbar')
               <div class="mt-2 mx-auto" style="width:100%; padding:3px 10px;">
                  @yield('content')
               </div>
            </div>
         </div>

         <!-- modals -->
         <div class="modal fade" id="updateUserDetailsModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <h4 class="modal-title text-center" id="custom-width-modalLabel">Update User Details</h4>
               </div>
               <form method="POST">
                  @csrf
                  <div class="modal-body">
                     <input type="hidden" id="updateUserId">
                     <div class="row mb-4 d-flex justify-content-center">
                        <div class="col-6 ">
                           <label for="firstName">First Name</label>
                           <input type="text" autocomplete="off" class="form-control w-100" id="updateFirstName" placeholder="First Name" />
                        </div>
                        <div class="col-6 ">
                           <label for="lastName">Last Name</label>
                           <input type="text" autocomplete="off" class="form-control w-100" id="updateLastName" placeholder="Last Name" />
                        </div>
                     </div>
               
                  <div class="row mb-4 d-flex justify-content-center">
                     <div class="col-6 ">
                        <label for="recordDateOfBirth">Date of Birth (MM/DD/YYYY)</label>
                        <input type="text" autocomplete="off" class="form-control w-100" id="updateDateOfBirth" name="date_of_birth" placeholder="Date of Birth (MM/DD/YYYY)" />
                     </div>
                           <div class="col-6 ">
                        <label for="recordCaseNumber">Case Number</label>
                        <input type="text" autocomplete="off" class="form-control w-100" id="updateCaseNumber" name="case_number" placeholder="Case Number" />
                     </div>
                  </div>
                     <div class="row mb-4 d-flex justify-content-center">
                     <div class="col-6">
                              <label for="proxyOption">Proxy</label>
                              <select id="proxyOption" class="form-control text-dark selected-proxy" style="width: 100%;">
                                 <option disabled value="">Select Proxy</option>
                                 <option value="Yes">Yes</option>
                                 <option value="No">No</option>
                              </select>
                           </div>
                  
                     <div class="col-6">
                              <label for="disableUserOption">Disable User</label>
                              <select id="disableUserOption" class="form-control text-dark selected-disable-option" style="width: 100%;">
                                 <option value="0">Yes</option>
                                 <option value="1">No</option>
                              </select>
                           </div>
                  </div>
                     <div class="row mb-4 d-flex justify-content-center">
                     <div class="col-12 ">
                        <label>User Latest No Shows</label>
                        <table style="width:100%; text-align:center;" id="userLatestNoshowsTable">
                                 <thead>
                                       <tr>
                                          <th style="border:2px solid rgb(17, 15, 15); padding:5px;">Ticket</th>
                                          <th style="border:2px solid rgb(17, 15, 15); padding:5px;">Date</th>
                                          <th style="border:2px solid rgb(17, 15, 15); padding:5px;">Time</th>
                                       </tr>
                                 </thead>
                                 <tbody></tbody>
                              </table>
                     </div>
                  </div>
                     <div class="row mb-4 d-flex justify-content-center">
                     <div class="col-12 ">
                        <label for="updateIdCardIssuedDate">ID Card Issued Date(MM/DD/YYYY)</label>
                        <input type="text" autocomplete="off" class="form-control w-100" id="updateIdCardIssuedDate" name="date_of_idcard_issue" placeholder="ID Card Issued Date (MM/DD/YYYY)" />
                     </div>
                  </div>
                     <div class="row mb-4 d-flex justify-content-center">
                        <div class="col-12 ">
                           <label for="updateIdCardFile">ID Card</label>
                           <input type="file" class="form-control w-100" accept="image/png, image/jpeg" id="updateIdCardFile" name="idcard_file" placeholder="ID Card File" />
                                 <div class="card-img-preview">
                                    <img class="preview-display-image" src=""/>
                                 </div>
                        </div>
                     </div>
                     <div class="row mb-4" id="createdAtDetails">
                        <div class="col-4 ">
                           <label>Staff Who created</label>
                           <input type="text" disabled="true" class="form-control w-100" id="staffName"/>
                        </div>
                        <div class="col-4 ">
                           <label>Date</label>
                           <input type="text" disabled="true" class="form-control w-100" id="staffDateCreated"/>
                        </div>
                     </div>
                  <div class="modal-footer">
                     <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                     <a class="btn btn-warning waves-effect view-userdetails-btn" target="_blank" href="">Tickets History</a>
                     <button type="button" class="btn btn-warning waves-effect close-modal" data-dismiss="modal">Close</button>
                     <button type="submit" class="btn btn-warning waves-effect update-userdetails-btn">Update</button>
                  </div>
               </form>   
            </div>
         </div>
      </div>

      <div class="modal fade" id="confirmUserDisableModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
         <div class="modal-dialog">
            <div class="modal-content">
               <form method="POST">
                  @csrf
                  <div class="modal-body">
                     <input type="hidden" class="user-id">
                     <div class="row section-groups m-2">
                           <div class="col-sm-12">
                              <p>Are you sure you want to disable the
                              user login?</p>
                           </div>
                     </div>
                  </div>
               
                  <div class="modal-footer">
                     <button type="button" class="btn btn-warning waves-effect close-disablelogin-modal" data-dismiss="modal">Close</button>
                     <button type="submit" class="btn btn-danger waves-effect disable-user-login-btn">Yes</button>
                  </div>
               </form> 
            </div>
         </div>
      </div>
      
      <div class="modal fade" id="confirmDeleteUserModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
         <div class="modal-dialog">
            <div class="modal-content">
               <form method="POST">
                  @csrf
                  <div class="modal-body">
                     <input type="hidden" class="user-id">
                     <div class="row section-groups m-2">
                           <div class="col-sm-12">
                              <p>Are You sure you want to Delete?</p>
                           </div>
                     </div>
                  </div>
               
                  <div class="modal-footer">
                     <ul style="list-style-type: none;" class="alert alert-warning d-none error-list col-12"></ul>
                     <button type="button" class="btn btn-warning waves-effect close-modal" data-dismiss="modal">Close</button>
                     <button type="submit" class="btn btn-danger waves-effect delete-user-btn">Delete</button>
                  </div>
               </form> 
            </div>
         </div>
      </div>

      <div class="modal fade" id="manageUserLoginModal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
         <div class="modal-dialog">
            <div class="modal-content">
                  <div class="modal-body">
                     <div class="row section-groups m-2">
                           <div class="col-sm-12">
                              <p class="manage-user-login-text"></p>
                           </div>
                     </div>
                  </div>
               
                  <div class="modal-footer">
                     <button type="button" class="btn btn-warning waves-effect manage-user-login-close-modal" data-dismiss="modal">Close</button>
                     <button type="button" class="btn btn-warning waves-effect manage-user-login-btn">Continue</button>
                  </div>
            </div>
         </div>
      </div>

    {{-- jquery --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    {{-- bootstrap --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>

    <!-- sweetalert -->
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    

      <script>
         window.onscroll = function () {
            if (pageYOffset >= 200) {
               document.getElementById('backToTop').style.visibility = "visible";
               document.getElementById('backToBottom').style.visibility = "hidden";
            } else {
               document.getElementById('backToTop').style.visibility = "hidden";
               document.getElementById('backToBottom').style.visibility = "visible";
            }
         };

         document.getElementById('backToTop').onclick = function()
         {
            scrollTo(document.body, 0, 0);
         }
         
         
         $("#backToBottom").click(function () {
            $("html, body").animate({
               scrollTop: $(document).height()
            }, 1000);
         });

         function scrollTo(element, to, duration) {
            var start = element.scrollTop,
               change = to - start,
               currentTime = 0,
               increment = 20;

            var animateScroll = function(){        
               currentTime += increment;
               var val = Math.easeInOutQuad(currentTime, start, change, duration);                        
               element.scrollTop = val; 
               if(currentTime < duration) {
                  setTimeout(animateScroll, increment);
               }
            };
            animateScroll();
         }

         Math.easeInOutQuad = function (t, b, c, d) {
            t /= d/2;
            if (t < 1) return c/2*t*t + b;
            t--;
            return -c/2 * (t*(t-2) - 1) + b;
         };


         $("#backToTop").click(function() {
            $("html").animate({ scrollTop: 0 }, "slow");
         });

         $(document).ready(function() {
            
            console.log(document.body.scrollHeight);

               var x = setInterval(function()
                {
                    const options = {
                    timeZone: 'America/Vancouver',
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                    };

                    const vancouverTime = new Date().toLocaleString('en-US', options);

                  //   console.log(vancouverTime);

                    $('#currentDate').text(vancouverTime);

                },1000);
         });

         // Toggle the visibility of a dropdown menu
         const toggleDropdown = (dropdown, menu, isOpen) => {

            dropdown.classList.toggle("open", isOpen);

            menu.style.height = isOpen ? `${menu.scrollHeight}px` : 0;
         };

         // Close all open dropdowns
         const closeAllDropdowns = () => {
            document.querySelectorAll(".dropdown-container.open").forEach((openDropdown) => {
               toggleDropdown(openDropdown, openDropdown.querySelector(".s-dropdown-menu"), false);
            });
         };

         // Attach click event to all dropdown toggles
         document.querySelectorAll(".s-dropdown-toggle").forEach((dropdownToggle) => {
            dropdownToggle.addEventListener("click", (e) => {
               e.preventDefault();
               const dropdown = e.target.closest(".dropdown-container");
               const menu = dropdown.querySelector(".s-dropdown-menu");
               const isOpen = dropdown.classList.contains("open");
               closeAllDropdowns(); // Close all open dropdowns
               toggleDropdown(dropdown, menu, !isOpen); // Toggle current dropdown visibility
            });
         });

         // Attach click event to sidebar toggle buttons
         document.querySelectorAll(".sidebar-toggler, .sidebar-menu-button").forEach((button) => {
            button.addEventListener("click", () => {
               closeAllDropdowns(); // Close all open dropdowns
               document.querySelector(".sidebar").classList.toggle("collapsed"); // Toggle collapsed class on sidebar
               document.querySelector(".main").classList.toggle("collapsed");
               
               // document.querySelector(".tabs .tab-body div.active").classList.toggle("collapsed");
            });
         });

         if (window.innerWidth > 1024){
            document.querySelector(".sidebar-menu-button").style.display = "none";
         }
         
         
         // Collapse sidebar by default on small screens
         if (window.innerWidth < 1024){
            document.querySelector(".sidebar").classList.add("collapsed");
            document.querySelector(".sidebar-menu-button").classList.toggle("toggle-sidebar-menu");
         }


         $(document).on('focusin', function(e) {
            if ($(e.target).closest(".tox").length) {
                e.stopImmediatePropagation();
            }
        });

         $(document).on('click', '.close-modal', function(e) {
            e.preventDefault();

            $('.error-list').html(" ").addClass('d-none');

            $('#resetQueueTicketsModal').modal('hide');

            $('#confirmDeleteReturnTimeModal').modal('hide');
            
            $('#addEditReturnTimeModal').modal('hide');

            $('#changePopupMessageModal').modal('hide');

            $('#addEditPopupMessageModal').modal('hide');

            $('#addEditDayTimeModal').modal('hide');

            $('#confirmDeleteDayModal').modal('hide');

            $('#addEditDateTimeModal').modal('hide');

            $('#viewPopupMessageModal').modal('hide');

            $('#confirmDeleteDateModal').modal('hide');

            $('#editNotificationMessageModal').modal('hide');

            $('#viewNotificationMessageModal').modal('hide');
                        
            $('#confirmTicketDeleteModal').modal('hide');
            
            $('#confirmUserCheckinModal').modal('hide');
                        
            $('#newVolunteerGroupSignupModal').modal('hide');

            $('#newVolunteerGroupUserModal').modal('hide');

            $('#confirmUserServingModal').modal('hide');
            
            $('#confirmSignUpDeleteModal').modal('hide');

            $('#confirmUserDeleteModal').modal('hide');

            $('#editVolunteerGroupSignupModal').modal('hide');
        });

      </script>

      @yield('number_control_script') 
      @yield('overview_dashboard_script')
      @yield('register_recipient_script')
      @yield('view_recipients_script')
      @yield('modify_user_script')
      @yield('overview_dashboard_copy_script')
      @yield('view_volunteer_group_signups_script')
      @yield('view_user_script')
      @yield('admin_tool_script')
      @yield('activity_log_script')
      @yield('admin_activity_log_script')
      @yield('admin_staffs_script')
      @yield('update_pwd_script')
      @yield('tickets_signups_dashboard_script')
      
      @yield('admin_single_ticket_script')
      @yield('admin_multiple_tickets_script')
      @yield('multiple_ticket_details_script')
      @yield('s_ticket_details_script')
      
      
      
      </body>
   </html>