<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\RedeemInvoice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RedeemInvoiceSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        RedeemInvoice::factory()->count(12)->create();
    }
}
