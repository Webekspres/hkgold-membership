<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_anomalies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->constrained('members')->cascadeOnDelete();
            $table->dateTime('last_mutation_at');
            $table->integer('hoarded_points');
            $table->dateTime('detected_at')->useCurrent();

            $table->index('member_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_anomalies');
    }
};
