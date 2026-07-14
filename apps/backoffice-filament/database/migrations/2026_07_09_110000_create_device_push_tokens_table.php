<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_push_tokens', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_uuid', 255)->nullable();
            $table->string('platform', 20);
            $table->string('token', 255);
            $table->dateTime('last_used_at')->nullable();
            $table->dateTime('revoked_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'token'], 'device_push_tokens_user_token_unique');
            $table->index(['user_id', 'revoked_at'], 'device_push_tokens_user_revoked_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_push_tokens');
    }
};
