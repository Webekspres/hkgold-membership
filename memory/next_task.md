# Next Task — Fitur Belum Dikerjakan

Sumber: sync AGENTS (mobile / api / backoffice) + audit gap 2026-07-22.
Status produk terkini: lihat `apps/mobile-app/AGENTS.md` §1.

---

## Sudah selesai (jangan ulangi)

- Auth login/register, ganti password, lupa password (OTP WA)
- Profile read + detail/edit + avatar (WebP `member/photo`) + alamat cascade + gender
- Tier benefit hero UI + `GET /api/tier/levels` (benefits CMS)
- Home wallet, banner, berita/event, katalog reward home
- List/detail berita, event, cabang, reward (stok filter)
- Redeem full cycle + QR + cancel + FCM wire (post-confirm)
- Suspended UX mobile (banner + kunci Tukarkan)
- FAQ CMS + API + screen `/faq`
- Riwayat poin: `GET /api/point-ledger` + screen `/point-ledger`
- Ganti nomor HP dual path (self-service OTP + admin-assisted PENDING)
- Filament Persetujuan Ganti Nomor + internal approve/reject API
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

---

## P2 — Produk setengah jalan

### 2. CMS hub `/cms`

- **Status:** `ComingSoonScreen`
- **Kerja:** hub navigasi konten (berita/event/banner) atau hapus rute sampai siap

### 3. Bersihkan mock / file mati

- `mock-tier-benefits.ts` — screen sudah API (hapus atau tinggal fixture test)
- `components/dev/dev-tier-switcher.tsx` — tidak lagi dipakai di home (hapus)
- Helper reward list mock di `rewards.ts` jika masih ada dan tidak dipakai
- Pastikan screen tidak reintroduksi mock di area yang sudah API

### 4. QA FCM development build

- Wire sudah ada; butuh QA di **dev build** (bukan Expo Go) + `google-services.json` / `GoogleService-Info.plist` lokal
- Lihat juga `memory/notif_setup_check.md`

---

## P3 — Roadmap panjang

### 5. Inbox notifikasi in-app (mobile)

- Filament: kampanye + inbox page maju
- Mobile: inbox belum; roadmap detail di `memory/notification_memo.md`

### 6. Kategori berita

- `Content` NEWS belum punya category; detail tanpa kategori

### 7. FraudSuspect admin

- Model ada; belum ada Filament Resource di `Resources/`

### 8. Demo mode tamu

- Target jangka panjang di AGENTS; belum di kode

---

## Urutan saran eksekusi

1. Cabang terdekat (schema geo → API → mobile)
2. CMS hub atau hapus rute placeholder
3. Cleanup mock / dev-tier-switcher mati
4. QA FCM dev build
5. Notifikasi inbox / kategori berita / fraud admin (sesuai prioritas bisnis)

---

## Catatan agent

- Sebelum kerjakan item di atas: baca AGENTS app terkait (`apps/mobile-app`, `apps/api-elysia`, `apps/backoffice-filament`).
- Setelah selesai satu item: update status di `apps/mobile-app/AGENTS.md` §1 dan centang/ubah baris di file ini.
