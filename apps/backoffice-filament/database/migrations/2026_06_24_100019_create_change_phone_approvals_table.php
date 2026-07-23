<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('change_phone_approvals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('requested_by_id')->constrained('staffs');
            $table->foreignId('approved_by_id')->nullable()->constrained('staffs');
            $table->string('old_phone_number', 20);
            $table->string('new_phone_number', 20)->unique();
            $table->string('status')->default('PENDING');
            $table->text('reason')->nullable();
            $table->text('action_notes')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();

            $table->index('member_id');
            $table->index('requested_by_id');
            $table->index('approved_by_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_phone_approvals');
    }
};
