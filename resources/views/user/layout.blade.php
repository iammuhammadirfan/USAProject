<!DOCTYPE html>
                    <html lang="en">

                    <head>
                        <meta charset="UTF-8">
                        <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
                        <meta name="csrf-token" content="{{ csrf_token() }}">
                        <title>GoGo</title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

                        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
                        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.0/font/bootstrap-icons.css" rel="stylesheet">

                        <!-- sweetalert -->
                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

                    <style>
                        body {
                            background-color: #f8f9fa; /* Light Gray */
                        }

                        .modal-backdrop {
                            display: none !important;
                        }

                        /* navbar */
                        .top-bar
                        {
                              display: flex;
                              align-items: center;
                              justify-content: space-between;
                              padding: 10px 0;
                        }

                        /* Hamburger button */
                        .navbar-toggler {
                           border: none;
                           background: transparent;
                           padding: 0;
                        }

                        /* Hamburger icon (three bars) */
                        .navbar-toggler-icon {
                           display: block;
                           width: 30px;
                           height: 3px;
                           background-color: #333;
                           position: relative;
                           margin: 6px 0;
                        }

                        .navbar-toggler-icon::before,
                        .navbar-toggler-icon::after {
                           content: "";
                           position: absolute;
                           width: 30px;
                           height: 3px;
                           background-color: #333;
                           left: 0;
                        }

                        .navbar-toggler-icon::before {
                           top: -8px;
                           height: 4px;
                        }

                        .navbar-toggler-icon::after {
                           bottom: -8px;
                           height: 4px;
                        }

                        /* Dropdown menu styling */
                        #navbarNav {
                           background-color: #f8f9fa;
                           border-radius: 6px;
                           box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                           width: 250px;
                           position: absolute;
                           top: 70px;
                           z-index: 1;
                        }

                        /* Show menu when collapsed */
                        .collapse.show {
                           display: block;
                        }

                        /* Navbar links */
                        .navbar-nav .nav-link {
                           font-weight: 500;
                           color: #000;
                           padding: 12px 16px; 
                           display: block;
                           width: 100%; 
                        }

                        /* Navbar link hover effect */
                        .navbar-nav .nav-link:hover {
                           background-color: #e9ecef;
                           text-decoration: none;
                        }

                        .navbar-nav .nav-link.no-wrap {
                           white-space: nowrap;
                        }

                        /* Highlight on hover spans the full width of the link */
                        .navbar-nav .nav-item:hover .nav-link {
                           background-color: #e9ecef; 
                        }

                        /* Styling for the Log Off button */
                        .navbar-nav .text-danger {
                           color: #dc3545 !important;
                        }

                        .manage-ticket-div,.view-ticket-div
                        {
                           text-align: center;
                           margin: 20px;
                        }

                        .view-id-details
                        {
                           display: flex;
                           flex-direction: column;
                           align-items: center;
                        }

                        /* .spinner {
                           border: 5px solid #f3f3f3;
                           border-top: 5px solid #3498db;
                           border-radius: 50%;
                           width: 50px;
                           height: 50px;
                           animation: spin 1s linear infinite;
                           margin: 20px auto;
                        }

                        @keyframes spin {
                           0% { transform: rotate(0deg); }
                           100% { transform: rotate(360deg); }
                        }

                        .loading-spinner {
                           position: fixed;
                           top: 0;
                           left: 0;
                           width: 100%;
                           height: 100%;
                           background: rgba(0, 0, 0, 0.5);
                           display: flex;
                           flex-direction: column;
                           justify-content: center;
                           align-items: center;
                           color: white;
                           z-index: 1000;
                        } */
                        
                    </style>
                     @yield('login_styles')
                     @yield('one_ticket_details_styles')
                     @yield('select_multiple_tickets_details_styles')
                     @yield('multiple_users_ticket_details_styles')
                     @yield('ticket_options_styles')
                     @yield('forgot_password_styles')
                     @yield('now_serving_styles')
                     @yield('faq_styles')
                    </head>
                    <body class="bg-white">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-10 mt-2 mx-auto">
                                    @include('user.navbar')
                                    @yield('content')
                                </div>
                            </div>
                            
                        <!-- jquery -->
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

                        <!-- bootstrap -->
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.min.js"></script>

                        <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->

                        <!-- sweetalert -->
                        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                        

                        <script>

                            document.addEventListener('click', function (event) {
                                const menu = document.getElementById('navbarNav');
                                const toggleBtn = document.querySelector('.navbar-toggler');
                                const isOpen = menu.classList.contains('show');

                                // If the menu is open and the click is outside the menu or the button, close the menu
                                if (isOpen && !menu.contains(event.target) && !toggleBtn.contains(event.target)) {
                                    bootstrap.Collapse.getInstance(menu).hide();
                                }
                            });

                           $(document).on('click', '.check-ticket', function()
                           {
                              var userID = $(this).attr('user-id');

                              if(typeof userID === 'undefined' || userID === "")
                              {
                                 var msg='You must be signed in to view your ticket number';

                                 var redirecturl = '/';
                              } else {
                                 var msg='You have not been issued a ticket today';

                                 var redirecturl = '/user/get-ticket-options';
                              }

                              // swal({
                              //    title: 'Success!',
                              //    text: msg,
                              //    type: 'success',
                              //    confirmButtonText: 'OK'
                              // }).then(() => {
                              //    console.log('triggered redirect here');
                              // });

                              Swal.fire(msg).then(() => {
                                 window.location.href = redirecturl;
                              });

                              // ;

                           })

                           document.addEventListener('DOMContentLoaded', function() {
                              // Get all accordion items
                              const accordionItems = document.querySelectorAll('#faqAccordion .accordion-collapse');
                              

                              console.log(accordionItems);
                              
                              // Initialize Bootstrap Collapse instances
                              const collapseInstances = [];
                              accordionItems.forEach(item => {
                                    collapseInstances.push(new bootstrap.Collapse(item, {
                                       toggle: false // Disable automatic toggle behavior
                                    }));
                              });

                              // Expand All functionality
                              document.getElementById('expandAllBtn').addEventListener('click', function() {
                                    accordionItems.forEach(item => {
                                       const instance = bootstrap.Collapse.getInstance(item);
                                       if (instance) {
                                          instance.show();
                                       }
                                    });
                                    
                                    // Update all accordion buttons to 'expanded' state
                                    document.querySelectorAll('#faqAccordion .accordion-button').forEach(button => {
                                       button.classList.remove('collapsed');
                                       button.setAttribute('aria-expanded', 'true');
                                    });
                              });

                              // Collapse All functionality
                              document.getElementById('collapseAllBtn').addEventListener('click', function() {
                                    accordionItems.forEach(item => {
                                       const instance = bootstrap.Collapse.getInstance(item);
                                       if (instance) {
                                          instance.hide();
                                       }
                                    });
                                    
                                    // Update all accordion buttons to 'collapsed' state
                                    document.querySelectorAll('#faqAccordion .accordion-button').forEach(button => {
                                       button.classList.add('collapsed');
                                       button.setAttribute('aria-expanded', 'false');
                                    });
                              });
                           });

                        </script>

                        @yield('login_script')
                        @yield('one_ticket_details_script')
                        @yield('select_multiple_tickets_details_script')
                        @yield('multiple_users_ticket_details_script')
                        @yield('ticket_options_script')
                        @yield('forgot_password_script')
                        @yield('now_serving_script')
                        @yield('faq_script')
                    </body>

                    </html>
