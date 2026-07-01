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
        if (! Schema::hasColumn('activity_logs', 'staff_id')) {
            return;
        }

        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->dropForeign(['staff_id']);
        });

        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->foreignUuid('user_id')->nullable()->after('id');
        });

        foreach (DB::table('activity_logs')->whereNotNull('staff_id')->lazyById() as $log) {
            $userId = DB::table('staffs')
                ->where('id', $log->staff_id)
                ->value('user_id');

            if ($userId !== null) {
                DB::table('activity_logs')
                    ->where('id', $log->id)
                    ->update(['user_id' => $userId]);
            }
        }

        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->dropColumn('staff_id');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('activity_logs', 'user_id')) {
            return;
        }

        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->foreignId('staff_id')->nullable()->after('id');
        });

        foreach (DB::table('activity_logs')->whereNotNull('user_id')->lazyById() as $log) {
            $staffId = DB::table('staffs')
                ->where('user_id', $log->user_id)
                ->value('id');

            if ($staffId !== null) {
                DB::table('activity_logs')
                    ->where('id', $log->id)
                    ->update(['staff_id' => $staffId]);
            }
        }

        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->dropColumn('user_id');
            $table->foreign('staff_id')->references('id')->on('staffs')->nullOnDelete();
            $table->index('staff_id');
        });
    }
};
