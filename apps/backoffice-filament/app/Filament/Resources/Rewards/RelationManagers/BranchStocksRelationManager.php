<?php

declare(strict_types=1);

namespace App\Filament\Resources\Rewards\RelationManagers;

use App\Enums\ActivityLogAction;
use App\Models\Branch;
use App\Models\BranchRewardStock;
use App\Services\ActivityLog\ActivityLogger;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BranchStocksRelationManager extends RelationManager
{
    protected static string $relationship = 'branchStocks';

    protected static ?string $title = 'Stok per Cabang';

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $rewardId = $this->getOwnerRecord()->getKey();

                return Branch::query()
                    ->select([
                        'branches.id',
                        'branches.name',
                        DB::raw('COALESCE(rbs.actual_stock, 0) as actual_stock'),
                        DB::raw('COALESCE(rbs.held_stock, 0) as held_stock'),
                        DB::raw('COALESCE(rbs.actual_stock, 0) - COALESCE(rbs.held_stock, 0) as available_stock'),
                        DB::raw('rbs.id as stock_id'),
                    ])
                    ->leftJoin('reward_branch_stocks as rbs', function ($join) use ($rewardId): void {
                        $join->on('branches.id', '=', 'rbs.branch_id')
                            ->where('rbs.reward_id', '=', $rewardId);
                    });
            })
            ->heading(null)
            ->columns([
                TextColumn::make('name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('actual_stock')
                    ->label('Stok aktual')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('held_stock')
                    ->label('Stok ditahan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('available_stock')
                    ->label('Tersedia')
                    ->numeric()
                    ->sortable(query: fn ($query, string $direction): Builder => $query->orderByRaw("(COALESCE(rbs.actual_stock, 0) - COALESCE(rbs.held_stock, 0)) {$direction}")
                    )
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state > 10 => 'success',
                        $state > 0 => 'warning',
                        default => 'danger',
                    }),
            ])
            ->defaultSort('available_stock', 'desc')
            ->recordActions([
                Action::make('edit_stock')
                    ->label('Edit Stok')
                    ->icon('heroicon-o-pencil-square')
                    ->modalWidth('xl')
                    ->modalHeading(fn (Branch $record): string => "Edit Stok — {$record->name}")
                    ->schema([
                        Section::make('Stok ditahan')
                            ->description('Stok yang sedang ditahan oleh transaksi aktif dan tidak dapat diubah dari sini.')
                            ->schema([
                                TextEntry::make('held_stock')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->suffix(' unit'),
                            ])
                            ->columns(1),

                        TextInput::make('actual_stock')
                            ->label('Stok aktual')
                            ->numeric()
                            ->integer()
                            ->minValue(fn (Branch $record): int => (int) $record->held_stock)
                            ->required()
                            ->suffix('unit')
                            ->columnSpanFull()
                            ->helperText('Harus lebih besar atau sama dengan stok ditahan.'),
                    ])
                    ->fillForm(fn (Branch $record): array => [
                        'actual_stock' => $record->actual_stock,
                    ])
                    ->action(function (Branch $record, array $data): void {
                        $ownerRecord = $this->getOwnerRecord();
                        $before = [
                            'branch_id' => $record->id,
                            'actual_stock' => (int) $record->actual_stock,
                        ];

                        BranchRewardStock::updateOrCreate(
                            [
                                'reward_id' => $ownerRecord->getKey(),
                                'branch_id' => $record->id,
                            ],
                            [
                                'actual_stock' => (int) $data['actual_stock'],
                            ]
                        );

                        app(ActivityLogger::class)->log(
                            action: ActivityLogAction::RewardStockUpdated,
                            description: "Memperbarui stok reward untuk cabang {$record->name}",
                            auditable: $ownerRecord,
                            ipAddress: (string) request()->ip(),
                            before: $before,
                            after: [
                                'branch_id' => $record->id,
                                'actual_stock' => (int) $data['actual_stock'],
                            ],
                            actor: Auth::user(),
                        );
                    })
                    ->successNotificationTitle('Stok berhasil diperbarui'),
            ])
            ->headerActions([])
            ->paginated([10, 25, 50]);
    }
}
