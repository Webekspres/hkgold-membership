<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->string('member_code', 50)->unique();
            $table->date('dob')->nullable();
            $table->decimal('total_points', 15, 2)->default(0);
            $table->string('tier')->default('SILVER');
            $table->boolean('phone_change_pending')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id')->references('id')->on('users')->cascadeOnDelete();
            $table->index('address_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
