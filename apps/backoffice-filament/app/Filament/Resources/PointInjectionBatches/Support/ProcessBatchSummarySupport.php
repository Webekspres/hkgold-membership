<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointInjectionBatches\Support;

use App\Data\Loyalty\ProcessBatchSummary;

class ProcessBatchSummarySupport
{
    public static function buildHtml(ProcessBatchSummary $summary): string
    {
        $nominal = number_format((float) $summary->totalNominal, 0, ',', '.');

        return <<<HTML
            <div class="space-y-3 text-sm">
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Total member unik</span>
                    <span class="font-semibold text-gray-900">{$summary->uniqueMembers}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Total baris diproses</span>
                    <span class="font-semibold text-gray-900">{$summary->totalRows}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Total poin diinjeksi</span>
                    <span class="font-semibold text-gray-900">{$summary->totalPoints}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Total nominal transaksi</span>
                    <span class="font-semibold text-gray-900">Rp {$nominal}</span>
                </div>
            </div>
            HTML;
    }
}
