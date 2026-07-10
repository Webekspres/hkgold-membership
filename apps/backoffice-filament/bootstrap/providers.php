<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AppPanelProvider;
use App\Providers\Filament\MemberPanelProvider;
use App\Providers\HorizonServiceProvider;

return [
    AppServiceProvider::class,
    AppPanelProvider::class,
    MemberPanelProvider::class,
    HorizonServiceProvider::class,
];
