<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class LocationSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (DB::table('nations')->count() > 0) {
            $this->command?->info('Location data already seeded, skipping.');

            return;
        }

        $path = database_path('alamat.sql');

        if (! File::exists($path)) {
            throw new RuntimeException("Missing location SQL dump at [{$path}].");
        }

        $this->command?->info('Importing Indonesian location data from alamat.sql...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $sql = File::get($path);
        $statements = preg_split('/;\s*\R/', $sql);

        foreach ($statements as $statement) {
            $statement = trim($statement);

            if ($statement === '') {
                continue;
            }

            DB::unprepared($statement.';');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command?->info('Location data imported successfully.');
    }
}
