<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redeem_invoices', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('invoice_number', 50)->unique();
            $table->foreignUuid('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('staff_id')->constrained('staffs');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignUuid('reward_id')->constrained('rewards');
            $table->integer('points_redeemed');
            $table->string('status')->default('COMPLETED');
            $table->timestamps();

            $table->index('member_id');
            $table->index('staff_id');
            $table->index('branch_id');
            $table->index('reward_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redeem_invoices');
    }
};
