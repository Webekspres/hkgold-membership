<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_injection_batches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('staff_id')->nullable()->constrained('staffs')->nullOnDelete();
            $table->foreignUuid('media_id')->unique()->constrained('media')->restrictOnDelete();
            $table->integer('total_rows')->default(0);
            $table->integer('successful_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->integer('total_points_injected')->default(0);
            $table->boolean('resolved')->default(false);
            $table->dateTime('uploaded_at')->useCurrent();
            $table->dateTime('import_started_at')->nullable();
            $table->dateTime('processing_started_at')->nullable();

            $table->index('staff_id');
            $table->index('media_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_injection_batches');
    }
};
