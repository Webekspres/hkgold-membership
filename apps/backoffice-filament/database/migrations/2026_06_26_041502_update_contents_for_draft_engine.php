<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table): void {
            $table->boolean('is_staged')->default(true)->after('status');
            $table->string('title', 200)->nullable()->change();
            $table->longText('body_content')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table): void {
            $table->dropColumn('is_staged');
            $table->string('title', 200)->nullable(false)->change();
            $table->longText('body_content')->nullable(false)->change();
        });
    }
};
