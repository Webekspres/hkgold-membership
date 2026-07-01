<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_mutations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->constrained('members')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('reference_id', 100)->nullable();
            $table->foreignId('transaction_type_id')->nullable()->constrained('transaction_types')->restrictOnDelete();
            $table->decimal('purchase_nominal', 15, 2)->default(0);
            $table->integer('points_issued')->default(0);
            $table->integer('points_redeemed')->default(0);
            $table->integer('balance_snapshot');
            $table->dateTime('transaction_date');
            $table->dateTime('uploaded_at')->useCurrent();

            $table->index('member_id');
            $table->index('branch_id');
            $table->index('transaction_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_mutations');
    }
};
