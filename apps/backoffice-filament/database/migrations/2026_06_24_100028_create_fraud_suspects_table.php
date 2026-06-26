<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fraud_suspects', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('detected_name', 150);
            $table->dateTime('detected_birth_date');
            $table->json('suspect_member_ids');
            $table->string('status')->default('PENDING_REVIEW');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_suspects');
    }
};
