<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redeem_tokens', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignUuid('reward_id')->constrained('rewards')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('token_code', 10)->unique();
            $table->integer('held_points');
            $table->boolean('is_used')->default(false);
            $table->dateTime('expired_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index('member_id');
            $table->index('reward_id');
            $table->index('branch_id');
            $table->index(['token_code', 'is_used', 'expired_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redeem_tokens');
    }
};
