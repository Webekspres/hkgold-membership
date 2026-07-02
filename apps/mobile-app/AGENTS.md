# AI Agent Development Directives: HK GOLD VIP Mobile Client

Anda adalah **Expert React Native & TypeScript Developer** yang membangun aplikasi seluler berperforma tinggi, aman, dan bernuansa premium (_luxury aesthetic_).

Tugas Anda adalah menulis, memodifikasi, dan merawat basis kode untuk aplikasi seluler **HK GOLD VIP (Mala Emas)** — program loyalitas ritel perhiasan emas nasional. Aplikasi ini beroperasi paralel dengan:

| Aplikasi | Path monorepo | Peran |
| --- | --- | --- |
| Backoffice Admin | `apps/backoffice-filament` | Laravel 13 + Filament v5 — CMS, member, redeem, fraud |
| Mobile API | `apps/api-elysia` | ElysiaJS + Bun — REST API untuk member app |
| Background Worker | `apps/worker-elysia` | ElysiaJS + Bun — job async (injeksi poin, notifikasi) |
| Shared Schema | `packages/database` | Prisma schema — sumber kebenaran data domain |

**Platform produksi:** iOS & Android saja. Dukungan web di repo ini hanya untuk development/preview starter; jangan mengoptimalkan fitur member untuk web kecuali diminta eksplisit.

**Dokumentasi Expo:** Sebelum menulis kode Expo, baca versi yang tepat di https://docs.expo.dev/versions/v56.0.0/

**Handoff chat baru:** Baca file ini **sebelum** mengubah kode. Ini adalah sumber kebenaran untuk struktur folder, konvensi import, routing, dan status implementasi mobile app.

**Branch Git aktif:** `mobile` di monorepo `hkgold-membership` (`apps/mobile-app/`).

---

## 1. Status Proyek & Roadmap

### Fase saat ini (sudah selesai)

Struktur folder sudah dievolusi (components per domain, `mocks/` + `config/` + `services/` + `types/`, routing seragam). **Seluruh layar konten member saat ini UI-only** — data dari mock, belum ada panggilan ke `apps/api-elysia`.

| Area | Status | Catatan |
| --- | --- | --- |
| Struktur `src/` | ✅ | Lihat §3 |
| Home `(tabs)/index` | ✅ UI mock | Wallet card, shortcut, banner, section event/berita/cabang/reward |
| List + detail Event | ✅ UI mock | Filter tanggal; detail: slider, lokasi, CTA maps |
| List + detail Berita | ✅ UI mock | Filter tanggal; detail: kategori, tanggal relatif |
| List Cabang | ✅ UI mock | Filter kota (dropdown) |
| List + detail Reward | ✅ UI mock | Filter kategori + rentang poin; redeem dialog (UI saja) |
| Auth login/register | ✅ UI saja | Belum wired ke API / JWT |
| Tab Card & Profile | 🔲 placeholder | `ComingSoonScreen` |
| CMS hub `/cms` | 🔲 placeholder | `ComingSoonScreen` |
| Integrasi API | ❌ | `axios`, React Query, encrypted storage belum dipasang |
| Auth gate | ❌ | Belum ada cek session di root layout |

### Fitur UI yang sudah dibangun (mock)

- **Home:** `MemberWalletCard`, `HomeShortcutGrid` (→ `/events`, `/berita`, `/cabang`, `/reward`), `PromotionBannerSlider`, section event/berita/cabang/reward dengan “Lihat semua”.
- **Event:** list + `EventFilterModal` (rentang tanggal); detail pakai `ContentDetailScreen` + highlight tanggal/waktu + tombol “Lihat lokasi”.
- **Berita:** list + filter tanggal; detail pakai `ContentDetailScreen` (tanpa CTA).
- **Cabang:** list + `BranchCityFilterDropdown`; kartu cabang buka maps via `openLocationUrl`.
- **Reward:** grid 2 kolom + `RewardFilterModal` (kategori + slider poin `@react-native-community/slider`); detail + stok per cabang + `RewardRedeemDialog`.

### Langkah berikutnya (prioritas wajar)

1. Pasang `axios` + `@tanstack/react-query` + `react-native-encrypted-storage`.
2. Implementasi auth (login API, simpan JWT, auth gate di `_layout.tsx`).
3. Ganti isi `src/services/*` dari mock ke API; pertahankan signature fungsi facade.
4. Tambah `services/member.ts` (wallet, tier) — home masih import `MOCK_MEMBER` & `MOCK_PROMOTION_BANNERS` langsung dari `mocks/`.
5. Wire redeem flow ke API; tab Card & Profile; CMS hub.

### Target jangka panjang (belum diimplementasi)

Auth gate penuh, ledger mutasi poin, pelacakan redeem OTP, proteksi suspended member, demo mode tamu, kalender pameran terintegrasi API. Rute baru mengikuti pola grup Expo Router (`(auth)`, `(tabs)`, `(tracking)`, dll.) tanpa mengubah fondasi stack §2.

### Fondasi teknis (ringkas)

Expo SDK 56 · React 19 · RN 0.85 · NativeWind v4 (stone) · RNR `new-york` · Reanimated v4 · Root: `GestureHandlerRootView` + `PortalHost` + splash `animated-icon` · Native tabs: `src/components/shared/app-tabs.tsx` · EAS `projectId` di `app.json`.

---

## 2. Locked Tech Stack

Anda **wajib** mematuhi stack berikut. Jangan mengganti dengan alternatif tanpa persetujuan eksplisit.

### Core (terpasang)

| Kategori | Pustaka | Catatan |
| --- | --- | --- |
| Framework | Expo SDK 56 + Expo Router | Entry: `expo-router/entry`, root di `src/app/` |
| Styling | NativeWind v4 + Tailwind v3 | Utility via `className`; config di `tailwind.config.js` |
| UI Components | React Native Reusables | `@rn-primitives/*`; terpasang: `button`, `text`, `card`, `input`, `dialog`, `icon`, `collapsible` |
| Animasi | react-native-reanimated v4 | Wajib untuk transisi premium |
| Gesture | react-native-gesture-handler | Root wrap di `_layout.tsx` |
| Gambar lokal & remote | **expo-image** | Caching & `.webp`; **jangan** pakai `react-native-fast-image` |
| Gradasi | expo-linear-gradient | Aksen emas pada CTA (lihat Design System) |
| Device ID | **expo-device** + **expo-application** | Hardware fingerprint untuk Marketing Protection Engine |
| Safe area | react-native-safe-area-context | Sudah terpasang |
| Ikon | expo-symbols (`SymbolView`) | Header back, filter, shortcut, tab |
| Tanggal | dayjs + react-native-ui-datepicker | Filter rentang tanggal event/berita |
| Slider poin | @react-native-community/slider | Filter reward (dua slider min/max) |
| Dropdown | react-native-element-dropdown | Filter kota cabang |
| Dialog | @rn-primitives/dialog + RNR `dialog` | Redeem confirmation |

### Wajib dipasang saat implementasi fitur terkait

| Kategori | Pustaka | Digunakan untuk |
| --- | --- | --- |
| Server state | **@tanstack/react-query** | Cache saldo poin, ledger, katalog |
| HTTP | **axios** | Client API dengan interceptor JWT global |
| Secure storage | **react-native-encrypted-storage** | JWT _stay logged in_ (Keychain / Keystore) |
| QR digital card | **react-native-qrcode-svg** | Kartu member SVG |

Instal dependensi baru dengan `npx expo install <package>` agar versi kompatibel SDK 56.

### Dilarang

- `react-native-fast-image` (gunakan `expo-image`)
- `react-native-device-info` (gunakan `expo-device` + `expo-application`)
- Styling library lain (Tamagui, Unistyles, StyleSheet-only untuk layout baru)
- `react-native-slider` / slider tanpa peer dep resmi RN 0.85 — gunakan `@react-native-community/slider`
- Import langsung dari `@react-navigation/*` di kode aplikasi (lihat §5)

---

## 3. Struktur Folder & Routing (Expo Router)

Root source: **`src/`** (bukan `app/` di root proyek).

```text
apps/mobile-app/
├── app.json
├── babel.config.js
├── metro.config.js              # withNativeWind, inlineRem: 16
├── tailwind.config.js
├── components.json              # RNR CLI config
├── nativewind-env.d.ts
└── src/
    ├── global.css               # Tailwind + CSS variables theme stone
    ├── app/
    │   ├── _layout.tsx          # Root Stack, theme, PortalHost, splash
    │   ├── (auth)/
    │   │   ├── _layout.tsx      # Stack auth (login, register)
    │   │   ├── login.tsx
    │   │   └── register.tsx
    │   ├── (tabs)/
    │   │   ├── _layout.tsx      # Native tabs wrapper
    │   │   ├── index.tsx        # Home / dasbor member
    │   │   ├── card.tsx
    │   │   └── profile.tsx
    │   ├── events/
    │   │   ├── index.tsx        # List event
    │   │   └── [slug].tsx       # Detail event
    │   ├── berita/
    │   │   ├── index.tsx
    │   │   └── [slug].tsx
    │   ├── cabang/
    │   │   └── index.tsx
    │   ├── reward/
    │   │   ├── index.tsx
    │   │   └── [sku].tsx
    │   └── cms.tsx
    ├── components/
    │   ├── ui/                  # Komponen RNR (button, text, card, input, …)
    │   ├── auth/                # Shell & field auth
    │   ├── shared/              # Cross-feature (gold-button, app-tabs, content-detail-*)
    │   ├── home/                # Section homepage
    │   ├── event/
    │   ├── berita/
    │   ├── reward/
    │   └── branch/
    ├── config/                  # brand, home-shortcuts, theme (Colors/Fonts starter)
    ├── constants/
    │   └── layout/              # grid, carousel, screen-layout tokens
    ├── mocks/                   # Fixture data (mock-*)
    ├── types/                   # Shared domain types
    ├── services/                # Data facade — mock sekarang, API nanti
    ├── hooks/                   # use-color-scheme (hardcode light), use-theme
    ├── lib/
    │   ├── filters/             # filter-events, filter-news, filter-rewards, …
    │   ├── format/              # format-event-date, format-branch-location, …
    │   ├── utils.ts             # cn() helper
    │   ├── theme.ts             # THEME + NAV_THEME (stone)
    │   ├── date-range-filter.ts
    │   └── open-location-url.ts

assets/                            # Di root proyek (bukan di src/)
├── logo/logo-hkgold.webp
├── media/background.webp
└── mockImage/                     # Gambar fixture list/detail
```

### Konvensi import data

| Lapisan | Import dari | Catatan |
| --- | --- | --- |
| **Screen** (`src/app/`) | `@/services/*` | Jangan import `@/mocks/*` di route — **kecuali** sementara: home memakai `MOCK_MEMBER`, `MOCK_PROMOTION_BANNERS` |
| **Komponen** | props dari parent | Import `@/types/*` untuk tipe props; jangan panggil service di komponen presentasional |
| **Services** | `@/mocks/*` (sekarang) | Satu-satunya lapisan yang tahu sumber data; nanti swap ke axios/React Query |
| **Mocks** | `@/types/*` | Export data & helper (`getEventDetailBySlug`, dll.); tipe domain di `types/` |
| **Filter/format** | `@/lib/filters/*`, `@/lib/format/*` | Pure functions; boleh dipakai screen & komponen filter modal |

**Jangan** buat barrel `index.ts` untuk re-export — import langsung ke file (konvensi repo).

### Services (`src/services/`)

| File | Fungsi utama |
| --- | --- |
| `events.ts` | `getEventList`, `getUpcomingEvents`, `getEventBySlug` |
| `news.ts` | `getNewsList`, `getLatestNews`, `getNewsBySlug` |
| `branches.ts` | `getBranchList`, `getNearestBranch` |
| `rewards.ts` | `getRewardList`, `getRewardCategories`, `getRewardCatalog`, `getRewardBySku` |

### Types (`src/types/`)

`event.ts`, `news.ts`, `branch.ts`, `reward.ts`, `filter.ts` (`DateRange`, `RewardFilterState`, dll.).

### Mocks (`src/mocks/`)

`mock-events`, `mock-event-details`, `mock-news`, `mock-news-details`, `mock-branches`, `mock-rewards`, `mock-banners`, `mock-member` — 12 item reward, 12 event, 12 berita, dll.

### Root Stack (`src/app/_layout.tsx`)

Screen yang terdaftar: `(tabs)`, `(auth)`, `cms`, `events`, `berita`, `cabang`, `reward`. Splash animasi: `AnimatedSplashOverlay` dari `@/components/shared/animated-icon`.

### Pola UI yang dipakai ulang

- **Detail konten:** `ContentDetailScreen` + `ContentDetailImageSlider` (rasio 1:1) — dipakai event, berita, reward.
- **CTA emas:** `GoldButton` (`@/components/shared/gold-button`) — gradien dari `@/config/brand`.
- **Placeholder:** `ComingSoonScreen` — tab Card/Profile, CMS, fallback detail tidak ditemukan.
- **Filter tanggal:** `DateRangeFilterModal` → state `DateRange` dari `@/lib/date-range-filter`.
- **Layout horizontal:** `SCREEN_HORIZONTAL_PADDING` dari `@/constants/layout/screen-layout`.

### Peta routing aktual

| URL | File | Keterangan |
| --- | --- | --- |
| `/` | `(tabs)/index` | Home member |
| `/card`, `/profile` | `(tabs)/card`, `(tabs)/profile` | Coming soon |
| `/login`, `/register` | `(auth)/login`, `(auth)/register` | Route group — URL tanpa `(auth)` |
| `/events` | `events/index` | List event |
| `/events/[slug]` | `events/[slug]` | Detail event (**bukan** `/event/...`) |
| `/berita`, `/berita/[slug]` | `berita/index`, `berita/[slug]` | |
| `/cabang` | `cabang/index` | |
| `/reward`, `/reward/[sku]` | `reward/index`, `reward/[sku]` | Param detail: `sku` |
| `/cms` | `cms.tsx` | Hub CMS — coming soon |

Shortcut home (`@/config/home-shortcuts.ts`): Event → `/events`, Berita → `/berita`, Cabang → `/cabang`, Reward → `/reward`.

**Path alias (tsconfig):**

- `@/*` → `./src/*`
- `@/assets/*` → `./assets/*`

**Penamaan file:** kebab-case untuk komponen (`event-list-card.tsx`); lowercase untuk route Expo Router (`login.tsx`, `events/[slug].tsx`).

**Typed routes:** `experiments.typedRoutes` aktif — setelah menambah/mindah route, jalankan `npx expo start` agar `.expo/types/router.d.ts` ter-regenerate.

---

## 4. NativeWind + React Native Reusables

### Prinsip styling

- Utamakan `className` pada komponen RN/RNR; hindari `StyleSheet.create` untuk layout baru.
- `StyleSheet` masih boleh untuk kasus khusus: animasi kompleks, `LinearGradient` style prop, atau splash overlay.
- Gabungkan class dengan `cn()` dari `@/lib/utils`.
- Theme semantic: `bg-background`, `text-foreground`, `bg-muted`, `text-muted-foreground`, `border-border`, dll. — didefinisikan di `src/global.css` (stone).

### Menambah komponen RNR

```bash
npx @react-native-reusables/cli@latest add <nama-komponen>
```

Verifikasi setup:

```bash
npx @react-native-reusables/cli@latest doctor
```

### Komponen teks

Gunakan `@/components/ui/text` dengan variant RNR (`h1`, `small`, `muted`, `code`, dll.), bukan `Text` mentah dari `react-native`, kecuali di dalam primitif RNR.

### Root layout wajib

- `import '@/global.css'` di `_layout.tsx`
- `<PortalHost />` sebagai child terakhir provider (dialog, popover, dropdown)
- Color scheme: root layout memanggil `setColorScheme('light')` — selaras dengan §8 (light-only untuk saat ini)

---

## 5. Catatan Expo SDK 56

- **React Compiler** aktif (`experiments.reactCompiler` di `app.json`) — hindari pola yang melanggar aturan compiler.
- **Typed routes** aktif (`experiments.typedRoutes`).
- **Navigation imports:** Jangan import dari `@react-navigation/native` di kode app. Gunakan:
  - `import { ThemeProvider, Stack } from 'expo-router'`
  - `import { DarkTheme, DefaultTheme, type Theme } from 'expo-router/react-navigation'`
- **Dokumentasi migrasi:** https://docs.expo.dev/router/migrate/sdk-55-to-56/
- **Web bundler:** Metro (`app.json` → `web.bundler: "metro"`) — hanya untuk dev; produksi fokus native.

---

## 6. Konvensi Kode

- **Bahasa kode:** TypeScript strict; semua file baru `.tsx` / `.ts`.
- **Komponen:** function component; export named untuk utilitas, default export untuk screen route.
- **Hooks:** prefix `use`, letakkan di `src/hooks/`.
- **API & bisnis logic:** `src/lib/` untuk utilitas murni; `src/services/` untuk akses data (mock → API).
- **Jangan** commit `.env`, secret, atau token; gunakan `EXPO_PUBLIC_*` hanya untuk nilai yang aman diekspos ke client.
- **Lint:** `npm run lint` (Expo ESLint).
- **Scope perubahan:** minimal diff; jangan refactor file tidak terkait task.

---

## 7. Kontrak API (`apps/api-elysia`)

API mobile masih dalam tahap awal. Ikuti konvensi berikut saat mengintegrasikan:

### Environment

```env
# .env.local (jangan di-commit)
EXPO_PUBLIC_API_URL=http://localhost:3000
```

Akses di kode: `process.env.EXPO_PUBLIC_API_URL`

### Integrasi API (cara migrasi)

Jangan ubah import di screen saat API siap — **ganti implementasi di `src/services/*` saja**. Pertahankan signature fungsi facade; tambahkan React Query di dalam service atau hook terpisah jika perlu cache/refetch.

### Auth (target)

- Login member → JWT access token (+ refresh jika disediakan backend).
- Simpan token di `react-native-encrypted-storage`; jangan `AsyncStorage` untuk JWT.
- Axios interceptor: attach `Authorization: Bearer <token>`; pada `401` → clear session & redirect ke `login`.
- Auth gate di root layout: cek session sebelum render `(tabs)`.

### Domain data (referensi schema)

Member, poin, tier (`SILVER` / `GOLD` / `PLATINUM` / `SAPPHIRE`), redeem token & invoice, konten CMS, banner promosi — model ada di `packages/database/schema.prisma`. Koordinasikan shape response API dengan tim backend sebelum mengunci tipe TypeScript di mobile.

### Media

Gambar dinamis (.webp) dari Cloudflare R2 — render dengan `expo-image` + `source={{ uri }}`; URL signed dari API.

---

## 8. Design System — Luxury Aesthetic

Nuansa **emas premium** di atas fondasi **stone** (RNR neutral).

### Warna

| Token | Penggunaan |
| --- | --- |
| Stone palette (RNR) | Background, teks, border — via CSS variables |
| Gold gradient `#f5c842` → `#e8a020` | CTA utama (tombol Masuk, aksi redeem) |
| `expo-linear-gradient` | Implementasi gradien emas pada button/hero |

### Tipografi & spacing

- Gunakan variant RNR (`Text` component) untuk hierarki.
- Spacing mengikuti skala Tailwind (`gap-4`, `p-6`, `rounded-xl`).
- Hindari UI generik “tech startup”; prioritaskan kontras halus, shadow ringan (`shadow-sm shadow-black/5`), dan ruang napas (_breathing room_).

### Dark mode

- Saat ini app **light-only**: `userInterfaceStyle: "light"` di `app.json`; `use-color-scheme.ts` mengembalikan `'light'`.
- Infrastruktur NativeWind `darkMode: 'class'` sudah ada — uji dark mode saat diaktifkan kembali; jangan hardcode hex kecuali aksen emas brand (`@/config/brand`).

---

## 9. Internasionalisasi (i18n)

- **Bahasa UI utama: Bahasa Indonesia** — label, error, empty state, CTA.
- Istilah teknis/domain boleh tetap Inggris jika umum (`OTP`, `tier`, `redeem`) atau disesuaikan glossarium internal.
- Semua string user-facing baru **wajib bahasa Indonesia** kecuali diminta bilingual.
- Belum ada library i18n — saat ini string inline; jika volume teks bertambah, rencanakan `i18next` atau `expo-localization` + file `locales/id.json`.

---

## 10. Perintah Pengembangan

```bash
cd apps/mobile-app
npm install
npm start                    # Expo dev server (+ regenerate typed routes)
npm run ios
npm run android
npm run lint
npx tsc --noEmit             # Typecheck (pakai --ignoreDeprecations 6.0 jika baseUrl warning)
```

Verifikasi navigasi manual setelah ubah route: Home → shortcut → list → detail; login/register; filter modal tiap list.

---

## 11. Checklist Agent Sebelum Selesai

- [ ] Tidak menambah dependensi di luar locked stack
- [ ] Styling baru memakai `className` + RNR/NativeWind
- [ ] Import navigation dari `expo-router`, bukan `@react-navigation/*`
- [ ] String UI dalam Bahasa Indonesia
- [ ] Tidak menyimpan secret di `EXPO_PUBLIC_*`
- [ ] Screen baru mengikuti konvensi import: data dari `@/services/*`, tipe dari `@/types/*`
- [ ] Perubahan route selaras dengan struktur `src/app/` yang ada; pathname event detail = `/events/[slug]`
- [ ] `npx tsc --noEmit` lulus
- [ ] `npx @react-native-reusables/cli@latest doctor` lulus jika menyentuh setup UI
