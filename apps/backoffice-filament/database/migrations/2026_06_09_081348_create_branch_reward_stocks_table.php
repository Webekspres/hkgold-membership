<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_reward_stocks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignUuid('reward_id')->constrained('rewards')->cascadeOnDelete();
            $table->integer('stock_quantity')->default(0);
            $table->timestamps();

            $table->unique(['branch_id', 'reward_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_reward_stocks');
    }
};
