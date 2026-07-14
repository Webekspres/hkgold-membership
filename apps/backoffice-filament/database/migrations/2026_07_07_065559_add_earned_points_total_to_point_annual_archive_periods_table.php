<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_annual_archive_periods', function (Blueprint $table): void {
            $table->integer('earned_points_total')
                ->default(0)
                ->after('frozen_points_total');
        });

        DB::table('point_annual_archive_periods')->update([
            'earned_points_total' => DB::raw('frozen_points_total + redeemed_points_total'),
        ]);
    }

    public function down(): void
    {
        Schema::table('point_annual_archive_periods', function (Blueprint $table): void {
            $table->dropColumn('earned_points_total');
        });
    }
};
