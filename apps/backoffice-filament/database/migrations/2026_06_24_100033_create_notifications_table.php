<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->uuid('notification_key')->nullable();
            $table->string('title', 150);
            $table->text('body');
            $table->string('platform');
            $table->string('status', 20)->default('PENDING');
            $table->json('data_payload')->nullable();
            $table->dateTime('read_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->string('error_message', 500)->nullable();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'platform', 'created_at'], 'notifications_user_platform_created_idx');
            $table->index(['status', 'created_at'], 'notifications_status_created_idx');
            $table->unique(['user_id', 'notification_key', 'platform'], 'notifications_user_key_platform_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
