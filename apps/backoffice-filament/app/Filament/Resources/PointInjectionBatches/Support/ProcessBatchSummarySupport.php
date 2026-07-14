<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointInjectionBatches\Support;

use App\Data\Loyalty\ProcessBatchSummary;

class ProcessBatchSummarySupport
{
    public static function buildHtml(ProcessBatchSummary $summary): string
    {
        $uniqueMembers = number_format($summary->uniqueMembers, 0, ',', '.');
        $totalRows = number_format($summary->totalRows, 0, ',', '.');
        $totalPoints = number_format($summary->totalPoints, 0, ',', '.');
        $nominal = number_format((float) $summary->totalNominal, 0, ',', '.');

        return <<<HTML
            <style>
                .process-batch-summary__grid {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 0.75rem;
                }

                .process-batch-summary__card {
                    border-radius: 0.75rem;
                    border: 1px solid rgb(229 231 235);
                    background-color: rgb(255 255 255);
                    padding: 1rem 1.25rem;
                    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                }

                .process-batch-summary__label {
                    margin: 0;
                    font-size: 0.875rem;
                    font-weight: 500;
                    color: rgb(107 114 128);
                }

                .process-batch-summary__value {
                    margin: 0.375rem 0 0;
                    font-size: 1.5rem;
                    font-weight: 700;
                    letter-spacing: -0.025em;
                    color: rgb(17 24 39);
                }
            </style>
            <div class="process-batch-summary__grid">
                <div class="process-batch-summary__card">
                    <p class="process-batch-summary__label">Total Member Unik</p>
                    <p class="process-batch-summary__value">{$uniqueMembers}</p>
                </div>
                <div class="process-batch-summary__card">
                    <p class="process-batch-summary__label">Total Baris Diproses</p>
                    <p class="process-batch-summary__value">{$totalRows}</p>
                </div>
                <div class="process-batch-summary__card">
                    <p class="process-batch-summary__label">Total Poin Diinjeksi</p>
                    <p class="process-batch-summary__value">{$totalPoints}</p>
                </div>
                <div class="process-batch-summary__card">
                    <p class="process-batch-summary__label">Total Nominal Transaksi</p>
                    <p class="process-batch-summary__value">Rp {$nominal}</p>
                </div>
            </div>
            HTML;
    }
}
