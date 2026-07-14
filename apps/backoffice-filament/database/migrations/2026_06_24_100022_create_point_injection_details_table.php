<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_injection_details', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignUuid('batch_id')->constrained('point_injection_batches')->cascadeOnDelete();
            $table->integer('row_number');
            $table->string('raw_member_number', 50);
            $table->string('raw_branch_code', 20);
            $table->decimal('purchase_nominal', 15, 2);
            $table->foreignId('transaction_type_id')->nullable()->constrained('transaction_types')->restrictOnDelete();
            $table->dateTime('transaction_date')->nullable();
            $table->integer('calculated_points')->default(0);
            $table->string('status')->default('PENDING');
            $table->text('error_message')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->string('receipt_number', 100)->nullable();

            $table->index('batch_id');
            $table->index('transaction_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_injection_details');
    }
};
