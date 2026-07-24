# HK Gold VIP — Design System

Panduan UI untuk agent/dev agar tampilan **mobile** (`apps/mobile-app`) dan **backoffice** (`apps/backoffice-filament`) konsisten. Dokumen ini **as-is** dari kode yang hidup — sumber kebenaran token, bukan Figma.

| Produk | Stack UI | Audience |
| --- | --- | --- |
| Mobile member | Expo + NativeWind + RNR (`new-york` / stone) | Member VIP |
| Backoffice | Filament v5 + `public/css/filament-custom.css` | Staff / admin |

**Prinsip visual:** luxury emas premium di atas fondasi netral stone/slate. Light-only. Bahasa UI: **Bahasa Indonesia**.

---

## 1. Brand bersama (cross-app)

### 1.1 Aksen emas (CTA / active)

Gradient brand (3 stop) — dipakai CTA mobile + pill aktif sidebar Filament:

| Stop | Hex |
| --- | --- |
| Mid gold | `#D1A13B` |
| Highlight | `#ebca86` |
| Deep brass | `#9A6B1F` |

Arah default: diagonal `135deg` / RN `start {0,0}` → `end {1,1}`.

Warna emas UI chrome (ikon topbar / label sidebar / badge login):

| Token | Hex | Pakai |
| --- | --- | --- |
| Gold UI | `#f5cb68` | Ikon/teks di chrome gelap |
| Gold hover | `#ffe8a3` | Hover topbar/sidebar |
| Gold border | `rgba(245, 203, 104, 0.15–0.35)` | Border chrome, badge |

> Catatan: hex lama `#f5c842` → `#e8a020` masih muncul di komentar/`AGENTS.md` singkat. **Ikuti `brand.ts` + CSS Filament** di atas.

### 1.2 Chrome gelap + pattern

Dipakai **login kedua app** + **sidebar/topbar Filament**:

| Token | Nilai |
| --- | --- |
| Chrome BG | `#0a0a0a` |
| Pattern mobile auth | `assets/media/pattern-horizontal.webp`, opacity ~`0.38` |
| Pattern Filament sidebar | `/images/pattern-vertical.webp`, opacity `0.18` + blur `2px` |
| Pattern Filament topbar / login | `/images/pattern-horizontal.webp` |

### 1.3 Logo & aset

| Aset | Mobile | Backoffice |
| --- | --- | --- |
| Logo mark oval | `assets/logo/…` (auth card) | `images/logo-icon.webp` |
| Logo horizontal | — | `images/logo-horizontal.webp` (sidebar brand) |
| Pattern | `assets/media/pattern-*.webp` | `public/images/pattern-*.webp` |
| Kartu tier | `assets/media/tier/card-*.webp` | — (hanya mobile) |

### 1.4 Radius & shadow

| Konteks | Radius | Shadow |
| --- | --- | --- |
| Auth card (mobile + Filament login) | ~`20px` / `1.25rem` | soft hitam, bukan glow ungu |
| GoldButton | `6px` (inner `4px` outline) | — |
| Filament card/section | `0.75rem` | `0 4px 20px -4px rgba(0,0,0,0.04)` |
| Pill nav (sidebar / tab) | `9999px` | gold soft saat active |

---

## 2. Mobile app

**Sumber token:** `src/config/brand.ts`, `src/lib/theme.ts`, `src/global.css`, `src/lib/fonts.ts`, `tailwind.config.js`.

### 2.1 Fondasi (stone / RNR)

Semantic via CSS variables → class NativeWind: `bg-background`, `text-foreground`, `bg-muted`, `text-muted-foreground`, `border-border`, `bg-primary`, …

| Semantic (light) | HSL ringkas |
| --- | --- |
| background | putih `0 0% 100%` |
| foreground | near-black warm `20 14.3% 4.1%` |
| muted / accent | stone soft `60 4.8% 95.9%` |
| muted-foreground | `25 5.3% 44.7%` |
| primary (RNR) | gelap stone — **bukan** emas; emas = brand overlay |
| destructive | merah `0 84.2% 60.2%` |
| radius | `0.625rem` |

App **light-only** (`userInterfaceStyle: "light"`). Jangan hardcode hex untuk layout netral — pakai token; hex boleh untuk aksen brand di `@/config/brand`.

### 2.2 Tipografi

| Family | Font | Pakai |
| --- | --- | --- |
| Sans | **Rubik** (400–700) | Body, UI, tombol |
| Serif | **Libre Baskerville** (400–700) | Nama member / luxury text di kartu |

- Komponen teks: `@/components/ui/text` (variant RNR), bukan `Text` RN mentah.
- Utility: `font-sans`, `font-semibold`, `font-libre-baskerville*`.
- Gradasi teks: `SilverGradientText` / `GoldGradientText`.

### 2.3 Spacing

| Sumber | Nilai |
| --- | --- |
| Padding horizontal layar | `SCREEN_HORIZONTAL_PADDING = 16` (`constants/layout/screen-layout`) |
| Skala starter | `Spacing`: 2 / 4 / 8 / 16 / 24 / 32 / 64 |
| Tailwind | `gap-4`, `p-6`, `rounded-xl` — breathing room |

### 2.4 Tier visual (kartu member)

Registry: `TIER_GRADIENTS` + `TIER_CARD_BACKGROUND_IMAGES` di `brand.ts`.

| Tier | Karakter warna |
| --- | --- |
| SILVER | Chrome cool + specular putih |
| GOLD | Bronze → satin gold multi-stop |
| PLATINUM | Charcoal / titanium gelap |
| ELITE | Blue deep (`#1e3a8a` … `#3b82f6`) + vignette |

Jangan invent gradient tier baru di screen — extend `brand.ts`.

### 2.5 Komponen kunci (reuse dulu)

| Komponen | Path | Peran |
| --- | --- | --- |
| `GoldButton` | `components/shared/gold-button` | CTA utama (`filled` / `outline`) |
| `GoldCircleIcon` | `…/gold-circle-icon` | Shortcut home / menu (destructive = merah) |
| `AuthScreenShell` | `components/auth/auth-screen-shell` | Login/register — chrome gelap + pattern + card putih |
| `AnimatedTabBar` | `components/shared/animated-tab-bar` | Tab bar gelap `#1A1A1A`, active putih + ikon gold |
| `ContentDetailScreen` | `components/shared/content-detail-screen` | Detail berita/event/reward |
| `SearchInput` + debounce 500ms | shared | List search (`q` hanya jika panjang > 2) |
| `DateRangeFilterModal` | shared | Filter tanggal |
| `SuspendedNotice` | shared | Member suspended |
| RNR `button` / `card` / `input` / `dialog` | `components/ui/*` | Primitif netral |

Tab bar tokens (`brand.ts`): `DARK_TAB_BAR_BACKGROUND`, `DARK_TAB_ACTIVE_BACKGROUND`, `DARK_TAB_ICON_*`, `GOLD_TAB_SELECTED`.

### 2.6 Pola layar contoh

**Auth (login/register)**  
Chrome `#0a0a0a` + pattern horizontal → card putih `rounded-[20px]` max-width ~416 → logo oval → form → `GoldButton` full. Status bar light-content. Samakan dengan login Filament mobile.

**Home**  
Wallet / kartu tier (gradient + pattern) → shortcut `GoldCircleIcon` → banner slider → section list. Pull-to-refresh tint gold `#e8a020` / brand.

**List (berita/event/cabang/reward)**  
Padding 16 → `SearchInput` → filter modal → infinite list card → detail lewat `ContentDetailScreen` bila konten.

**Kartu member / redeem QR**  
Background tier image + vignette; nomor member pill gold; QR di card gold outline.

---

## 3. Backoffice Filament

**Sumber token:** `AppPanelProvider` (`primary => '#ebca86'`), `public/css/filament-custom.css`, login blade `resources/views/filament/auth/pages/login.blade.php`.

### 3.1 Theme mode

- `darkMode(false)` + `ThemeMode::Light` — **jangan** aktifkan dark Filament.
- Primary Filament: `#ebca86` (selaras stop tengah gradient brand).
- Custom CSS wajib via `Css::make(..., public_path('css/filament-custom.css'))` + cache-bust `filemtime` (lihat `AppPanelProvider`).

### 3.2 Chrome (sidebar + topbar)

| Elemen | Perilaku |
| --- | --- |
| BG | `#0a0a0a` + pattern overlay |
| Border | `1px solid rgba(245, 203, 104, 0.15)` |
| Group label | `#f5cb68`, uppercase, tracking lebar |
| Item idle | teks putih, ikon gold |
| Item hover | `rgba(245, 203, 104, 0.12)` |
| Item active | gradient `#d1a13b → #ebca86 → #9a6b1f`, label/ikon **hitam**, pill full |
| Topbar control | warna `#f5cb68` (dropdown panel reset ke teks gelap) |
| Global search | disembunyikan |

**Jangan** set `position: relative` pada `.fi-sidebar` (pecah sticky).

### 3.3 Page canvas

Bukan gold wash — soft slate:

```text
#f1f5f9 → #e8eef4 → #e2e8f0 → #d8e0ea (120deg, fixed)
```

Card/section/widget: putih, border `rgba(0,0,0,0.08)`, radius `0.75rem`, shadow lembut.

### 3.4 Login

- Desktop: split hero kiri (logo + copy) + card kanan.
- Mobile: full dark + pattern (sama roh dengan mobile auth); card putih `border-radius: 1.25rem`.
- Badge hero: gold translucent + border gold.
- Judul aksen: gradient text `#ffe596 → #f5cb68 → #e5b038`.

### 3.5 Navigasi & label

Semua label Filament **Bahasa Indonesia**. Group yang dipakai:

`CMS` · `Katalog Reward` · `Loyalty Point` · `Redeem Poin` · `Manajemen Pengguna` · `Master Lokasi` · `Konfigurasi` · `Notifikasi`

Ikon: `Heroicon::Outlined*` konsisten resource sejenis. Struktur Resource: Form / Table / Infolist terpisah (lihat `AGENTS.md` backoffice).

### 3.6 Pola layar contoh

**Dashboard / list Resource**  
Canvas slate → section/card putih → table Filament; aksi primary ikut warna `#ebca86`.

**Wizard redeem (Antrean Kupon)**  
Modal multi-step + scan QR; aset scanner di-hook `BODY_END` — jangan andalkan `app.js` panel.

**Form CMS / media**  
Upload disk `r2`; crop/rasio ikuti resource existing (banner, content).

---

## 4. Alignment mobile ↔ backoffice

| Pola | Mobile | Backoffice |
| --- | --- | --- |
| Login chrome | `AuthScreenShell` `#0a0a0a` + pattern | `.fi-hkgold-login` sama |
| CTA emas | `GoldButton` / `GOLD_GRADIENT_COLORS` | Primary `#ebca86` + CSS gradient pill |
| Teks netral | stone / Rubik | Filament default + Inter vendor (jangan ganti font stack panel tanpa request) |
| Light-only | ya | ya |
| Bahasa UI | ID | ID |

Kalau ubah gradient brand: update **`brand.ts` + `filament-custom.css` (+ primary panel)** bareng supaya tidak drift.

---

## 5. Do / Don’t (agent)

**Do**

- Reuse `GoldButton`, `AuthScreenShell`, token `brand.ts`, class semantic NativeWind.
- Ikuti padding `SCREEN_HORIZONTAL_PADDING` di screen baru.
- String user-facing Bahasa Indonesia.
- Filament: daftar CSS via `public_path(...)`; label ID + group existing.

**Don’t**

- Jangan invent purple glow / pill cluster / flat “SaaS starter” look.
- Jangan hardcode gold random di screen — taruh di `brand.ts` atau CSS shared.
- Jangan aktifkan dark mode tanpa keputusan produk.
- Jangan pakai `StyleSheet` untuk layout baru di mobile kecuali animasi / gradient style prop.
- Jangan commit asset kredensial; logo/pattern tetap di path aset di atas.

---

## 6. File referensi cepat

```text
# Mobile
apps/mobile-app/src/config/brand.ts
apps/mobile-app/src/lib/theme.ts
apps/mobile-app/src/lib/fonts.ts
apps/mobile-app/src/global.css
apps/mobile-app/src/components/shared/gold-button.tsx
apps/mobile-app/src/components/auth/auth-screen-shell.tsx
apps/mobile-app/src/components/shared/animated-tab-bar.tsx

# Backoffice
apps/backoffice-filament/app/Providers/Filament/AppPanelProvider.php
apps/backoffice-filament/public/css/filament-custom.css
apps/backoffice-filament/resources/views/filament/auth/pages/login.blade.php
```

Detail arsitektur / status fitur: `apps/mobile-app/AGENTS.md`, `apps/backoffice-filament/AGENTS.md`.
