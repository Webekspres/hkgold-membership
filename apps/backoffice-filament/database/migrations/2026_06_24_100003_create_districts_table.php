<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_districts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->string('nama', 150);
            $table->decimal('latitude', 15, 11)->nullable();
            $table->decimal('longitude', 15, 11)->nullable();

            $table->index('city_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_districts');
    }
};
