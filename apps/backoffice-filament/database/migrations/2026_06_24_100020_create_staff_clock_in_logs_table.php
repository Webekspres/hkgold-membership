<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_clock_in_logs', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('staff_id')->constrained('staffs')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches');
            $table->dateTime('clock_in_at')->useCurrent();
            $table->string('login_ip', 45);
            $table->text('user_agent');

            $table->index('staff_id');
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_clock_in_logs');
    }
};
