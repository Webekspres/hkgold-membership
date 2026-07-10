<?php

declare(strict_types=1);

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Seeder;

class ShieldRolesSeeder extends Seeder
{
    public function run(): void
    {
        Utils::createRole(name: Utils::getSuperAdminName());
        Utils::createPanelUserRole();
    }
}
