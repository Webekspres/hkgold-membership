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
            $table->id();
            $table->string('branch_code', 10)->unique();
            $table->string('name', 100);
            $table->text('address')->nullable();
            $table->foreignUuid('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->string('phone', 20)->nullable();
            $table->string('location_url', 500)->nullable();
            $table->boolean('is_online_warehouse')->default(false);
            $table->timestamps();

            $table->index('address_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
