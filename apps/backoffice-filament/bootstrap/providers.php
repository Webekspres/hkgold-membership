<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AppPanelProvider;
use App\Providers\Filament\MemberPanelProvider;

return [
    AppServiceProvider::class,
    AppPanelProvider::class,
    MemberPanelProvider::class,
];
