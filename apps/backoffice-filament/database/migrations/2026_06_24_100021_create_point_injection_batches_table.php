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
            $table->foreignId('staff_id')->constrained('staffs');
            $table->string('file_name', 255);
            $table->integer('total_rows')->default(0);
            $table->integer('successful_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->integer('total_points_injected')->default(0);
            $table->dateTime('uploaded_at')->useCurrent();

            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_injection_batches');
    }
};
