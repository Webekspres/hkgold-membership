<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('redeem_invoices', function (Blueprint $table): void {
            $table->char('redeem_token_id', 36)->nullable()->after('reward_id');
            $table->unique('redeem_token_id');
            $table->foreign('redeem_token_id')
                ->references('id')
                ->on('redeem_tokens')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('redeem_invoices', function (Blueprint $table): void {
            $table->dropForeign(['redeem_token_id']);
            $table->dropUnique(['redeem_token_id']);
            $table->dropColumn('redeem_token_id');
        });
    }
};
