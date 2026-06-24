<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('identifier', 100);
            $table->string('otp_code', 10);
            $table->string('type');
            $table->boolean('is_used')->default(false);
            $table->dateTime('expired_at');
            $table->timestamps();

            $table->index(['identifier', 'otp_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
