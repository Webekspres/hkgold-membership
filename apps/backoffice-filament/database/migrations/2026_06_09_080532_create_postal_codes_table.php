<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postal_codes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->foreignId('sub_district_id')->constrained('sub_districts')->cascadeOnDelete();
            $table->string('kodepos', 20);

            $table->index(['city_id', 'sub_district_id']);
            $table->index('kodepos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postal_codes');
    }
};
