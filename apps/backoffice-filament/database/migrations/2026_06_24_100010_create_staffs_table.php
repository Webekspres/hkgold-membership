<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staffs', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('employee_code', 20)->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staffs');
    }
};
