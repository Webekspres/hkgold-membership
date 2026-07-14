<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_campaigns', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title', 150);
            $table->text('body');
            $table->json('platforms');
            $table->json('criteria_json')->nullable();
            $table->unsignedInteger('targeted_count')->default(0);
            $table->unsignedInteger('accepted_count')->nullable();
            $table->unsignedInteger('failed_count')->nullable();
            $table->string('status', 20)->default('PENDING');
            $table->string('error_message', 500)->nullable();
            $table->foreignUuid('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'notification_campaigns_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_campaigns');
    }
};
