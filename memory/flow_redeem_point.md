# 📑 DOKUMENTASI TEKNIS: ALUR DATA REDEEM POINT (LOYALTY PROGRAM)

Dokumen ini menjelaskan arsitektur data, transaksional database, dan logika bisnis untuk siklus hidup penukaran poin, yang terbagi menjadi dua fase: **Fase Reservasi (Mobile App API)** dan **Fase Verifikasi/Klaim (Web Admin Filament)**.

---

## 📱 FASE 1: PENERBITAN TOKEN (MEMBER VIA APP MOBILE / ELYSIAJS API)

### 1. Payload Input API

Ketika Member memilih hadiah di aplikasi mobile, API menerima payload:

- `member_id` (String UUID)
- `reward_id` (String UUID)
- `branch_id` (Int)

### 2. Validasi Prakondisi (Pre-checks)

Sebelum membuat token, sistem wajib menjalankan 3 pengecekan berikut:

- **Validasi Akun:** Memastikan `Member.is_suspended == false`.
- **Validasi Saldo Poin:** Memeriksa apakah `Member.point_balance` >= `Reward.points_required`.
- **Validasi Stok Cabang:** Memeriksa apakah stok barang terkait pada tabel inventaris cabang (`RewardBranchStock`) di `branch_id` tersebut tersedia (> 0).

### 3. ACID Database Transaction (Atomic Block)

Jika semua validasi lolos, jalankan transaksi database tunggal untuk menjaga integritas data:

1. **Potong Saldo Member:** Kurangi `Member.point_balance` seharga poin hadiah.
2. **Terbitkan Token (`RedeemToken`):** Simpan baris baru ke tabel `redeem_tokens` dengan aturan:
   - `token_code` = Generate 10 karakter string acak unik (alphanumeric uppercase).
   - `held_points` = Isi dengan nilai poin yang dipotong dari member.
   - `is_used` = `false`.
   - `expired_at` = Tentukan waktu kedaluwarsa (misal: `now() + 3 days`).
   - `branch_id` = Kunci ID cabang tempat pengambilan yang dipilih.

### 4. Output Response

API mengembalikan objek JSON berisi `token_code` dan metadata untuk dirender sebagai QR Code di ponsel milik Member.

---

## 🏬 FASE 2: VERIFIKASI & KLAIM HADIAH (STAF VIA WEB ADMIN FILAMENT)

### 1. Proses Pencarian & Pemindaian Token

Staf Toko (`Staff`) membuka halaman admin, memicu komponen Custom Action `[📥 Verifikasi Token]`, lalu memasukkan/memindai kode kupon 10 digit. Sistem akan melakukan query menggunakan indeks komposit:

```prisma
@@index([tokenCode, isUsed, expiredAt])

```

Kriteria pencarian: `token_code == input`, `is_used == false`, dan `expired_at > now()`. Jika tidak ditemukan, kembalikan error: _"Token tidak valid atau sudah kedaluwarsa!"_.

### 2. Validasi Lokasi & Staf (Cross-Check)

Sistem melakukan pengecekan silang terhadap hak akses dan lokasi fisik:

- **Validasi Cabang:** Memastikan `RedeemToken.branch_id` sama dengan `branch_id` tempat Staf tersebut ditugaskan saat ini. (Mencegah klaim lintas cabang yang tidak sesuai reservasi awal).

### 3. ACID Database Transaction (Eksekusi Akhir)

Ketika Staf menekan tombol konfirmasi penyerahan hadiah, jalankan blok transaksi database (`db.$transaction`):

1. **Ubah Status Token (`RedeemToken`):**
   Update kolom `is_used = true` pada token terkait untuk mencegah penukaran ganda (_anti-double spend_).
2. **Kurangi Stok Fisik Cabang (`RewardBranchStock`):**
   Lakukan operasi _decrement_ (-1) pada jumlah stok hadiah terkait khusus untuk `branch_id` toko tersebut.
3. **Penerbitan Nota Pembukuan (`RedeemInvoice`):**
   Simpan rekaman finansial permanen ke tabel `redeem_invoices` dengan detail:

- `invoice_number` = Generate otomatis dengan format formal (Contoh: `INV-RED/202607/0001`).
- `member_id` = Masukkan dari `RedeemToken.memberId`.
- `staff_id` = Tangkap ID Staf yang sedang login dari konteks session (`auth()->user()->staff->id`).
- `branch_id` = Ambil dari lokasi toko saat ini.
- `reward_id` = Ambil dari tipe hadiah yang ditukar.
- `points_redeemed` = Salin nilai dari `RedeemToken.held_points`.
- `status` = Set default menjadi `COMPLETED`.

### 4. Output UI & Logistik

Filament memberikan respon pop-up sukses (_Toast Notification_). Sistem mengizinkan Staf toko secara fisik menyerahkan barang hadiah ke tangan pelanggan. Transaksi selesai dan terkunci secara permanen (Read-Only) untuk kebutuhan audit akuntansi kantor pusat.

---

## 🛠️ ROADMAP FASE DEVELOPMENT (WEB ADMIN FILAMENT)

Berikut urutan fase pengembangan agar implementasi aman, terukur, dan mudah direview.

### Fase 0 — Fondasi Data & Kontrak Domain

Tujuan: memastikan model data dan aturan inti siap dipakai oleh UI admin.

- Validasi ulang struktur model: `RedeemToken`, `RedeemInvoice`, `RewardBranchStock`, `Staff`, `Branch`.
- Pastikan index query verifikasi token tersedia (`token_code`, `is_used`, `expired_at`).
- Definisikan kontrak error domain (contoh: token invalid, expired, used, branch mismatch, stock habis).
- Tetapkan format nomor invoice final (misal `INV-RED/YYYYMM/NNNN`).

### Fase 1 — Resource Read-Only Redeem Invoice (Audit Layer)

Tujuan: menyediakan observability dan audit trail di Filament sebelum fitur klaim aktif.

- Bangun `RedeemInvoiceResource` (index + view).
- Tabel index menampilkan histori klaim (invoice, member, reward, branch, staff, poin, waktu).
- Halaman view menampilkan detail member, info cabang/staff, dan detail reward.
- Tambahkan metrik dashboard (redeem 30 hari, sukses hari ini, chart per cabang, top reward).
- Semua aksi mutasi data pada fase ini nonaktif/read-only.

### Fase 2 — UI Verifikasi Token di Admin

Tujuan: staf bisa memverifikasi token dari admin dengan feedback jelas.

- Tambahkan action `[Verifikasi Token]` pada halaman list yang relevan.
- Form input/scanner kode token 10 karakter.
- Tampilkan ringkasan token saat valid (member, reward, branch pickup, expiry, poin ditahan).
- Tampilkan pesan error spesifik untuk token tidak valid/sudah dipakai/kedaluwarsa.

### Fase 3 — Service Klaim Terpusat (Business Transaction Layer)

Tujuan: memindahkan logika sensitif ke service class terisolasi, bukan di UI.

- Buat service aplikasi, contoh: `RedeemClaimService`.
- Jalankan seluruh proses klaim dalam satu transaksi database:
  1. set `RedeemToken.is_used = true`
  2. decrement `RewardBranchStock.actual_stock`
  3. create `RedeemInvoice`
- Lindungi dari race condition/double submit (lock row/token check).
- Kembalikan hasil terstruktur untuk dipakai oleh Action Filament.

### Fase 4 — Integrasi Action Filament ↔ Service

Tujuan: menghubungkan action UI ke service transaksi final dengan UX yang aman.

- Tombol konfirmasi klaim memanggil `RedeemClaimService`.
- Gunakan confirmation modal sebelum eksekusi final.
- Tampilkan toast sukses/gagal yang informatif.
- Setelah sukses, redirect/refresh ke detail invoice hasil klaim.

### Fase 5 — Otorisasi, Keamanan, dan Guardrail

Tujuan: mencegah klaim ilegal dan menjaga integritas operasional cabang.

- Policy/Gate untuk membatasi siapa yang boleh klaim.
- Validasi branch scope: staff hanya bisa klaim token cabangnya.
- Hardening terhadap replay/double click dan token reuse.
- Logging aktivitas admin untuk audit.

### Fase 6 — Refund / Cancel Darurat (Opsional Lanjutan)

Tujuan: menyediakan jalur koreksi operasional dengan kontrol ketat.

- Tambahkan alur perubahan status invoice `COMPLETED -> REFUNDED`.
- Transaksi refund: kembalikan poin member + rollback stock cabang.
- Batasi role yang boleh refund (mis. Store Manager/Superadmin).
- Simpan alasan refund wajib dan jejak audit lengkap.

### Fase 7 — Testing, Monitoring, dan UAT

Tujuan: memastikan reliabilitas sebelum go-live.

- Unit test untuk service klaim (happy path + semua failure path).
- Feature test action Filament (valid token, expired, used, branch mismatch, stock habis).
- Uji concurrency sederhana untuk mencegah double claim.
- UAT checklist bersama user operasional toko.

### Definisi Selesai (Definition of Done)

Fitur klaim dianggap siap produksi jika:

- semua transaksi inti berjalan atomik dan idempotent,
- tidak ada jalur double spend token,
- audit trail invoice lengkap dan read-only,
- hak akses per role dan per cabang tervalidasi,
- seluruh test kritis lulus.
