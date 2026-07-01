<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if ($this->indexExists('point_mutations', 'point_mutations_reference_transaction_type_unique')) {
            return;
        }

        Schema::table('point_mutations', function (Blueprint $table): void {
            $table->unique(['reference_id', 'transaction_type_id'], 'point_mutations_reference_transaction_type_unique');
        });
    }

    public function down(): void
    {
        if (! $this->indexExists('point_mutations', 'point_mutations_reference_transaction_type_unique')) {
            return;
        }

        Schema::table('point_mutations', function (Blueprint $table): void {
            $table->dropUnique('point_mutations_reference_transaction_type_unique');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->selectOne(
            'SELECT COUNT(*) AS aggregate FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName],
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }
};
