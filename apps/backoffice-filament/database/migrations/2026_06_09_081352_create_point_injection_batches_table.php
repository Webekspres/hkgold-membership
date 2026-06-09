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
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('uploaded_by_id')->constrained('staffs');
            $table->string('filename', 255);
            $table->text('file_url');
            $table->string('status')->default('PENDING');
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->longText('error_log')->nullable();
            $table->timestamps();

            $table->index('branch_id');
            $table->index('uploaded_by_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_injection_batches');
    }
};
