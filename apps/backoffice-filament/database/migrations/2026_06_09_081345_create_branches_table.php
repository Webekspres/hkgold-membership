<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->string('phone', 50)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('open_time', 5)->default('08:00');
            $table->string('close_time', 5)->default('17:00');
            $table->timestamps();

            $table->index('address_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
