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
        Schema::table('members', function (Blueprint $table): void {
            $table->dateTime('phone_changed_at')->nullable()->after('gender');
        });

        Schema::table('change_phone_approvals', function (Blueprint $table): void {
            $table->string('source', 32)->default('ADMIN_ASSISTED')->after('new_phone_number');
        });

        // Drop unique index on new_phone_number (name from create migration).
        Schema::table('change_phone_approvals', function (Blueprint $table): void {
            $table->dropUnique(['new_phone_number']);
        });

        Schema::table('change_phone_approvals', function (Blueprint $table): void {
            $table->index('new_phone_number');
            $table->index('status');
        });

        // Make requested_by_id nullable (member-initiated requests).
        Schema::table('change_phone_approvals', function (Blueprint $table): void {
            $table->dropForeign(['requested_by_id']);
        });

        DB::statement('ALTER TABLE change_phone_approvals MODIFY requested_by_id BIGINT UNSIGNED NULL');

        Schema::table('change_phone_approvals', function (Blueprint $table): void {
            $table->foreign('requested_by_id')->references('id')->on('staffs');
        });
    }

    public function down(): void
    {
        Schema::table('change_phone_approvals', function (Blueprint $table): void {
            $table->dropForeign(['requested_by_id']);
        });

        DB::table('change_phone_approvals')->whereNull('requested_by_id')->delete();

        DB::statement('ALTER TABLE change_phone_approvals MODIFY requested_by_id BIGINT UNSIGNED NOT NULL');

        Schema::table('change_phone_approvals', function (Blueprint $table): void {
            $table->foreign('requested_by_id')->references('id')->on('staffs');
            $table->dropIndex(['status']);
            $table->dropIndex(['new_phone_number']);
            $table->dropColumn('source');
            $table->unique('new_phone_number');
        });

        Schema::table('members', function (Blueprint $table): void {
            $table->dropColumn('phone_changed_at');
        });
    }
};
