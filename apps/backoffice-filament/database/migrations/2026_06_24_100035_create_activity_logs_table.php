<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('staff_id')->nullable()->constrained('staffs')->nullOnDelete();
            $table->string('action', 100);
            $table->text('description');
            $table->string('auditable_type', 100);
            $table->string('auditable_id', 50);
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->string('ip_address', 45);
            $table->timestamp('created_at')->useCurrent();

            $table->index('staff_id');
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
