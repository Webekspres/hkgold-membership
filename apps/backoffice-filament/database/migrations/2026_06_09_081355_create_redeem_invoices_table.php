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
            $table->string('invoice_number', 100)->unique();
            $table->foreignUuid('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('reward_id')->constrained('rewards');
            $table->decimal('points_deducted', 15, 2);
            $table->string('status')->default('PENDING');
            $table->string('qr_token', 255)->unique();
            $table->dateTime('expires_at');
            $table->dateTime('qr_expires_at');
            $table->foreignUuid('confirmed_by_id')->nullable()->constrained('staffs')->nullOnDelete();
            $table->foreignUuid('cancelled_by_id')->nullable()->constrained('staffs')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('member_id');
            $table->index('branch_id');
            $table->index('qr_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redeem_invoices');
    }
};
