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
        Schema::create('fatwa_integrity_audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('operation_uuid', 36)->index();
            $table->unsignedBigInteger('record_id')->index();
            $table->string('question_id', 50)->index();
            $table->unsignedBigInteger('backup_id')->nullable()->index();
            $table->unsignedBigInteger('admin_id')->index();
            $table->string('action_type', 50);
            $table->json('changed_fields');
            $table->json('pre_restoration_snapshot');
            $table->json('post_restoration_snapshot');
            $table->json('source_record_info')->nullable();
            $table->string('forked_question_id', 50)->nullable()->index();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('admin_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('record_id')->references('id')->on('question_and_answer')->onDelete('cascade');
            $table->foreign('backup_id')->references('id')->on('fatwa_integrity_backups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fatwa_integrity_audit_logs');
    }
};

