<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('reward_id')->nullable()->constrained('rewards')->cascadeOnDelete();
            $table->text('caption')->nullable();
            $table->string('file_name', 255);
            $table->string('file_type', 255);
            $table->text('file_url');
            $table->integer('file_size');
            $table->timestamps();

            $table->index('reward_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
