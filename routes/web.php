<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\VolunteerController;
use App\Http\Controllers\Admin\AdminToolController;
use App\Http\Controllers\Admin\ForgetController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\RegisterRecipientScreenController;
use App\Http\Controllers\ticketManagementController;
use App\Http\Controllers\User\TicketServiceController;
use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



Route::GET('/', [LoginController::class, 'showLoginForm'])->name('home');

Route::get('/contact-us', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'sendContactForm'])->name('contact.send');
Route::GET('faq', [ContactController::class, 'faq'])->name('faq');

Route::get('number-served', [ticketManagementController::class, 'number_served'])->name('number.served');
Route::get('get-ticket-served', [ticketManagementController::class, 'get_ticket_served'])->name('get_ticket_served');
// Route::middleware('throttle:ticket-requests')->group(function () {
//     Route::POST('/login', [LoginController::class, 'login'])->name('login');     
// });
Route::POST('/login', [LoginController::class, 'login'])->name('login');
Route::POST('/permit-login', [LoginController::class, 'permit_login'])->name('permit_login');

Route::POST('/custom-login', [LoginController::class, 'custom_login'])->name('custom_login');
Route::POST('/logout', [LoginController::class, 'logout'])->name('logout');
Route::GET('forget/user', [ForgetController::class, 'forgetUser'])->name('admin.forget.user');
Route::any('forget/user/caseNumber', [ForgetController::class, 'forgetUserCaseNumber'])->name('admin.forget.userCaseNumber');

Route::get('get-no-shows-tickets', [TicketServiceController::class, 'get_noshows_tickets'])->name('get_noshows_tickets');
// Route::get('admin/general-dashboard', [AdminController::class, 'index'])->name('general-dashboard');
Route::get('general-dashboard', [AdminController::class, 'general_dashboard'])->name('general_dashboard');

Route::get('adminlogin', [LoginController::class, 'admin_login'])->name('admin_login');

Route::get('manage-duplicates', [LoginController::class, 'manage_duplicates'])->name('manage_duplicates');

Route::get('reset-error-tickets', [RegisterRecipientScreenController::class, 'reset_error_tickets'])->name('reset_error_tickets');

Route::group(['as' => 'admin.', 'prefix' => 'admin', 'middleware' => ['auth', 'admin']], function () {
    Route::get('update-staff-password', [AdminController::class, 'update_staff_password'])->name('update_staff_password');

    Route::post('update-admin-staff-password', [AdminController::class, 'update_admin_staff_password'])->name('update_admin_staff_password');



    // Route::get('staffs', [AdminController::class, 'staffs'])->name('staffs');

    Route::group(['middleware' => ['can:access-super-admin']], function () {
        Route::get('password-db-mgt', [AdminToolController::class, 'password_db_mgt'])->name('password_db_mgt');

        Route::get('memos', [AdminToolController::class, 'memos'])->name('memos');

        Route::get('login-days', [AdminToolController::class, 'login_days'])->name('login_days');

        Route::get('notification-messages', [AdminToolController::class, 'notification_messages'])->name('notification_messages');

        Route::get('ticket-limit', [AdminToolController::class, 'ticket_limit'])->name('ticket_limit');

        Route::get('return-times', [AdminToolController::class, 'return_times'])->name('return_times');

        Route::get('distribution-times', [AdminToolController::class, 'distribution_times'])->name('distribution_times');

        Route::get('staff-activity-log', [AdminController::class, 'staff_activity_log'])->name('staff_activity_log');
    });

    Route::get('all-signups', [AdminToolController::class, 'all_signups'])->name('all_signups');

    Route::get('no-shows', [AdminToolController::class, 'no_shows'])->name('no_shows');

    Route::get('number-control', [AdminToolController::class, 'number_control'])->name('number_control');

    Route::get('admin-activity-log', [AdminController::class, 'admin_activity_log'])->name('admin_activity_log');

    Route::get('tickets-signups-dashboard', [AdminToolController::class, 'tgog_ticket_signups_dashboard'])->name('tgog_ticket_signups_dashboard');

    Route::get('overview-dashboard', [AdminToolController::class, 'overview_dashboard'])->name('overview_dashboard');

    Route::get('volunteer-group-dashboard', [AdminToolController::class, 'volunteer_group_dashboard'])->name('volunteer_group_dashboard');

    Route::get('get-single-ticket', [AdminToolController::class, 'get_single_ticket'])->name('get_single_ticket');

    Route::get('multiple-tickets-details/{id}', [AdminToolController::class, 'multiple_ticket_details'])->name('multiple_ticket_details');

    Route::get('get-volunteer-signups', [AdminToolController::class, 'volunteer_signups'])->name('volunteer_signups');

    Route::get('get-group-signups', [AdminToolController::class, 'group_signups'])->name('group_signups');

    Route::get('register-new-user', [AdminToolController::class, 'register_new_user'])->name('register_new_user');

    Route::get('search-users', [AdminToolController::class, 'search_users'])->name('search_users');

    Route::get('view-user/{id}', [AdminToolController::class, 'view_user'])->name('view_user');

    Route::get('dashboard', [AdminToolController::class, 'admin_dashboard'])->name('admin_dashboard');

    Route::get('admin-memos', [AdminToolController::class, 'admin_memos'])->name('admin_memos');


    Route::post('manage-distribution-times', [TicketServiceController::class, 'manage_distribution_times'])->name('manage_distribution_times');

    Route::get('get-users-details', [TicketServiceController::class, 'get_users_details'])->name('get_users_details');

    Route::get('get-ticket-details', [TicketServiceController::class, 'get_ticket_details'])->name('get_ticket_details');

    Route::get('volunteer-approved-name', [VolunteerController::class, 'index'])->name('index');

    Route::get('volunteer-users-home', [VolunteerController::class, 'volunteer_users'])->name('volunteer_users');

    Route::get('volunteer-check-in', [VolunteerController::class, 'volunteer_checkIn'])->name('volunteer_checkIn');
    // Route::get('dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::post('volunteer-check-in', [VolunteerController::class, 'store'])->name('store');;
    Route::get('volunteer-check-in/{id}/edit', [VolunteerController::class, 'edit'])->name('edit');
    Route::put('volunteer-check-in/{id}', [VolunteerController::class, 'update'])->name('update');
    Route::delete('volunteer-check-in/{id}', [VolunteerController::class, 'destroy'])->name('destroy');
    
    Route::post('volunteer-assign-case_number-delete', [VolunteerController::class, 'assign_case_number_delete'])->name('assign_case_number_delete');
    Route::post('assignUp-case-delete', [VolunteerController::class, 'assignUp_case_delete'])->name('assignUp_case_delete');

    Route::get('volunteer-group-home-sign-up', [VolunteerController::class, 'volunteer_group_signUp'])->name('volunteer_group_signUp');
    
    Route::get('volunteer-cases-signup/{id}', [VolunteerController::class, 'volunteer_getCaseNumber'])->name('volunteer_getCaseNumber');


    // Route::get('number-control', [AdminController::class, 'number_control'])->name('number_control');

    Route::get('reset-current-ticket', [AdminController::class, 'reset_current_ticket'])->name('reset_current_ticket');

    Route::get('get-tickets', [AdminController::class, 'get_tickets'])->name('get_tickets');
    Route::get('view-signups-table-pdf', [AdminController::class, 'view_signups_table_pdf'])->name('view_signups_table_pdf');


    Route::get('get-users-without-tickets', [AdminController::class, 'get_users_without_tickets'])->name('get_users_without_tickets');
    Route::get('volunteer-group-home-users', [AdminController::class, 'volunteer_group_home_users'])->name('volunteer_group_home_users');
    Route::post('manage-volunteer-group-home-users', [AdminController::class, 'manage_volunteer_group_home_users'])->name('manage_volunteer_group_home_users');
    Route::post('add-volunteer-users', [AdminController::class, 'add_volunteer_users'])->name('add_volunteer_users');
    Route::get('get-volunteer-group-home-users', [AdminController::class, 'get_volunteer_group_home_users'])->name('get_volunteer_group_home_users');
    Route::post('delete-volunteer-group-user', [AdminController::class, 'delete_volunteer_group_user'])->name('delete_volunteer_group_user');

    Route::get('volunteer-group-signups', [AdminController::class, 'volunteer_group_signups'])->name('volunteer_group_signups');
    Route::post('delete-volunteer-group-signup', [AdminController::class, 'delete_volunteergroup_signup'])->name('delete_volunteergroup_signup');
    Route::get('get-volunteer-group-signup-details/{id}', [AdminController::class, 'get_volunteer_group_signup_details'])->name('get_volunteer_group_signup_details');
    Route::get('volunteer-signups', [AdminController::class, 'volunteer_signups'])->name('volunteer_signups');
    Route::post('manage-volunteer-group-signup', [AdminController::class, 'manage_volunteer_group_signup'])->name('manage_volunteer_group_signup');
    Route::get('group-home-signups', [AdminController::class, 'group_signups'])->name('group_signups');
    Route::post('served-in-status', [AdminController::class, 'served_in_status'])->name('served_in_status');
    Route::get('print-id-card-pdf/{id}', [RegisterRecipientScreenController::class, 'print_id_card_pdf'])->name('print_id_card_pdf');

    Route::get('search-ticket', [AdminController::class, 'search_ticket'])->name('search_ticket');
    Route::post('delete-ticket', [AdminController::class, 'delete_ticket'])->name('delete_ticket');
    Route::post('check-in-status', [AdminController::class, 'check_in_status'])->name('check_in_status');
    Route::post('check-in-barcode-scan', [AdminController::class, 'check_in_barcode_scan'])->name('check_in_barcode_scan');
    Route::post('reset-tickets', [AdminController::class, 'reset_tickets'])->name('reset_tickets');

    Route::post('adjust-served-ticket', [AdminController::class, 'adjust_served_ticket'])->name('adjust_served_ticket');

    Route::get('tool', [AdminToolController::class, 'index'])->name('tool.index');

    Route::get('exports', [AdminToolController::class, 'export'])->name('export.excel');
    Route::POST('import/user', [AdminToolController::class, 'importUser'])->name('import.excel');
    Route::any('print/{id}', [AdminController::class, 'print'])->name('print');
    Route::GET('token/print', [AdminController::class, 'tokenPrint'])->name('print.token');
    Route::POST('assign/case/number', [AdminController::class, 'assignCaseNumber'])->name('assign.caseNumber');
    Route::POST('get/recipient/number', [AdminController::class, 'getRecipientNumber'])->name('get.recipient.number');
    Route::POST('enable/user/login', [AdminController::class, 'enableUserlogin'])->name('enableuser.login');
    Route::POST('disable/user/login', [AdminController::class, 'disableUserlogin'])->name('disableUser.login');
    Route::POST('manage-user-login', [AdminToolController::class, 'manage_user_login'])->name('manage.user_login');

    Route::POST('admin-password/{id}', [AdminController::class, 'admin_password_reset']);
    Route::post('distribution/start/time', [AdminToolController::class, 'distributionStartTime'])->name('distribution.startTime');
    Route::post('Interval-Time', [AdminToolController::class, 'IntervalTime'])->name('Interval-Time');

    Route::get('get-memos', [AdminToolController::class, 'getMemos'])->name('get.memos');
    Route::post('create-update-memo', [AdminToolController::class, 'create_update_memo'])->name('create-update.memo');
    Route::post('enable-disable-memo', [AdminToolController::class, 'enable_disable_memo'])->name('enable-disable.memo');
    Route::get('get-memo-details/{id}', [AdminToolController::class, 'get_memo_details'])->name('get_details.memo');

    Route::get('get-notification-msgs', [AdminToolController::class, 'get_notificationmsgs'])->name('get.notificationmsgs');
    Route::post('update-notification-msgs', [AdminToolController::class, 'update_notification_msgs'])->name('update.notification_msgs');
    Route::get('get-notification-msgs-details/{id}', [AdminToolController::class, 'get_notification_msgs_details'])->name('get_details.notification_msgs');

    Route::get('get-login-days', [AdminToolController::class, 'getLoginDays'])->name('get.login_days');
    Route::post('create-update-login-day', [AdminToolController::class, 'create_update_login_day'])->name('create-update.login-day');
    Route::get('get-login-day-details/{id}', [AdminToolController::class, 'get_login_day_details'])->name('get_details.login_day');
    Route::post('delete-login-day', [AdminToolController::class, 'delete_login_day'])->name('delete.login_day');

    Route::get('get-added-dates', [AdminToolController::class, 'get_added_dates'])->name('get.added_dates');
    Route::post('create-update-added-date', [AdminToolController::class, 'create_update_added_date'])->name('create-update.added-date');
    Route::get('get-added-date-details/{id}', [AdminToolController::class, 'get_added_date_details'])->name('get_details.added_date');
    Route::post('delete-added-date', [AdminToolController::class, 'delete_added_date'])->name('delete.added_date');

    Route::post('manage-ticket-return-times', [AdminToolController::class, 'manage_ticket_return_times'])->name('manage_ticket_return_times');
    Route::get('get-return-times', [AdminToolController::class, 'get_return_times'])->name('get.return_times');
    Route::post('create-update-return-time', [AdminToolController::class, 'create_update_return_time'])->name('create-update.return_time');
    Route::get('get-return-time-details/{id}', [AdminToolController::class, 'get_return_time_details'])->name('get_details.return-time_details');
    Route::post('delete-return-time', [AdminToolController::class, 'delete_return_time'])->name('delete.return_time');



    Route::get('verify-admin-casenumber', [TicketServiceController::class, 'verify_admin_casenumber'])->name('verify_admin_casenumber');

    // Route::get('verify-admin-case-number', [TicketServiceController::class, 'verify_admin_case_number'])->name('verify_admin_case_number');

    Route::post('manage-ticket-limit', [TicketServiceController::class, 'manage_ticket_limit'])->name('manage_ticket_limit');
    Route::get('find-user-details', [RegisterRecipientScreenController::class, 'find_user_details'])->name('find_user_details');

    Route::get('user-unpicked-tickets/{id}', [RegisterRecipientScreenController::class, 'user_unpicked_tickets'])->name('user_unpicked_tickets');
    Route::get('user-cancelled-tickets/{id}', [RegisterRecipientScreenController::class, 'user_cancelled_tickets'])->name('user_cancelled_tickets');


    Route::get('search-user/first-name/last-name/case-number/date', [RegisterRecipientScreenController::class, 'search_user'])->name('search_user');
    Route::get('get-user/{id}', [RegisterRecipientScreenController::class, 'get_user'])->name('get_user');
    //Route::get('check-ticket-issued', [RegisterRecipientScreenController::class, 'check_ticket_issued'])->name('check_ticket_issued');
    Route::post('update-user', [RegisterRecipientScreenController::class, 'update_user'])->name('update_user');
    Route::post('delete-user', [RegisterRecipientScreenController::class, 'delete_user'])->name('delete_user');


    Route::get('check-user-ticket', [TicketServiceController::class, 'check_user_ticket'])->name('check_user_ticket');



    Route::get('current-redis-ticket', [TicketServiceController::class, 'current_redis_ticket'])->name('current_redis_ticket');
    Route::post('reset-redis-ticket', [TicketServiceController::class, 'reset_redis_ticket'])->name('reset_redis_ticket');
});
Route::POST('register', [AdminController::class, 'register'])->name('register');
Route::POST('register-record-single-ticket', [RegisterRecipientScreenController::class, 'register_record_single_ticket'])->name('register_record_single_ticket');


Route::post('admin/token/update-serving-number', [AdminToolController::class, 'updateServingNumber']);



Route::group(['middleware' => ['auth']], function () {

    Route::get('view-ticket-size-pdf/{id}/{type}', [TicketServiceController::class, 'view_ticket_size_pdf'])->name('view_ticket_size_pdf');

    Route::post('cancel-one-ticket-details', [TicketServiceController::class, 'cancel_one_ticket_details'])->name('cancel_one_ticket_details');

    Route::get('select-multiple-ticket-details/{id}', [TicketServiceController::class, 'select_multiple_ticket_details'])->name('select_multiple_ticket_details');

    Route::get('multiple-users-ticket-details/{id}', [TicketServiceController::class, 'multiple_users_ticket_details'])->name('multiple_users_ticket_details');

    Route::get('view-multiple-users-ticket-size-pdf/{id}/{type}', [TicketServiceController::class, 'view_multiple_users_ticket_size_pdf'])->name('view_multiple_users_ticket_size_pdf');

    Route::post('cancel-multiple-tickets-details', [TicketServiceController::class, 'cancel_multiple_tickets_details'])->name('cancel_multiple_tickets_details');

    Route::get('check-case-number', [TicketServiceController::class, 'check_case_number'])->name('check_case_number');

    Route::get('verify-single-case-number', [TicketServiceController::class, 'verify_single_case_number'])->name('verify_single_case_number');
    Route::get('verify-volunteer-case-number', [TicketServiceController::class, 'verify_volunteer_case_number'])->name('verify_volunteer_case_number');

    Route::get('one-ticket-details/{id}', [TicketServiceController::class, 'one_ticket_details'])->name('one_ticket_details');

    Route::post('manage-tickets-generation', [TicketServiceController::class, 'manage_tickets_generation'])->name('manage_tickets_generation');

    // Route::middleware('throttle:manage-ticket-requests')->group(function () {

    // Route::post('create-one-ticket-details', [TicketServiceController::class, 'create_one_ticket_details'])->name('create_one_ticket_details');
    // Route::post('create-multiple-tickets-details', [TicketServiceController::class, 'create_multiple_tickets_details'])->name('create_multiple_tickets_details');
    // });
});

Route::group(['as' => 'user.', 'prefix' => 'user', 'namespace' => 'User', 'middleware' => ['auth', 'user']], function () {
    Route::GET('dashboard', [UserController::class, 'index'])->name('dashboard');
    Route::GET('print', [UserController::class, 'print'])->name('print');

    Route::get('get-ticket-options', [TicketServiceController::class, 'get_ticket_options'])->name('get.ticket_options');
});



//   Route::prefix('user')->group(function () {
//     Route::GET('dashboard', [UserController::class, 'index'])->name('user.dashboard');
//     Route::GET('print', [UserController::class, 'print'])->name('user.print');
// });


Route::post('/update-check-in-status/{id}', [AdminToolController::class, 'updateCheckInStatus'])->name('update.checkin.status');

//Route::get('/enableuser_login', [AdminToolController::class,'enableuser_login'])->name('update.user.status');
//Route::get('/disableuser_login', [AdminToolController::class,'disableuser_login'])->name('update.user.status');



// Route::get('login-activated-function', [LoginController::class, 'login_activated_function'])->name('login_activated_function');

Route::get('system-reset-function/{id}', [LoginController::class, 'system_reset_function'])->name('system_reset_function');

Route::post('store_data', [AdminToolController::class, 'storeData'])->name('store.data');
