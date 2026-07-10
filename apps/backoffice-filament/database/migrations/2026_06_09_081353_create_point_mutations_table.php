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
            $table->foreignUuid('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignUuid('batch_id')->nullable()->constrained('point_injection_batches')->nullOnDelete();
            $table->string('type');
            $table->decimal('points', 15, 2);
            $table->dateTime('transaction_date');
            $table->text('description')->nullable();
            $table->decimal('transaction_amount', 15, 2);
            $table->string('invoice_reference', 100)->nullable();
            $table->dateTime('upload_date')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('member_id');
            $table->index('branch_id');
            $table->index('batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_mutations');
    }
};
