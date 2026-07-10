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
            $table->foreignUuid('member_1_id')->constrained('members')->cascadeOnDelete();
            $table->foreignUuid('member_2_id')->constrained('members')->cascadeOnDelete();
            $table->decimal('confidence_score', 5, 2);
            $table->text('reason');
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();

            $table->index('member_1_id');
            $table->index('member_2_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_suspects');
    }
};
