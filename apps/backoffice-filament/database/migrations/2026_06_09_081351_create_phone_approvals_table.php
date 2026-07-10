<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phone_approvals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->constrained('members')->cascadeOnDelete();
            $table->string('old_phone', 50);
            $table->string('new_phone', 50);
            $table->string('status')->default('PENDING');
            $table->foreignUuid('approved_by')->nullable()->constrained('staffs')->nullOnDelete();
            $table->timestamps();

            $table->index('member_id');
            $table->index('approved_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_approvals');
    }
};
