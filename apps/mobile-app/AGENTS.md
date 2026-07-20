# AI Agent Development Directives: HK GOLD VIP Mobile Client

Anda adalah **Expert React Native & TypeScript Developer** yang membangun aplikasi seluler berperforma tinggi, aman, dan bernuansa premium (_luxury aesthetic_).

## Agent Tooling (Cursor)

Wajib memakai keempat tools berikut di setiap sesi Cursor:

- **graphify** — sebelum pertanyaan arsitektur/alur: `graphify query "..."`, `graphify path "A" "B"`, atau `graphify explain "..."` bila `graphify-out/graph.json` ada. Setelah ubah kode: `graphify update .` (AST-only).
- **rtk** — prefix CLI verbose: `rtk git …`, `rtk rg …`, `rtk npm …`, `rtk npx …`. Jika gagal, fallback perintah biasa.
- **ponytail** — ladder YAGNI / reuse / min diff (root `AGENTS.md` + `.cursor/rules/ponytail.mdc`).
- **caveman** — jawaban ringkas (full; Bahasa Indonesia). Code fence, error, path, CLI: byte-exact. String UI app tetap Bahasa Indonesia penuh (bukan caveman).

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

**Branch Git aktif:** biasanya `dev` (atau `mobile`) di monorepo `hkgold-membership` (`apps/mobile-app/`).

**Spesifikasi redeem:** `memory/dev_phase_redeem.md` + `memory/flow_redeem_point.md` (root monorepo) — sumber alur reserve / OTP / invoice / FCM.

---

## 1. Status Proyek & Roadmap

### Fase saat ini

Struktur `src/` stabil. **Auth + konten + redeem + registrasi FCM token** sudah wired ke `apps/api-elysia` via `axios` + React Query. Sisa mock: home cabang terdekat, home katalog reward.

| Area | Status | Catatan |
| --- | --- | --- |
| Struktur `src/` | ✅ | Lihat §3 |
| Auth login/register | ✅ API | JWT + secure store; gate di `_layout.tsx` |
| Profile | ✅ API | `useMyProfile` / `services/member.ts` |
| Home wallet | ✅ API | dari profile; pattern BG light |
| Home banner | ✅ API | `usePromotionBanners` (stale 4 jam); hide jika kosong/`error` |
| Home berita / event | ✅ API | limit 5 / 3; `staleTime` 15 menit |
| Home cabang terdekat | 🟡 mock | API belum punya lat/lng / nearest |
| Home katalog reward | 🟡 mock | list tab Reward sudah API |
| List + detail Berita | ✅ API | search debounce + date filter; detail `/berita/[id]` (UUID) |
| List + detail Event | ✅ API | sama; detail `/events/[id]`; lokasi/maps jika field terisi |
| List Cabang | ✅ API | `q` + filter kota; infinite scroll |
| List + detail Reward | ✅ API | search + filter + sort; list/catalog/home dari API **tanpa** reward stok 0; detail tampil cabang available > 0 saja (deep link stok habis → info reward + pesan stok habis) |
| Redeem (reserve / active / history / detail / cancel) | ✅ API | `/redeem`, `/redeem/[id]`; QR redeem di `/card/redeem-qr` (plain `tokenCode` untuk scan kasir Filament); cancel + pull-refresh; profil highlight pending/selesai |
| Push FCM (post-confirm invoice) | ✅ wire | Register token + deep link; **bukan** Expo Go — lihat §5 |
| Tab Card | ✅ API | `(tabs)/card` — kartu member + QR; `/card/redeem-qr` saat ada reservasi aktif |
| CMS hub `/cms` | 🔲 | `ComingSoonScreen` |

### Gap schema / kontrak (jangan asumsikan field ada)

| Kebutuhan UI | Status DB / API | Workaround sekarang |
| --- | --- | --- |
| Banner tap URL | ✅ `PromotionBanner.linkUrl` | Slider buka URL jika terisi |
| Urutan banner CMS | ✅ `PromotionBanner.sortOrder` | Order `sortOrder asc` |
| Lokasi event + CTA maps | ✅ `Content.locationAddress` + `locationUrl` | Detail event tampil alamat + tombol maps |
| Cabang terdekat | `Branch` **tanpa** lat/lng | Home nearest tetap mock |
| Kategori berita | `Content` **tanpa** category | Detail berita tanpa kategori |
| `Member.birthDate` | ✅ ada di schema + migrasi | Profile tampil jika tidak null |

### Langkah berikutnya (prioritas wajar)

1. Wire home nearest branch + home reward catalog (butuh keputusan API / tetap mock).
2. CMS hub.
3. QA FCM di **development build** (bukan Expo Go) dengan `google-services.json` / `GoogleService-Info.plist` lokal.
4. (Opsional) lat/lng cabang untuk nearest; proteksi suspended di UI; ledger mutasi poin di app.

### Target jangka panjang (belum / parsial)

Proteksi suspended di UI, demo mode tamu, in-app notification center. Rute baru ikut pola grup Expo Router tanpa ubah fondasi stack §2.

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
| Push (FCM/APNs) | **expo-notifications** | Token device + listener; **hanya** di luar Expo Go (lihat §5) |

### Server state & HTTP (sudah terpasang)

| Kategori | Pustaka | Digunakan untuk |
| --- | --- | --- |
| Server state | **@tanstack/react-query** | List infinite, home cache, profile |
| HTTP | **axios** | `src/lib/api-client.ts` + interceptor JWT |
| Secure storage | **expo-secure-store** | JWT |
| QR digital card | **react-native-qrcode-svg** | Kartu member (`MemberQrCard`) + layar redeem (`QrCodeCard` di `/card/redeem-qr`) |

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
    │   │   ├── card/
    │   │   │   ├── index.tsx        # Kartu member
    │   │   │   └── redeem-qr.tsx    # QR redeem aktif
    │   │   └── profile.tsx
    │   ├── events/
    │   │   ├── index.tsx        # List event
    │   │   └── [id].tsx         # Detail event (UUID)
    │   ├── berita/
    │   │   ├── index.tsx
    │   │   └── [id].tsx         # Detail berita (UUID)
    │   ├── cabang/
    │   │   └── index.tsx
    │   ├── reward/
    │   │   ├── index.tsx
    │   │   └── [sku].tsx
    │   ├── redeem/
    │   │   ├── index.tsx        # Active / history redeem
    │   │   └── [id].tsx         # Detail invoice (UUID)
    │   └── cms.tsx
    ├── components/
    │   ├── ui/                  # Komponen RNR (button, text, card, input, …)
    │   ├── auth/                # Shell & field auth
    │   ├── shared/              # Cross-feature (gold-button, app-tabs, search-input, content-detail-*)
    │   ├── home/                # Section homepage
    │   ├── event/
    │   ├── berita/
    │   ├── reward/
    │   └── branch/
    ├── config/                  # brand, home-shortcuts, theme (Colors/Fonts starter)
    ├── constants/
    │   └── layout/              # grid, carousel, screen-layout tokens
    ├── mocks/                   # Fixture sisa (home nearest/catalog, dll.)
    ├── types/                   # Shared domain types
    ├── services/                # Facade HTTP (auth, redeem, device-push, …)
    ├── hooks/                   # React Query + use-register-push-token
    ├── lib/
    │   ├── api-client.ts        # axios + JWT interceptor
    │   ├── filters/             # filter-events, filter-news, filter-rewards, …
    │   ├── format/              # format-event-date, format-news-date, …
    │   ├── notifications/       # handle-redeem-push (deep link payload FCM)
    │   ├── utils.ts             # cn() helper
    │   ├── theme.ts             # THEME + NAV_THEME (stone)
    │   ├── date-range-filter.ts
    │   └── open-location-url.ts

assets/                            # Di root proyek (bukan di src/)
├── logo/logo-hkgold.webp
├── media/background.webp
├── media/pattern-horizontal.webp  # profile tier card
├── media/pattern-vertical.webp    # auth + tier benefit
└── mockImage/                     # Gambar fixture list/detail
```

### Konvensi import data

| Lapisan | Import dari | Catatan |
| --- | --- | --- |
| **Screen** (`src/app/`) | `@/hooks/*` dan/atau `@/services/*` | Jangan import `@/mocks/*` di route kecuali sisa mock (home nearest / reward catalog) |
| **Komponen** | props dari parent | Import `@/types/*` untuk tipe props; jangan panggil service di komponen presentasional |
| **Hooks** | `@/services/*` | React Query (`useQuery` / `useInfiniteQuery`); `staleTime` sesuai domain |
| **Services** | `apiClient` atau `@/mocks/*` | Facade HTTP; mock hanya untuk yang belum API |
| **Mocks** | `@/types/*` | Fixture sisa; tipe domain di `types/` |
| **Filter/format** | `@/lib/filters/*`, `@/lib/format/*` | Pure functions; boleh dipakai screen & komponen filter modal |

**Jangan** buat barrel `index.ts` untuk re-export — import langsung ke file (konvensi repo).

### Services (`src/services/`)

| File | Fungsi utama |
| --- | --- |
| `auth.ts` | login/register/logout + token storage |
| `member.ts` | profile member |
| `content.ts` | shared `fetchContentPage` / detail content |
| `events.ts` | list/upcoming/detail EVENT via content API |
| `news.ts` | list/latest/detail NEWS via content API |
| `banners.ts` | `fetchActivePromotionBanners` |
| `branches.ts` | list + cities + `getNearestBranch` (masih mock) |
| `rewards.ts` | catalog page (sort/filter), categories, detail by sku; catalog home mock |

### Types (`src/types/`)

`event.ts`, `news.ts`, `branch.ts`, `reward.ts`, `banner.ts`, `member.ts`, `auth.ts`, `filter.ts` (`DateRange`, `RewardFilterState` + `sortBy`/`sortOrder`, dll.).

### Mocks (`src/mocks/`)

Sisa: nearest branch, reward catalog home, redeem history, member fallback bila perlu. List berita/event/reward/banner **jangan** di-mock lagi di screen yang sudah API.

### Root Stack (`src/app/_layout.tsx`)

Screen yang terdaftar: `(tabs)`, `(auth)`, `cms`, `events`, `berita`, `cabang`, `reward`. Splash animasi: `AnimatedSplashOverlay` dari `@/components/shared/animated-icon`.

### Pola UI yang dipakai ulang

- **Detail konten:** `ContentDetailScreen` + `ContentDetailImageSlider` (rasio 1:1) — dipakai event, berita, reward.
- **CTA emas:** `GoldButton` (`@/components/shared/gold-button`) — gradien dari `@/config/brand`.
- **Placeholder:** `ComingSoonScreen` — CMS, fallback detail tidak ditemukan.
- **Filter tanggal:** `DateRangeFilterModal` → state `DateRange` dari `@/lib/date-range-filter`.
- **Search list:** `SearchInput` + debounce 500ms; kirim `q` hanya jika panjang **> 2**.
- **Layout horizontal:** `SCREEN_HORIZONTAL_PADDING` dari `@/constants/layout/screen-layout`.

### Peta routing aktual

| URL | File | Keterangan |
| --- | --- | --- |
| `/` | `(tabs)/index` | Home member |
| `/card` | `(tabs)/card` | Kartu member + QR |
| `/card/redeem-qr` | `(tabs)/card/redeem-qr` | QR redeem aktif (`tokenCode` 10 char — discan kasir di backoffice) |
| `/profile` | `(tabs)/profile` | Profile + tier (API) |
| `/login`, `/register` | `(auth)/login`, `(auth)/register` | Route group — URL tanpa `(auth)` |
| `/events` | `events/index` | List event |
| `/events/[id]` | `events/[id]` | Detail event (UUID; **bukan** `/event/...`) |
| `/berita`, `/berita/[id]` | `berita/index`, `berita/[id]` | Detail berita (UUID) |
| `/cabang` | `cabang/index` | |
| `/reward`, `/reward/[sku]` | `reward/index` (atau tab), `reward/[sku]` | Param detail: `sku` |
| `/cms` | `cms.tsx` | Hub CMS — coming soon |

Shortcut home (`@/config/home-shortcuts.ts`): Event → `/events`, Berita → `/berita`, Cabang → `/cabang`, Reward → `/reward`.

**Path alias (tsconfig):**

- `@/*` → `./src/*`
- `@/assets/*` → `./assets/*`

**Penamaan file:** kebab-case untuk komponen (`event-list-card.tsx`); lowercase untuk route Expo Router (`login.tsx`, `events/[id].tsx`).

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
- **Push / `expo-notifications` (wajib):**
  - Remote push **tidak didukung di Expo Go** (Android SDK 53+): import barrel `expo-notifications` bisa **throw** (side-effect auto-registration).
  - Di Expo Go: **jangan** static-import `expo-notifications`. Guard dengan `isRunningInExpoGo()`; load dinamis hanya di luar Expo Go (`device-push.ts`, `use-register-push-token.ts`).
  - Uji FCM nyata: **development build** / EAS (`npx expo run:android` / `run:ios`), bukan Expo Go.
  - Kredensial client: `google-services.json` (Android) + `GoogleService-Info.plist` (iOS) — **project Firebase yang sama** dengan FCM Filament. **Bukan** Service Account JSON string di env mobile.
  - File kredensial client di-gitignore; jangan commit.

---

## 6. Konvensi Kode

- **Bahasa kode:** TypeScript strict; semua file baru `.tsx` / `.ts`.
- **Komponen:** function component; export named untuk utilitas, default export untuk screen route.
- **Hooks:** prefix `use`, letakkan di `src/hooks/`.
- **API & bisnis logic:** `src/lib/` untuk utilitas murni; `src/services/` untuk akses data (mock → API).
- **Jangan** commit secret/token; env dari Doppler (`hkgoldvip` / `dev_mobile`). Template: `.env.example`. `EXPO_PUBLIC_*` hanya untuk nilai aman di client.
- **Lint:** `npm run lint` (Expo ESLint).
- **Scope perubahan:** minimal diff; jangan refactor file tidak terkait task.

---

## 7. Kontrak API (`apps/api-elysia`)

API mobile masih dalam tahap awal. Ikuti konvensi berikut saat mengintegrasikan:

### Environment

Sumber: Doppler project `hkgoldvip`, config `dev_mobile` (`doppler.yaml`). Script `npm start` / `android` / `ios` / `web` memakai `doppler run`.

```env
# .env.example (template saja — secret di Doppler)
EXPO_PUBLIC_API_URL=http://192.168.0.193:3000
```

Akses di kode: `process.env.EXPO_PUBLIC_API_URL`

### Integrasi API (cara migrasi)

Jangan ubah import di screen saat API siap — **ganti implementasi di `src/services/*` saja**. Pertahankan signature fungsi facade; tambahkan React Query di dalam service atau hook terpisah jika perlu cache/refetch.

### Auth (sudah)

- Login member → JWT; simpan di secure store (jangan `AsyncStorage` untuk JWT).
- Axios interceptor: `Authorization: Bearer <token>`; `401` → clear session & redirect login.
- Auth gate di root `_layout.tsx`.

### Endpoint yang dipakai (ringkas)

| Domain | Endpoint |
| --- | --- |
| Auth | `POST /api/auth/login`, register, dll. |
| Profile | `GET /api/member/me` (atau setara di member routes) |
| Konten | `GET /api/content?type=NEWS\|EVENT` (`q`, `dateFrom`, `dateTo`, cursor); `GET /api/content/:id` |
| Banner | `GET /api/promotion-banner` |
| Cabang | `GET /api/branch`, `GET /api/branch/cities` |
| Reward | `GET /api/reward` (`search`, filter, `sortBy`/`sortOrder`; hanya in-stock), `GET /api/reward/categories`, `GET /api/reward/:sku` (detail boleh stok habis; `branchStocks` filtered server-side, mobile guard `filterAvailableBranchStocks`) |
| Redeem | `POST /api/redeem/token`, `GET /api/redeem/active`, `POST /api/redeem/cancel`, `GET /api/redeem/token/:id/status`, history |
| Device push | `POST /api/device/push-token`, `DELETE /api/device/push-token` (JWT member) |

### Push FCM (post-confirm redeem)

Setelah kasir konfirmasi OTP di Filament (scan/ketik token di wizard Antrean Kupon), member dapat push `MobileAppPush` (fail-soft di server). Payload data (string): `type=redeem_invoice`, `invoiceId=<uuid>` → navigasi `/redeem/[id]`.

| File | Peran |
| --- | --- |
| `src/services/device-push.ts` | Ambil FCM/APNs token → `POST /api/device/push-token`; revoke saat logout |
| `src/hooks/use-register-push-token.ts` | Register saat login + listener tap notifikasi |
| `src/lib/notifications/handle-redeem-push.ts` | Parse payload → route Expo Router |

Invalidasi React Query `active-redeem` + `redeem-history` saat tap push / setelah navigate.

### Domain data (referensi schema)

Member, poin, tier (`SILVER` / `GOLD` / `PLATINUM` / `ELITE`), redeem token & invoice, konten CMS, banner promosi — model di `packages/database/schema.prisma`. Lihat gap schema di §1 sebelum menambah field UI.

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
# sekali per mesin: doppler login + doppler setup --no-interactive
# WAJIB pakai npm start (doppler run) — jangan `npx expo start` tanpa Doppler
npm start                    # Expo via doppler run (+ regenerate typed routes)
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
- [ ] Perubahan route selaras dengan struktur `src/app/` yang ada; detail konten = `/events/[id]`, `/berita/[id]`
- [ ] Screen list pakai hook React Query + service; jangan reintroduksi mock di area yang sudah API
- [ ] `npx tsc --noEmit` lulus
- [ ] `npx @react-native-reusables/cli@latest doctor` lulus jika menyentuh setup UI
