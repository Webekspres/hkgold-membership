<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rewards', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('category_id')->constrained('category_rewards');
            $table->string('name', 150);
            $table->string('sku', 50)->unique();
            $table->text('description');
            $table->integer('points_required');
            $table->boolean('is_active')->default(true);
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->timestamps();

            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
