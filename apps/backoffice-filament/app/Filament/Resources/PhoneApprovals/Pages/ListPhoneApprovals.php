<?php

declare(strict_types=1);

namespace App\Filament\Resources\PhoneApprovals\Pages;

use App\Filament\Resources\PhoneApprovals\PhoneApprovalResource;
use Filament\Resources\Pages\ListRecords;

class ListPhoneApprovals extends ListRecords
{
    protected static string $resource = PhoneApprovalResource::class;
}
