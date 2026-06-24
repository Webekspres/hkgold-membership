<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('village_id')->constrained('villages');
            $table->foreignUuid('postal_code_id')->constrained('postal_codes');
            $table->text('street');
            $table->timestamps();

            $table->index('village_id');
            $table->index('postal_code_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
