<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_branch_stocks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('reward_id')->constrained('rewards')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->integer('actual_stock')->default(0);
            $table->integer('held_stock')->default(0);
            $table->timestamps();

            $table->unique(['reward_id', 'branch_id']);
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_branch_stocks');
    }
};
