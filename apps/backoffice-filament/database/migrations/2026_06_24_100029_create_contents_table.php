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
            $table->string('title', 200)->nullable();
            $table->string('slug', 200)->unique();
            $table->longText('body_content')->nullable();
            $table->dateTime('event_date')->nullable();
            $table->string('status', 20)->default('draft');
            $table->boolean('is_staged')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
