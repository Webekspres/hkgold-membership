<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tier_benefits', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('tier_member_id')->constrained('tier_members')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('description', 255);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tier_member_id', 'sort_order'], 'tier_benefits_tier_member_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tier_benefits');
    }
};
