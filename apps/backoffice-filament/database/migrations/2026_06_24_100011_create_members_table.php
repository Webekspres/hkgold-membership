<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('registered_at_branch_id')->constrained('branches');
            $table->foreignUuid('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->string('member_number', 15)->unique();
            $table->string('phone_number', 20)->unique();
            $table->string('current_tier')->default('SILVER');
            $table->integer('point_balance')->default(0);
            $table->dateTime('last_activity_at')->useCurrent();
            $table->boolean('is_suspended')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('registered_at_branch_id');
            $table->index('address_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
