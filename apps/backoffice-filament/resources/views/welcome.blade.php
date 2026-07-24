@php
    $officialUrl = 'https://hkgoldofficial.com';
    $staffLoginUrl = url('/app/login');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HK GOLD VIP — Portal Membership</title>
    <meta name="description" content="Portal resmi HK GOLD VIP. Keunggulan membership, level tiering, dan akses portal staf Filament.">

    @fonts

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="lp-body antialiased">
    <div class="lp-pattern" aria-hidden="true"></div>

    {{-- ========== Nav ========== --}}
    <header class="lp-header">
        <div class="lp-container flex items-center justify-between gap-4 py-4">
            <a href="#top" class="flex shrink-0 items-center gap-2">
                <img
                    src="{{ asset('images/logo-hkgold.webp') }}"
                    alt="HK GOLD"
                    class="h-9 w-auto object-contain"
                >
            </a>

            <nav class="hidden items-center gap-8 text-sm font-medium text-white/80 lg:flex" aria-label="Utama">
                <a href="#keunggulan" class="lp-nav-link">Keunggulan Membership</a>
                <a href="#tiering" class="lp-nav-link">Tiering Level</a>
                <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-nav-link">Website Utama</a>
            </nav>

            <div class="hidden items-center gap-4 lg:flex">
                <a href="{{ $staffLoginUrl }}" class="lp-nav-link text-sm font-medium">Portal Staf</a>
                <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-btn-gold px-4 py-2 text-sm">
                    HK GOLD OFFICIAL
                </a>
            </div>

            <details class="lp-mobile-nav relative lg:hidden">
                <summary class="lp-mobile-nav__btn" aria-label="Menu">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="size-6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </summary>
                <div class="lp-mobile-nav__panel">
                    <a href="#keunggulan">Keunggulan Membership</a>
                    <a href="#tiering">Tiering Level</a>
                    <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer">Website Utama</a>
                    <a href="{{ $staffLoginUrl }}">Portal Staf</a>
                    <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-btn-gold mt-2 justify-center px-4 py-2.5 text-sm">
                        HK GOLD OFFICIAL
                    </a>
                </div>
            </details>
        </div>
    </header>

    <main id="top">
        {{-- ========== Hero ========== --}}
        <section class="lp-container grid items-center gap-12 py-16 lg:grid-cols-2 lg:gap-16 lg:py-24">
            <div>
                <p class="lp-eyebrow mb-4">Established 1918</p>
                <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl lg:leading-[1.15]">
                    Warisan Kemewahan &amp;
                    <em class="lp-gold-text not-italic font-extrabold">Keuntungan Eksklusif</em>
                    HK GOLD VIP
                </h1>
                <p class="mt-5 max-w-xl text-base leading-relaxed text-white/75 sm:text-lg">
                    Lebih dari satu abad warisan emas premium. Program loyalty HK GOLD VIP menghadirkan poin otomatis,
                    benefit seumur hidup, dan akses koleksi eksklusif bagi member setia.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-btn-gold gap-2 px-5 py-3 text-sm sm:text-base">
                        Kunjungi hkgoldofficial.com
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 0 0-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 0 0 .75-.75v-4a.75.75 0 0 1 1.5 0v4A2.25 2.25 0 0 1 12.75 17h-8.5A2.25 2.25 0 0 1 2 14.75v-8.5A2.25 2.25 0 0 1 4.25 4h5a.75.75 0 0 1 0 1.5h-5Z" clip-rule="evenodd" />
                            <path fill-rule="evenodd" d="M6.194 12.753a.75.75 0 0 0 1.06.053L16.5 4.44v2.81a.75.75 0 0 0 1.5 0v-4.5a.75.75 0 0 0-.75-.75h-4.5a.75.75 0 0 0 0 1.5h2.553l-9.056 8.194a.75.75 0 0 0-.053 1.06Z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <a href="{{ $staffLoginUrl }}" class="lp-btn-outline gap-2 px-5 py-3 text-sm sm:text-base">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd" />
                        </svg>
                        Masuk Portal Staf
                    </a>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-md lg:mx-0 lg:max-w-none lg:justify-self-end">
                <div class="lp-card-frame relative overflow-hidden rounded-2xl p-1.5">
                    <img
                        src="{{ asset('images/tier/card-platinum.webp') }}"
                        alt="Kartu membership Platinum VIP"
                        class="aspect-[1.586/1] w-full rounded-xl object-cover"
                    >
                </div>
                <div class="lp-toast absolute -bottom-3 left-4 right-4 sm:left-auto sm:right-6 sm:bottom-6 sm:max-w-[16rem]">
                    <span class="lp-toast__dot" aria-hidden="true"></span>
                    <div>
                        <p class="text-xs font-semibold text-[#f5cb68]">Poin Otomatis</p>
                        <p class="text-sm text-white/90">+150 Points Today</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ========== Keunggulan ========== --}}
        <section id="keunggulan" class="lp-container scroll-mt-24 py-16 lg:py-24">
            <div class="mx-auto max-w-2xl text-center">
                <p class="lp-eyebrow mb-3">Eksklusivitas Tanpa Batas</p>
                <h2 class="text-2xl font-bold tracking-tight text-white sm:text-3xl lg:text-4xl">
                    Keunggulan Keanggotaan HK GOLD VIP
                </h2>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <article class="lp-feature-card">
                    <div class="lp-icon-box" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-white">Poin Otomatis</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/65">
                        Setiap transaksi dihitung dan dikonversi menjadi poin loyalty secara otomatis sesuai aturan tier member.
                    </p>
                </article>

                <article class="lp-feature-card">
                    <div class="lp-icon-box" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-white">Masa Aktif Seumur Hidup</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/65">
                        Keanggotaan VIP tidak kedaluwarsa. Nikmati benefit berkesinambungan selama menjadi bagian keluarga HK GOLD.
                    </p>
                </article>

                <article class="lp-feature-card sm:col-span-2 lg:col-span-1">
                    <div class="lp-icon-box" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-white">Penukaran Hadiah</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/65">
                        Tukarkan poin dengan koleksi emas premium, voucher, atau reward eksklusif melalui aplikasi member.
                    </p>
                </article>
            </div>
        </section>

        {{-- ========== Tiering ========== --}}
        <section id="tiering" class="lp-container scroll-mt-24 py-16 lg:py-24">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-xl">
                    <h2 class="text-2xl font-bold tracking-tight text-white sm:text-3xl lg:text-4xl">
                        Level Keanggotaan
                    </h2>
                    <p class="mt-3 text-sm leading-relaxed text-white/65 sm:text-base">
                        Empat tingkatan loyalty dengan privilege yang meningkat seiring perjalanan member di HK GOLD VIP.
                    </p>
                </div>
                <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-nav-link shrink-0 text-sm font-semibold text-[#f5cb68]">
                    Lihat Detail Syarat Tiering
                </a>
            </div>

            <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <article class="lp-tier-card">
                    <div class="lp-icon-box lp-icon-box--sm" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-white">Silver</h3>
                    <p class="text-xs font-medium uppercase tracking-wider text-white/45">Basic</p>
                    <ul class="mt-4 space-y-2 text-sm text-white/70">
                        <li>Multiplier poin 1×</li>
                        <li>Katalog produk umum</li>
                        <li>Notifikasi promo</li>
                    </ul>
                </article>

                <article class="lp-tier-card">
                    <div class="lp-icon-box lp-icon-box--sm" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.871m0 0a3.001 3.001 0 0 0 5.008 0m0 0a3 3 0 0 0-5.008 0" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-white">Gold</h3>
                    <p class="text-xs font-medium uppercase tracking-wider text-white/45">Preferred</p>
                    <ul class="mt-4 space-y-2 text-sm text-white/70">
                        <li>Multiplier poin 1,5×</li>
                        <li>Akses event tahunan</li>
                        <li>Prioritas layanan toko</li>
                    </ul>
                </article>

                <article class="lp-tier-card">
                    <div class="lp-icon-box lp-icon-box--sm" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-white">Platinum</h3>
                    <p class="text-xs font-medium uppercase tracking-wider text-white/45">Elite</p>
                    <ul class="mt-4 space-y-2 text-sm text-white/70">
                        <li>Multiplier poin 2×</li>
                        <li>CS prioritas 24/7</li>
                        <li>Event &amp; preview eksklusif</li>
                    </ul>
                </article>

                <article class="lp-tier-card lp-tier-card--accent">
                    <div class="lp-icon-box lp-icon-box--sm" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H18A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6A2.25 2.25 0 0 1 6 3.75h1.5m9 0h-9" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-white">Elite</h3>
                    <p class="text-xs font-medium uppercase tracking-wider text-[#f5cb68]/Prestige</p>
                    <ul class="mt-4 space-y-2 text-sm text-white/70">
                        <li>Multiplier poin 3×</li>
                        <li>Undangan private viewing</li>
                        <li>Personal shopping assistant</li>
                    </ul>
                </article>
            </div>
        </section>

        {{-- ========== CTA Staf ========== --}}
        <section class="lp-container py-16 lg:py-20">
            <div class="lp-cta relative overflow-hidden rounded-2xl px-6 py-10 sm:px-10 lg:flex lg:items-center lg:justify-between lg:gap-10 lg:px-12 lg:py-12">
                <div class="lp-cta__pattern" aria-hidden="true"></div>
                <div class="relative z-10 max-w-xl">
                    <h2 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Akses Portal Staf &amp; Informasi Resmi
                    </h2>
                    <p class="mt-3 text-sm leading-relaxed text-white/70 sm:text-base">
                        Staf cabang dan admin dapat masuk ke backoffice Filament dengan kredensial internal untuk mengelola
                        member, poin, reward, dan operasional loyalty.
                    </p>
                </div>
                <div class="relative z-10 mt-8 flex shrink-0 flex-col gap-3 sm:flex-row lg:mt-0 lg:flex-col xl:flex-row">
                    <a href="{{ $staffLoginUrl }}" class="lp-btn-gold gap-2 px-5 py-3 text-sm sm:text-base">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 0 1 5.25 2h5.5A2.25 2.25 0 0 1 13 4.25v2a.75.75 0 0 1-1.5 0v-2a.75.75 0 0 0-.75-.75h-5.5a.75.75 0 0 0-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 0 0 .75-.75v-2a.75.75 0 0 1 1.5 0v2A2.25 2.25 0 0 1 10.75 18h-5.5A2.25 2.25 0 0 1 3 15.75V4.25Z" clip-rule="evenodd" />
                            <path fill-rule="evenodd" d="M19 10a.75.75 0 0 0-.75-.75H8.704l1.048-.943a.75.75 0 1 0-1.004-1.114l-2.5 2.25a.75.75 0 0 0 0 1.114l2.5 2.25a.75.75 0 1 0 1.004-1.114l-1.048-.943h9.546A.75.75 0 0 0 19 10Z" clip-rule="evenodd" />
                        </svg>
                        Login Backoffice Filament
                    </a>
                    <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-btn-outline gap-2 px-5 py-3 text-sm sm:text-base">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-1.5 0a6.5 6.5 0 1 1-11.016-4.582l2.04 1.99a.75.75 0 0 0 1.11-.011l2.18-2.38a.75.75 0 1 1 1.112 1.016l-1.46 1.595 2.66.055a.75.75 0 0 0 .03-1.5l-.01-.001Z" clip-rule="evenodd" />
                        </svg>
                        Website Resmi HK GOLD
                    </a>
                </div>
            </div>
        </section>
    </main>

    {{-- ========== Footer ========== --}}
    <footer class="border-t border-[rgba(245,203,104,0.15)]">
        <div class="lp-container grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2 lg:col-span-1">
                <img
                    src="{{ asset('images/logo-horizontal.webp') }}"
                    alt="HK GOLD"
                    class="h-8 w-auto object-contain"
                >
                <p class="mt-4 text-sm leading-relaxed text-white/55">
                    Warisan emas premium sejak 1918. Program loyalty HK GOLD VIP untuk member setia di seluruh cabang.
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#f5cb68]">Navigasi</p>
                <ul class="mt-4 space-y-2 text-sm text-white/70">
                    <li><a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-nav-link">Tentang Kami</a></li>
                    <li><a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-nav-link">Katalog</a></li>
                    <li><a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-nav-link">Harga Emas Hari Ini</a></li>
                    <li><a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-nav-link">Lokasi Cabang</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#f5cb68]">Dukungan</p>
                <ul class="mt-4 space-y-2 text-sm text-white/70">
                    <li><a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-nav-link">Customer Service</a></li>
                    <li><a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-nav-link">Pusat Bantuan</a></li>
                    <li><a href="{{ $staffLoginUrl }}" class="lp-nav-link">Klaim Poin (Staf)</a></li>
                    <li><a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-nav-link">Syarat &amp; Ketentuan</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#f5cb68]">Ikuti Kami</p>
                <div class="mt-4 flex gap-3">
                    <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-social" aria-label="Website">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-6.5-4.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM9 9.75a.75.75 0 0 0-1.5 0V14a.75.75 0 0 0 1.5 0V9.75Zm3.5 0a.75.75 0 0 0-1.5 0V14a.75.75 0 0 0 1.5 0V9.75Z" clip-rule="evenodd" /></svg>
                    </a>
                    <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" class="lp-social" aria-label="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h9A3.75 3.75 0 0 1 20.25 7.5v9a3.75 3.75 0 0 1-3.75 3.75h-9A3.75 3.75 0 0 1 3.75 16.5v-9A3.75 3.75 0 0 1 7.5 3.75Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5ZM17.25 7.5h.008v.008H17.25V7.5Z" /></svg>
                    </a>
                    <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" class="lp-social" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5"><path d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm1.4 8.2V16H9.2v-5.8H7.8V8.4h1.4V7.1c0-1.4.8-2.2 2.1-2.2.6 0 1.2.1 1.2.1v1.4h-.7c-.7 0-.9.3-.9.9v1.1h1.6l-.3 1.8h-1.3Z" /></svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="lp-container flex flex-col gap-3 border-t border-[rgba(245,203,104,0.1)] py-6 text-xs text-white/45 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; 1918–{{ date('Y') }} PT Ham Kwan Gold Investama.</p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-nav-link">Privacy Policy</a>
                <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-nav-link">Terms of Service</a>
                <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="lp-nav-link">Official Site</a>
            </div>
        </div>
    </footer>

    <style>
        /* Landing — brand tokens mirror Filament login chrome */
        .lp-body {
            margin: 0;
            min-height: 100dvh;
            background-color: #0a0a0a;
            color: #fff;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            position: relative;
        }

        .lp-pattern {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background-image: url('{{ asset('images/pattern-horizontal.webp') }}');
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
            opacity: 0.22;
        }

        .lp-header,
        main,
        footer {
            position: relative;
            z-index: 1;
        }

        .lp-header {
            position: sticky;
            top: 0;
            z-index: 40;
            backdrop-filter: blur(12px);
            background: rgba(10, 10, 10, 0.85);
            border-bottom: 1px solid rgba(245, 203, 104, 0.15);
        }

        .lp-container {
            width: 100%;
            max-width: 72rem;
            margin-inline: auto;
            padding-inline: 1.25rem;
        }

        @media (min-width: 640px) {
            .lp-container {
                padding-inline: 1.5rem;
            }
        }

        .lp-eyebrow {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #f5cb68;
        }

        .lp-gold-text {
            background: linear-gradient(90deg, #ffe596 0%, #f5cb68 50%, #e5b038 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-style: italic;
        }

        .lp-nav-link {
            transition: color 0.15s ease;
        }

        .lp-nav-link:hover {
            color: #ffe8a3;
        }

        .lp-btn-gold {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            font-weight: 600;
            color: #0a0a0a;
            background-image: linear-gradient(135deg, #d1a13b 0%, #ebca86 50%, #9a6b1f 100%);
            transition: filter 0.15s ease, transform 0.15s ease;
        }

        .lp-btn-gold:hover {
            filter: brightness(1.08);
        }

        .lp-btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            font-weight: 600;
            color: #fff;
            border: 1px solid rgba(245, 203, 104, 0.35);
            background: rgba(255, 255, 255, 0.04);
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .lp-btn-outline:hover {
            background: rgba(245, 203, 104, 0.12);
            border-color: rgba(245, 203, 104, 0.5);
        }

        .lp-card-frame {
            border: 1px solid rgba(245, 203, 104, 0.25);
            background: linear-gradient(145deg, rgba(245, 203, 104, 0.12), rgba(10, 10, 10, 0.4));
            box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.55);
        }

        .lp-toast {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            background: rgba(10, 10, 10, 0.92);
            border: 1px solid rgba(245, 203, 104, 0.3);
            box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.5);
        }

        .lp-toast__dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
            background: #f5cb68;
            box-shadow: 0 0 0 4px rgba(245, 203, 104, 0.2);
            flex-shrink: 0;
        }

        .lp-feature-card,
        .lp-tier-card {
            border-radius: 0.75rem;
            border: 1px solid rgba(245, 203, 104, 0.15);
            background: rgba(255, 255, 255, 0.03);
            padding: 1.5rem;
            transition: border-color 0.15s ease, background 0.15s ease;
        }

        .lp-feature-card:hover,
        .lp-tier-card:hover {
            border-color: rgba(245, 203, 104, 0.35);
            background: rgba(245, 203, 104, 0.06);
        }

        .lp-tier-card--accent {
            border-color: rgba(245, 203, 104, 0.35);
            background: rgba(245, 203, 104, 0.08);
        }

        .lp-icon-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            color: #f5cb68;
            background: rgba(245, 203, 104, 0.12);
            border: 1px solid rgba(245, 203, 104, 0.25);
        }

        .lp-icon-box--sm {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
        }

        .lp-cta {
            border: 1px solid rgba(245, 203, 104, 0.2);
            background: rgba(255, 255, 255, 0.03);
        }

        .lp-cta__pattern {
            position: absolute;
            inset: 0;
            background-image: url('{{ asset('images/pattern-horizontal.webp') }}');
            background-size: cover;
            background-position: center;
            opacity: 0.18;
            pointer-events: none;
        }

        .lp-social {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            color: #f5cb68;
            border: 1px solid rgba(245, 203, 104, 0.2);
            background: rgba(245, 203, 104, 0.04);
            transition: background 0.15s ease;
        }

        .lp-social:hover {
            background: rgba(245, 203, 104, 0.15);
        }

        .lp-mobile-nav summary {
            list-style: none;
            cursor: pointer;
            color: #f5cb68;
            display: inline-flex;
            padding: 0.5rem;
            border-radius: 0.375rem;
            border: 1px solid rgba(245, 203, 104, 0.25);
        }

        .lp-mobile-nav summary::-webkit-details-marker {
            display: none;
        }

        .lp-mobile-nav__panel {
            position: absolute;
            right: 0;
            top: calc(100% + 0.5rem);
            width: min(18rem, calc(100vw - 2rem));
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            padding: 0.75rem;
            border-radius: 0.75rem;
            background: rgba(10, 10, 10, 0.96);
            border: 1px solid rgba(245, 203, 104, 0.25);
            box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.6);
        }

        .lp-mobile-nav__panel a {
            padding: 0.625rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85);
        }

        .lp-mobile-nav__panel a:hover {
            background: rgba(245, 203, 104, 0.1);
            color: #ffe8a3;
        }

        html {
            scroll-behavior: smooth;
        }
    </style>
</body>
</html>
