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

---

## 1. Kondisi Saat Ini vs Target

### Sudah ada di repo

- **Expo SDK 56** — React 19, React Native 0.85, Expo Router file-based routing
- **NativeWind v4** + Tailwind CSS v3, theme **stone**, `darkMode: 'class'`
- **React Native Reusables (RNR)** — style `new-york`, komponen UI: `button`, `text`, `card`, `input`
- **Root layout** — `GestureHandlerRootView`, `PortalHost`, sinkronisasi dark mode via `nativewind`
- **Halaman:** `login.tsx` (UI masuk, belum terhubung API), `(tabs)/index` & `(tabs)/explore` (starter)
- **Native tabs** — `expo-router/unstable-native-tabs` di `src/components/app-tabs.tsx`
- **Aset brand** — `assets/logo/logo-hkgold.webp`, `assets/media/background.webp`
- **EAS project** — `projectId` di `app.json`

### Target arsitektur (belum diimplementasi penuh)

Fitur member yang akan datang: auth gate, dasbor saldo poin & tier, ledger mutasi, katalog redeem, profil & keamanan, pelacakan redeem OTP, CMS konten, kalender pameran, proteksi suspended member, dan demo mode tamu. Rute akan ditambahkan secara bertahap di bawah `src/app/` mengikuti pola grup Expo Router (`(auth)`, `(tabs)`, `(tracking)`, dll.) tanpa mengubah fondasi stack yang sudah dikunci.

---

## 2. Locked Tech Stack

Anda **wajib** mematuhi stack berikut. Jangan mengganti dengan alternatif tanpa persetujuan eksplisit.

### Core (terpasang)

| Kategori | Pustaka | Catatan |
| --- | --- | --- |
| Framework | Expo SDK 56 + Expo Router | Entry: `expo-router/entry`, root di `src/app/` |
| Styling | NativeWind v4 + Tailwind v3 | Utility via `className`; config di `tailwind.config.js` |
| UI Components | React Native Reusables | `@rn-primitives/*`, tambah komponen via CLI |
| Animasi | react-native-reanimated v4 | Wajib untuk transisi premium |
| Gesture | react-native-gesture-handler | Root wrap di `_layout.tsx` |
| Gambar lokal & remote | **expo-image** | Caching & `.webp`; **jangan** pakai `react-native-fast-image` |
| Gradasi | expo-linear-gradient | Aksen emas pada CTA (lihat Design System) |
| Device ID | **expo-device** + **expo-application** | Hardware fingerprint untuk Marketing Protection Engine |
| Safe area | react-native-safe-area-context | Sudah terpasang |

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
    │   ├── login.tsx            # Halaman masuk member (UI siap, API belum)
    │   └── (tabs)/
    │       ├── _layout.tsx      # Native tabs wrapper
    │       ├── index.tsx        # Home (sementara: starter → nanti dasbor)
    │       └── explore.tsx      # Explore (sementara: starter → nanti diganti)
    ├── components/
    │   ├── ui/                  # Komponen RNR (button, text, card, input, …)
    │   ├── app-tabs.tsx         # NativeTabs (iOS/Android)
    │   └── app-tabs.web.tsx     # Fallback web dev only
    ├── hooks/
    ├── lib/
    │   ├── utils.ts             # cn() helper
    │   └── theme.ts             # THEME + NAV_THEME (stone)
    ├── constants/
    └── assets/                  # Alias @/assets/* di tsconfig
        ├── logo/
        └── media/
```

**Path alias (tsconfig):**

- `@/*` → `./src/*`
- `@/assets/*` → `./assets/*`

**Penamaan file:** kebab-case untuk komponen (`hint-row.tsx`), lowercase untuk route Expo Router (`login.tsx`, `(tabs)/index.tsx`).

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
- Sync color scheme: `useColorScheme` dari `nativewind` + `react-native`

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
- **API & bisnis logic:** `src/lib/` atau `src/services/` (buat folder `services/` saat axios layer ditambahkan).
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

- Ikuti `userInterfaceStyle: "automatic"` di `app.json`.
- Uji setiap screen di light & dark; jangan hardcode warna hex kecuali aksen emas brand.

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
npm start          # Expo dev server
npm run ios
npm run android
npm run lint
```

Branch Git terkait mobile: `mobile` di monorepo `hkgold-membership`.

---

## 11. Checklist Agent Sebelum Selesai

- [ ] Tidak menambah dependensi di luar locked stack
- [ ] Styling baru memakai `className` + RNR/NativeWind
- [ ] Import navigation dari `expo-router`, bukan `@react-navigation/*`
- [ ] String UI dalam Bahasa Indonesia
- [ ] Tidak menyimpan secret di `EXPO_PUBLIC_*`
- [ ] Perubahan route selaras dengan struktur `src/app/` yang ada
- [ ] `npx @react-native-reusables/cli@latest doctor` lulus jika menyentuh setup UI
