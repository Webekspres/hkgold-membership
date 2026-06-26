<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversion_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('transaction_type_id')->constrained('transaction_types')->cascadeOnDelete();
            $table->foreignId('tier_member_id')->constrained('tier_members')->cascadeOnDelete();
            $table->decimal('conversion_nominal', 15, 2);
            $table->timestamps();

            $table->unique(['transaction_type_id', 'tier_member_id']);
            $table->index('tier_member_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversion_rules');
    }
};
