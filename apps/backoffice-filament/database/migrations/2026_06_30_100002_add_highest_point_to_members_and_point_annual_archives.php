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
        if (! Schema::hasColumn('members', 'highest_point')) {
            Schema::table('members', function (Blueprint $table): void {
                $table->integer('highest_point')->default(0)->after('point_balance');
            });

            DB::table('members')->update([
                'highest_point' => DB::raw('point_balance'),
            ]);
        }

        if (! Schema::hasColumn('point_annual_archives', 'highest_point')) {
            Schema::table('point_annual_archives', function (Blueprint $table): void {
                $table->integer('highest_point')->nullable()->after('frozen_points_total');
            });

            DB::table('point_annual_archives')->update([
                'highest_point' => DB::raw('frozen_points_total'),
            ]);

            Schema::table('point_annual_archives', function (Blueprint $table): void {
                $table->integer('highest_point')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('point_annual_archives', 'highest_point')) {
            Schema::table('point_annual_archives', function (Blueprint $table): void {
                $table->dropColumn('highest_point');
            });
        }

        if (Schema::hasColumn('members', 'highest_point')) {
            Schema::table('members', function (Blueprint $table): void {
                $table->dropColumn('highest_point');
            });
        }
    }
};
