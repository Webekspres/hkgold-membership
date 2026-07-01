<?php

declare(strict_types=1);

namespace App\Filament\Resources\TierMembers\Pages;

use App\Filament\Resources\TierMembers\TierMemberResource;
use Filament\Resources\Pages\ListRecords;

class ListTierMembers extends ListRecords
{
    protected static string $resource = TierMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
