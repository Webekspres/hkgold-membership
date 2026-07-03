<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_injection_batches', function (Blueprint $table): void {
            $table->dateTime('import_started_at')->nullable()->after('uploaded_at');
            $table->dateTime('processing_started_at')->nullable()->after('import_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('point_injection_batches', function (Blueprint $table): void {
            $table->dropColumn(['import_started_at', 'processing_started_at']);
        });
    }
};
