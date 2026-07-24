<div class="fi-hkgold-login">
    {{-- Golden Backdrop (Full width on mobile, left half on desktop) --}}
    <div class="fi-hkgold-login__backdrop" aria-hidden="true">
        <div class="fi-hkgold-login__gradient"></div>
        <div class="fi-hkgold-login__pattern"></div>
    </div>

    {{-- Left Side: Golden Banner Content (Desktop) --}}
    <div class="fi-hkgold-login__hero" aria-hidden="true">
        <div class="fi-hkgold-login__hero-content">
            <div class="fi-hkgold-hero__top">
                <img src="{{ asset('images/logo-hkgold.webp') }}" alt="HK Gold Logo" class="fi-hkgold-hero__logo-img">
            </div>

            <div class="fi-hkgold-hero__main">
                <div class="fi-hkgold-hero__badge">
                    <span>Portal HK GOLD VIP</span>
                </div>
                <h1 class="fi-hkgold-hero__title">
                    Platform Membership<br>
                    <span class="fi-hkgold-hero__title-gold">& Loyalty HK Gold</span>
                </h1>
                <p class="fi-hkgold-hero__subtitle">
                    Kelola data keanggotaan, poin loyalty, katalog reward, serta transaksi member dalam satu dashboard terintegrasi.
                </p>
            </div>

            <div class="fi-hkgold-hero__footer">
                <p>&copy; {{ date('Y') }} HK Gold Membership System. All rights reserved.</p>
            </div>
        </div>
    </div>

    {{-- Right Side: Login Card --}}
    <div class="fi-hkgold-login__card-container">
        <div class="fi-hkgold-login__card-wrapper">
            <x-filament-panels::page.simple>
                {{ $this->content }}
            </x-filament-panels::page.simple>

            <div class="fi-hkgold-login__security-footer">
                <svg class="fi-hkgold-security-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <span>Akses aman dan terenkripsi</span>
            </div>
        </div>
    </div>
</div>

<style>
    /* Full layout reset for login page inside Filament simple layout */
    body:has(.fi-hkgold-login) {
        margin: 0;
        padding: 0;
        background-color: #151009;
    }

    body:has(.fi-hkgold-login) .fi-simple-layout {
        min-height: 100dvh;
        display: flex;
        flex-direction: column;
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
    }

    body:has(.fi-hkgold-login) .fi-simple-main-ctn {
        flex: 1;
        display: flex;
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
    }

    body:has(.fi-hkgold-login) .fi-simple-main {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* Outer Wrapper */
    .fi-hkgold-login {
        display: flex;
        width: 100%;
        min-height: 100dvh;
        position: relative;
        isolation: isolate;
        background-color: #151009;
    }

    /* Golden Backdrop (Gradient + Pattern Overlay) */
    .fi-hkgold-login__backdrop {
        position: absolute;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        overflow: hidden;
    }

    .fi-hkgold-login__gradient {
        position: absolute;
        inset: 0;
        /* Match sidebar chrome — solid dark, no gold wash */
        background: #0a0a0a;
    }

    .fi-hkgold-login__pattern {
        position: absolute;
        inset: 0;
        background-image: url('{{ asset('images/pattern-horizontal.webp') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        opacity: 0.18;
        /* no blur — crisp pattern; skip overlay blend so pattern stays visible on #0a0a0a */
        filter: none;
        mix-blend-mode: normal;
    }

    /* Left Hero Content (Desktop) */
    .fi-hkgold-login__hero {
        display: none;
        width: 50%;
        position: relative;
        z-index: 1;
        overflow: hidden;
        flex-direction: column;
        justify-content: space-between;
        padding: 3.5rem 4rem;
    }

    .fi-hkgold-login__hero-content {
        position: relative;
        z-index: 1;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        color: #ffffff;
    }

    .fi-hkgold-hero__top {
        display: flex;
        align-items: center;
    }

    .fi-hkgold-hero__logo-img {
        height: 2.75rem;
        width: auto;
        object-fit: contain;
    }

    .fi-hkgold-hero__main {
        max-width: 32rem;
        margin-top: auto;
        margin-bottom: auto;
        padding-top: 3rem;
        padding-bottom: 3rem;
    }

    .fi-hkgold-hero__badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
        background: rgba(245, 203, 104, 0.15);
        border: 1px solid rgba(245, 203, 104, 0.35);
        color: #f5cb68;
        font-size: 0.8125rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(4px);
    }

    .fi-hkgold-hero__title {
        font-size: 2.5rem;
        line-height: 1.2;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 1.25rem;
        letter-spacing: -0.02em;
    }

    .fi-hkgold-hero__title-gold {
        background: linear-gradient(90deg, #ffe596 0%, #f5cb68 50%, #e5b038 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .fi-hkgold-hero__subtitle {
        font-size: 1.05rem;
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.82);
        font-weight: 400;
    }

    .fi-hkgold-hero__footer {
        font-size: 0.8125rem;
        color: rgba(255, 255, 255, 0.55);
    }

    /* Right Side Login Form Container — match Filament page gray wash */
    .fi-hkgold-login__card-container {
        width: 100%;
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1.5rem;
        background-color: #f1f5f9;
        background-image: linear-gradient(
            120deg,
            #f1f5f9 0%,
            #e8eef4 45%,
            #e2e8f0 80%,
            #d8e0ea 100%
        );
    }

    .fi-hkgold-login__card-wrapper {
        width: 100%;
        max-width: 26rem;
    }

    .fi-hkgold-login .fi-simple-page {
        position: relative;
        width: 100%;
        background-color: transparent;
    }

    .fi-hkgold-login .fi-simple-page-content {
        display: flex;
        flex-direction: column;
        background-color: #ffffff;
        border-radius: 1.25rem;
        padding: 2.5rem 2.25rem;
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.08), 0 0 1px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.04);
    }

    /* Replace brand logo in simple page header with oval logo-icon.webp */
    .fi-hkgold-login .fi-logo img,
    .fi-hkgold-login .fi-simple-header img,
    .fi-hkgold-login .fi-simple-page-header img {
        content: url('{{ asset('images/logo-icon.webp') }}') !important;
        height: 4.25rem !important;
        max-height: 4.5rem !important;
        width: auto !important;
        object-fit: contain !important;
        margin-left: auto;
        margin-right: auto;
    }

    /* Header text & gold badge styling inside card */
    .fi-hkgold-login .fi-simple-header,
    .fi-hkgold-login .fi-simple-page-header {
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .fi-hkgold-login .fi-simple-header-heading,
    .fi-hkgold-login .fi-simple-page-heading,
    .fi-hkgold-login .fi-header-heading {
        font-size: 1.625rem !important;
        font-weight: 800 !important;
        color: #111827 !important;
        margin-top: 0.25rem !important;
        margin-bottom: 0.35rem !important;
    }

    /* Insert Portal HK GOLD VIP badge above heading */
    .fi-hkgold-login .fi-simple-header-heading::before,
    .fi-hkgold-login .fi-simple-page-heading::before,
    .fi-hkgold-login .fi-header-heading::before {
        content: 'Portal HK GOLD VIP';
        display: block;
        font-size: 0.875rem;
        font-weight: 700;
        color: #d9a838;
        letter-spacing: 0.02em;
        margin-bottom: 0.25rem;
    }

    .fi-hkgold-login .fi-simple-header-subheading,
    .fi-hkgold-login .fi-simple-page-subheading,
    .fi-hkgold-login .fi-header-subheading {
        font-size: 0.875rem !important;
        color: #6b7280 !important;
        font-weight: 400 !important;
    }

    /* Gold button styling */
    .fi-hkgold-login .fi-btn {
        background: linear-gradient(135deg, #d1a13b 0%, #ebca86 50%, #9a6b1f 100%) !important;
        border: none !important;
        border-radius: 0.625rem !important;
        font-weight: 700 !important;
        color: #1c1917 !important;
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
        font-size: 1rem !important;
        box-shadow: 0 4px 14px rgba(217, 168, 56, 0.25) !important;
        transition: all 0.2s ease;
    }

    .fi-hkgold-login .fi-btn:hover {
        filter: brightness(1.05);
        transform: translateY(-1px);
    }

    /* Security footer */
    .fi-hkgold-login__security-footer {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        margin-top: 1.25rem;
        color: #6b7280;
        font-size: 0.8125rem;
        font-weight: 500;
    }

    .fi-hkgold-security-icon {
        width: 1rem;
        height: 1rem;
        color: #6b7280;
    }

    @media (min-width: 1024px) {
        .fi-hkgold-login__hero {
            display: flex;
        }

        .fi-hkgold-login__card-container {
            width: 50%;
            background-color: #f1f5f9;
            background-image: linear-gradient(
                120deg,
                #f1f5f9 0%,
                #e8eef4 45%,
                #e2e8f0 80%,
                #d8e0ea 100%
            );
        }

        .fi-hkgold-login .fi-simple-page-content {
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.08), 0 0 1px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.04);
        }
    }
</style>
