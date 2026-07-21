# Next Task — Fitur Belum Dikerjakan

Sumber: sync AGENTS (mobile / api / backoffice) + audit gap 2026-07-21.
Status produk terkini: lihat `apps/mobile-app/AGENTS.md` §1.

---

## Sudah selesai (jangan ulangi)

- Auth login/register, ganti password
- Profile read + detail/edit + avatar (WebP `member/photo`) + alamat cascade + gender
- Tier benefit hero UI + `GET /api/tier/levels` (benefits CMS)
- Home wallet, banner, berita/event, katalog reward home
- List/detail berita, event, cabang, reward (stok filter)
- Redeem full cycle + QR + cancel + FCM wire (post-confirm)
- AGENTS.md monorepo sudah di-sync

---

## P1 — Sisa nyata di app (kerjakan dulu)

### 1. Cabang terdekat (home)

- **Status:** mobile masih mock (`getNearestBranch`)
- **Gap:** `Branch` belum lat/lng; API nearest belum ada
- **Kerja:**
  - Migrasi + Filament: kolom geo cabang
  - API: endpoint nearest (atau query sorted by distance)
  - Mobile: unmock home section + wire React Query

### 2. Suspended UX (mobile)

- **Status:** API redeem sudah block `is_suspended`; UI belum
- **Kerja:**
  - Lock tombol redeem / pesan jelas di reward + card
  - Opsional banner/status di profile/home
  - Tetap boleh baca konten/cabang/tier (kontrak dual-layer di api AGENTS §6)

### 3. FAQ

- **Status:** screen `/faq` mock; belum CMS/API
- **Keputusan:** buat CMS+API+wire **atau** drop/hide screen mock
- **Jika buat:** model/content FAQ → Filament → `GET` list → unmock mobile

---

## P2 — Produk setengah jalan

### 4. Ledger mutasi poin (mobile)

- **Status:** schema + Filament `PointMutations` ada; **modul API mobile belum**; screen app belum
- **Kerja:** `GET` mutasi poin (real-time, no Redis cache — lihat api AGENTS §4) + screen riwayat di app

### 5. CMS hub `/cms`

- **Status:** `ComingSoonScreen`
- **Kerja:** hub navigasi konten (berita/event/banner) atau hapus rute sampai siap

### 6. Bersihkan mock mati

- `mock-tier-benefits.ts` — screen sudah API (hapus atau tinggal fixture test)
- Helper reward list mock di `rewards.ts` jika masih ada dan tidak dipakai
- Pastikan screen tidak reintroduksi mock di area yang sudah API

### 7. QA FCM development build

- Wire sudah ada; butuh QA di **dev build** (bukan Expo Go) + `google-services.json` / `GoogleService-Info.plist` lokal
- Lihat juga `memory/notif_setup_check.md`

---

## P3 — Roadmap panjang

### 8. Inbox notifikasi in-app (mobile)

- Filament: kampanye + inbox page maju
- Mobile: inbox belum; roadmap detail di `memory/notification_memo.md`

### 9. Kategori berita

- `Content` NEWS belum punya category; detail tanpa kategori

### 10. Ganti nomor HP

- Schema / `ChangePhoneApproval` ada
- Belum: Filament resource penuh + API + flow mobile

### 11. FraudSuspect admin

- Model ada; belum ada Filament Resource di `Resources/`

### 12. Demo mode tamu

- Target jangka panjang di AGENTS; belum di kode

---

## Urutan saran eksekusi

1. Cabang terdekat (schema geo → API → mobile)
2. Suspended UX mobile
3. FAQ (keputusan: build vs drop)
4. Ledger poin API + screen
5. Cleanup mock + CMS hub
6. Notifikasi inbox / kategori berita / ganti HP / fraud admin (sesuai prioritas bisnis)

---

## Catatan agent

- Sebelum kerjakan item di atas: baca AGENTS app terkait (`apps/mobile-app`, `apps/api-elysia`, `apps/backoffice-filament`).
- Setelah selesai satu item: update status di `apps/mobile-app/AGENTS.md` §1 dan centang/ubah baris di file ini.
