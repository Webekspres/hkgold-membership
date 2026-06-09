<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('model_has_roles') || ! Schema::hasColumn('model_has_roles', 'model_uuid')) {
            return;
        }

        DB::statement('ALTER TABLE model_has_roles MODIFY model_uuid CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE model_has_permissions MODIFY model_uuid CHAR(36) NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('model_has_roles') || ! Schema::hasColumn('model_has_roles', 'model_uuid')) {
            return;
        }

        DB::statement('ALTER TABLE model_has_roles MODIFY model_uuid BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE model_has_permissions MODIFY model_uuid BIGINT UNSIGNED NOT NULL');
    }
};
