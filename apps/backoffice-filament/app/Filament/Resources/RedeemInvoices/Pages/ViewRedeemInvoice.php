<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemInvoices\Pages;

use App\Filament\Resources\RedeemInvoices\RedeemInvoiceResource;
use App\Models\RedeemInvoice;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewRedeemInvoice extends ViewRecord
{
    protected static string $resource = RedeemInvoiceResource::class;

    public function getView(): string
    {
        return 'filament.resources.redeem-invoices.view-redeem-invoice';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali ke List')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(RedeemInvoiceResource::getUrl('index')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        /** @var RedeemInvoice $record */
        $record = $this->record;

        $rewardImages = $record->reward?->rewardImages
            ->map(fn ($image): ?string => $image->media?->file_url)
            ->filter()
            ->values()
            ->all() ?? [];

        return [
            'member_photo' => $record->member?->user?->profilePhoto?->file_url,
            'member_name' => $record->member?->user?->full_name ?? '-',
            'member_number' => $record->member?->member_number ?? '-',
            'member_tier' => $record->member?->current_tier?->value ?? '-',
            'member_phone' => $record->member?->phone_number ?? '-',
            'member_email' => $record->member?->user?->email ?? '-',
            'branch_name' => $record->branch?->name ?? '-',
            'branch_address' => $record->branch?->address ?? '-',
            'branch_location_url' => $record->branch?->location_url,
            'staff_name' => $record->staff?->user?->full_name ?? '-',
            'reward_name' => $record->reward?->name ?? '-',
            'reward_category' => $record->reward?->categoryReward?->name ?? '-',
            'reward_sku' => $record->reward?->sku ?? '-',
            'reward_description' => $record->reward?->description ?? '-',
            'reward_points_required' => number_format((int) ($record->reward?->points_required ?? 0), 0, ',', '.'),
            'invoice_points_redeemed' => number_format((int) $record->points_redeemed, 0, ',', '.'),
            'reward_images' => $rewardImages,
            'invoice_number' => $record->invoice_number,
            'redeemed_at' => $record->created_at?->translatedFormat('d M Y, H:i'),
        ];
    }
}
