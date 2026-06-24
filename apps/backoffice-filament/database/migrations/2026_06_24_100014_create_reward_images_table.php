<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_images', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('reward_id')->constrained('rewards')->cascadeOnDelete();
            $table->foreignUuid('media_id')->constrained('media')->restrictOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['reward_id', 'media_id']);
            $table->index('media_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_images');
    }
};
