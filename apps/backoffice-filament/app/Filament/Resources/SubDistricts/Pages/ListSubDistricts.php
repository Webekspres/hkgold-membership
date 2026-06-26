<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubDistricts\Pages;

use App\Filament\Resources\SubDistricts\SubDistrictResource;
use Filament\Resources\Pages\ListRecords;

class ListSubDistricts extends ListRecords
{
    protected static string $resource = SubDistrictResource::class;
}
