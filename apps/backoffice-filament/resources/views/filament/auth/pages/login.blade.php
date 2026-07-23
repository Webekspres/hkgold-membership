<div class="fi-hkgold-login">
    <div class="fi-hkgold-login__backdrop" aria-hidden="true">
        <div class="fi-hkgold-login__gradient"></div>
        <div class="fi-hkgold-login__pattern"></div>
    </div>

    <x-filament-panels::page.simple>
        {{ $this->content }}
    </x-filament-panels::page.simple>
</div>

<style>
    body:has(.fi-hkgold-login) .fi-simple-layout {
        min-height: 100dvh;
        display: flex;
        flex-direction: column;
    }

    body:has(.fi-hkgold-login) .fi-simple-main-ctn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    body:has(.fi-hkgold-login) .fi-simple-main {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fi-hkgold-login {
        position: relative;
        isolation: isolate;
        width: 100%;
    }

    .fi-hkgold-login__backdrop {
        position: fixed;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        background-color: #ffffff;
    }

    /* Diagonal putih → emas tipis → putih (brand mobile) */
    .fi-hkgold-login__gradient {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            135deg,
            #ffffff 0%,
            #ffffff 28%,
            rgba(245, 200, 66, 0.28) 48%,
            rgba(209, 161, 59, 0.22) 52%,
            #ffffff 72%,
            #ffffff 100%
        );
    }

    .fi-hkgold-login__pattern {
        position: absolute;
        inset: 0;
        background-image: url('{{ asset('images/pattern-horizontal.webp') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        opacity: 0.25;
    }

    .fi-hkgold-login .fi-simple-page {
        position: relative;
        z-index: 1;
        width: 100%;
        background-color: transparent;
    }

    .fi-hkgold-login .fi-simple-page-content {
        background-color: #ffffff;
        border-radius: 0.75rem;
        padding: 2rem;
        box-shadow: 0 20px 50px rgb(0 0 0 / 0.15);
    }
</style>
