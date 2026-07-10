<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('title', 255);
            $table->longText('body');
            $table->string('location', 255)->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_published')->default(true);
            $table->foreignUuid('media_id')->unique()->constrained('media')->cascadeOnDelete();
            $table->timestamps();

            $table->index('media_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
