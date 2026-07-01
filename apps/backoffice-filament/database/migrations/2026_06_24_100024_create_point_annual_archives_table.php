<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_annual_archives', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->integer('archive_year');
            $table->integer('frozen_points_total');
            $table->integer('highest_point');
            $table->string('last_tier_position');
            $table->dateTime('frozen_at')->useCurrent();

            $table->index('member_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_annual_archives');
    }
};
