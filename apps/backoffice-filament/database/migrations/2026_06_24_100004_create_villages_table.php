<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sub_district_id')->constrained('sub_districts')->cascadeOnDelete();
            $table->string('nama', 150);
            $table->decimal('latitude', 15, 11)->nullable();
            $table->decimal('longitude', 15, 11)->nullable();

            $table->index('sub_district_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};
