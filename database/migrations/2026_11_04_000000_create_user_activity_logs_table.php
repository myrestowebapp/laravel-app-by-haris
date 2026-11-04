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
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Searchable Identity Fields (populated even for non-existent/guest users)
            $table->string('identifier', 191)->nullable()->index(); // Email or Mobile or Username attempted
            $table->string('user_name', 191)->nullable();
            $table->string('user_email', 191)->nullable()->index();
            $table->string('user_mobile', 50)->nullable()->index();
            $table->string('user_role', 50)->nullable()->default('guest')->index(); // admin, mufti, questioner, guest
            
            // Action taxonomy & classification
            $table->string('action', 80)->index(); // login_success, login_failed, logout, otp_requested, otp_verified, otp_failed, password_reset, question_submitted, profile_completed, etc.
            $table->string('action_category', 50)->default('general')->index(); // auth, question, profile, security, session
            $table->string('status', 30)->default('info')->index(); // success, failed, warning, info
            
            // Explanatory messages & Failure Diagnostics
            $table->text('description')->nullable(); // Human readable summary of event
            $table->string('failure_reason', 150)->nullable()->index(); // e.g. 'invalid_password', 'user_not_found', 'account_inactive', 'invalid_otp'
            
            // Telemetry & Device details
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('device', 50)->nullable()->default('Desktop'); // Desktop, Mobile Device, Tablet
            $table->string('platform', 50)->nullable(); // Windows, Android, iOS, macOS, Linux
            $table->string('browser', 50)->nullable(); // Chrome, Safari, Firefox, Edge
            $table->string('location', 150)->nullable(); // City, State, Country
            
            // Context metadata & timestamp
            $table->json('context_data')->nullable(); // Extra debug metadata (inputs, query params, etc.)
            $table->timestamps();

            // Composite indexes for fast search & filtering
            $table->index(['created_at', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
