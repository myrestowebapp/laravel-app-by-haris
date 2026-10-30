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
        Schema::create('fatwa_integrity_backups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('operation_uuid', 36)->index();
            $table->unsignedBigInteger('record_id')->index();
            $table->string('question_id', 50)->index();
            $table->unsignedInteger('snapshot_version')->default(1);
            $table->string('original_title', 500)->nullable();
            $table->longText('original_body');
            $table->longText('original_answer')->nullable();
            $table->string('original_status', 50);
            $table->boolean('original_is_public')->default(true);
            $table->unsignedInteger('original_madhab_id')->nullable();
            $table->unsignedBigInteger('original_category_id')->nullable();
            $table->unsignedBigInteger('original_user_id')->nullable();
            $table->unsignedBigInteger('original_assigned_mufti_id')->nullable();
            $table->timestamp('original_published_at')->nullable();
            $table->timestamp('original_created_at')->nullable();
            $table->timestamp('original_updated_at')->nullable();
            $table->json('full_row_snapshot');
            $table->json('references_snapshot')->nullable();
            $table->char('content_sha256', 64);
            $table->unsignedBigInteger('created_by_admin_id');
            $table->string('snapshot_reason', 255);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('created_by_admin_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('record_id')->references('id')->on('question_and_answer')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fatwa_integrity_backups');
    }
};

