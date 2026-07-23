<?php

declare(strict_types=1);

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Support\Enums\Width;

class Login extends BaseLogin
{
    protected string $view = 'filament.auth.pages.login';

    protected Width|string|null $maxWidth = Width::ExtraLarge;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Masuk ke Akun Anda';
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return 'Silakan login untuk mengakses dashboard membership.';
    }
}
