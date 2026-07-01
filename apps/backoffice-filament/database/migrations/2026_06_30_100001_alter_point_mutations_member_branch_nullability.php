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
        $orphanMemberIds = DB::table('point_mutations')
            ->whereNull('member_id')
            ->pluck('id');

        if ($orphanMemberIds->isNotEmpty()) {
            $fallbackMemberId = DB::table('members')->value('id');

            if ($fallbackMemberId === null) {
                throw new RuntimeException('Cannot make member_id required: point_mutations has rows without member_id and no members exist.');
            }

            DB::table('point_mutations')
                ->whereIn('id', $orphanMemberIds)
                ->update(['member_id' => $fallbackMemberId]);
        }

        Schema::table('point_mutations', function (Blueprint $table): void {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['member_id']);
        });

        Schema::table('point_mutations', function (Blueprint $table): void {
            $table->foreignUuid('member_id')->nullable(false)->change();
            $table->foreignId('branch_id')->nullable()->change();
        });

        Schema::table('point_mutations', function (Blueprint $table): void {
            $table->foreign('member_id')
                ->references('id')
                ->on('members')
                ->restrictOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('point_mutations', function (Blueprint $table): void {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['member_id']);
        });

        DB::table('point_mutations')
            ->whereNull('branch_id')
            ->update(['branch_id' => DB::table('branches')->value('id')]);

        Schema::table('point_mutations', function (Blueprint $table): void {
            $table->foreignUuid('member_id')->nullable()->change();
            $table->foreignId('branch_id')->nullable(false)->change();
        });

        Schema::table('point_mutations', function (Blueprint $table): void {
            $table->foreign('member_id')
                ->references('id')
                ->on('members')
                ->nullOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->restrictOnDelete();
        });
    }
};
