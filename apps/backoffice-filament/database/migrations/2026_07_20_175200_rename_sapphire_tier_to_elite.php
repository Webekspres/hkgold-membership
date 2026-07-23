<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('members')->where('current_tier', 'SAPPHIRE')->update(['current_tier' => 'ELITE']);
        DB::table('tier_members')->where('tier_code', 'SAPPHIRE')->update(['tier_code' => 'ELITE']);
        DB::table('point_annual_archives')->where('last_tier_position', 'SAPPHIRE')->update(['last_tier_position' => 'ELITE']);

        if (Schema::hasTable('loyalty_configs')) {
            DB::table('loyalty_configs')->where('tier', 'SAPPHIRE')->update(['tier' => 'ELITE']);
        }

        if (Schema::hasTable('notification_campaigns')) {
            DB::table('notification_campaigns')
                ->where('criteria_json', 'like', '%SAPPHIRE%')
                ->update([
                    'criteria_json' => DB::raw("REPLACE(criteria_json, 'SAPPHIRE', 'ELITE')"),
                ]);
        }
    }

    public function down(): void
    {
        DB::table('members')->where('current_tier', 'ELITE')->update(['current_tier' => 'SAPPHIRE']);
        DB::table('tier_members')->where('tier_code', 'ELITE')->update(['tier_code' => 'SAPPHIRE']);
        DB::table('point_annual_archives')->where('last_tier_position', 'ELITE')->update(['last_tier_position' => 'SAPPHIRE']);

        if (Schema::hasTable('loyalty_configs')) {
            DB::table('loyalty_configs')->where('tier', 'ELITE')->update(['tier' => 'SAPPHIRE']);
        }

        if (Schema::hasTable('notification_campaigns')) {
            DB::table('notification_campaigns')
                ->where('criteria_json', 'like', '%ELITE%')
                ->update([
                    'criteria_json' => DB::raw("REPLACE(criteria_json, 'ELITE', 'SAPPHIRE')"),
                ]);
        }
    }
};
