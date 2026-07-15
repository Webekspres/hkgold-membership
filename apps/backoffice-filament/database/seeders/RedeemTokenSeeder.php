<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\RedeemToken;
use Illuminate\Database\Seeder;

class RedeemTokenSeeder extends Seeder
{
    public function run(): void
    {
        RedeemToken::factory()->count(4)->create();
        RedeemToken::factory()->count(4)->used()->create();
        RedeemToken::factory()->count(4)->expired()->create();
    }
}
