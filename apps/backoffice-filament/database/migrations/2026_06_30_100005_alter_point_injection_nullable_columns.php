<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_injection_batches', function (Blueprint $table): void {
            $table->dropForeign(['staff_id']);
            $table->unsignedBigInteger('staff_id')->nullable()->change();
            $table->foreign('staff_id')->references('id')->on('staffs')->nullOnDelete();
        });

        Schema::table('point_injection_details', function (Blueprint $table): void {
            $table->dropForeign(['transaction_type_id']);
            $table->unsignedBigInteger('transaction_type_id')->nullable()->change();
            $table->dateTime('transaction_date')->nullable()->change();
            $table->foreign('transaction_type_id')->references('id')->on('transaction_types')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('point_injection_details', function (Blueprint $table): void {
            $table->dropForeign(['transaction_type_id']);
            $table->unsignedBigInteger('transaction_type_id')->nullable(false)->change();
            $table->dateTime('transaction_date')->nullable(false)->change();
            $table->foreign('transaction_type_id')->references('id')->on('transaction_types');
        });

        Schema::table('point_injection_batches', function (Blueprint $table): void {
            $table->dropForeign(['staff_id']);
            $table->unsignedBigInteger('staff_id')->nullable(false)->change();
            $table->foreign('staff_id')->references('id')->on('staffs');
        });
    }
};
