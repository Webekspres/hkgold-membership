<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('nation_code');
            $table->string('iso2', 2);
            $table->string('iso3', 3);
            $table->string('nama', 100);
            $table->string('mata_uang', 50);
            $table->string('kode_mata_uang', 10);
            $table->string('simbol_mata_uang', 10);
            $table->string('satuan_berat', 10);
            $table->string('satuan_panjang', 10);
            $table->decimal('latitude', 15, 11)->nullable();
            $table->decimal('longitude', 15, 11)->nullable();
            $table->boolean('is_provinsi')->default(false);
            $table->boolean('is_kabkota')->default(false);
            $table->boolean('is_kecamatan')->default(false);
            $table->boolean('is_kelurahan')->default(false);
            $table->boolean('is_kodepos')->default(false);
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nations');
    }
};
