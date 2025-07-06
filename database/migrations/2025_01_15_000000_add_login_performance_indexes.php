<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes for users table login queries
        Schema::table('users', function (Blueprint $table) {
            // Composite index for login queries (last_name, case_number)
            $table->index(['last_name', 'case_number'], 'users_login_index');
            
            // Index for active users
            $table->index(['is_active', 'status'], 'users_active_status_index');
            
            // Index for case number uniqueness checks
            $table->index('case_number', 'users_case_number_index');
        });

        // Add indexes for generated_tickets table
        Schema::table('generated_tickets', function (Blueprint $table) {
            // Composite index for today's tickets count
            $table->index(['status', 'is_active', 'is_cancelled', 'created_at'], 'tickets_today_count_index');
            
            // Index for user's existing tickets
            $table->index(['user_id', 'is_active', 'status', 'is_cancelled', 'created_at'], 'tickets_user_existing_index');
            
            // Index for ticket analytics
            $table->index(['status', 'is_active', 'is_cancelled', 'is_reset', 'checked_in', 'created_at'], 'tickets_analytics_index');
        });

        // Add indexes for login_days_time table
        Schema::table('login_days_time', function (Blueprint $table) {
            // Index for active login days
            $table->index(['is_active', 'day'], 'login_days_active_index');
            
            // Index for specific date login times
            $table->index(['status', 'is_active', 'date'], 'login_days_date_index');
            
            // Index for general login days
            $table->index(['status', 'is_active'], 'login_days_general_index');
        });

        // Add indexes for tickets_management table
        Schema::table('tickets_management', function (Blueprint $table) {
            // Index for ticket limit checks
            $table->index(['ticket_limit_status', 'ticket_limit'], 'tickets_management_limit_index');
        });

        // Add indexes for ticket_return_times table
        Schema::table('ticket_return_times', function (Blueprint $table) {
            // Index for active return times
            $table->index(['is_active', 'status'], 'return_times_active_index');
        });

        // Add indexes for memos table
        Schema::table('memos', function (Blueprint $table) {
            // Index for enabled messages
            $table->index(['status', 'is_enabled', 'is_type'], 'memos_enabled_index');
        });

        // Add indexes for notification_messages table
        Schema::table('notification_messages', function (Blueprint $table) {
            // Index for active notifications
            $table->index(['status'], 'notification_messages_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_login_index');
            $table->dropIndex('users_active_status_index');
            $table->dropIndex('users_case_number_index');
        });

        // Remove indexes from generated_tickets table
        Schema::table('generated_tickets', function (Blueprint $table) {
            $table->dropIndex('tickets_today_count_index');
            $table->dropIndex('tickets_user_existing_index');
            $table->dropIndex('tickets_analytics_index');
        });

        // Remove indexes from login_days_time table
        Schema::table('login_days_time', function (Blueprint $table) {
            $table->dropIndex('login_days_active_index');
            $table->dropIndex('login_days_date_index');
            $table->dropIndex('login_days_general_index');
        });

        // Remove indexes from tickets_management table
        Schema::table('tickets_management', function (Blueprint $table) {
            $table->dropIndex('tickets_management_limit_index');
        });

        // Remove indexes from ticket_return_times table
        Schema::table('ticket_return_times', function (Blueprint $table) {
            $table->dropIndex('return_times_active_index');
        });

        // Remove indexes from memos table
        Schema::table('memos', function (Blueprint $table) {
            $table->dropIndex('memos_enabled_index');
        });

        // Remove indexes from notification_messages table
        Schema::table('notification_messages', function (Blueprint $table) {
            $table->dropIndex('notification_messages_status_index');
        });
    }
}; 