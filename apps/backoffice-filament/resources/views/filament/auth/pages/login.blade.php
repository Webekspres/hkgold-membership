<div class="fi-hkgold-login">
    <div class="fi-hkgold-login__backdrop" aria-hidden="true"></div>

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
        background-image: url('{{ asset('images/background.webp') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        pointer-events: none;
    }

    @media (min-width: 1024px) {
        .fi-hkgold-login__backdrop {
            inset: auto;
            top: 50%;
            left: 50%;
            width: 100vh;
            height: 100vw;
            transform: translate(-50%, -50%) rotate(270deg);
        }
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
