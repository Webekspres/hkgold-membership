<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemInvoices\Widgets;

use App\Models\RedeemInvoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RedeemBranchChartWidget extends ChartWidget
{
    protected ?string $heading = 'Jumlah Redeem per Cabang';

    protected ?string $description = '30 hari terakhir';

    protected ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'lg' => 3,
    ];

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = RedeemInvoice::query()
            ->select([
                'branches.name as branch_name',
                DB::raw('COUNT(redeem_invoices.id) as total_redeems'),
            ])
            ->join('branches', 'branches.id', '=', 'redeem_invoices.branch_id')
            ->where('redeem_invoices.created_at', '>=', now()->subDays(30))
            ->groupBy('branches.name')
            ->orderByDesc('total_redeems')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Redeem',
                    'data' => $rows->pluck('total_redeems')->map(fn (mixed $value): int => (int) $value)->all(),
                    'backgroundColor' => '#e8a020',
                    'borderRadius' => 8,
                ],
            ],
            'labels' => $rows->pluck('branch_name')->all(),
        ];
    }
}
