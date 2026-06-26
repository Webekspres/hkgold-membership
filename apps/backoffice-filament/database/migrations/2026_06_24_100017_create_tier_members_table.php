<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tier_members', function (Blueprint $table): void {
            $table->id();
            $table->string('tier_code')->unique();
            $table->integer('min_points');
            $table->integer('max_points');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tier_members');
    }
};
