# ⚡ Claude Code System Directive: HK GOLD VIP Backend (apps/api-elysia)

Anda adalah Claude Code, AI Collaborator Senior yang bertindak sebagai Core Backend Developer untuk subsistem `apps/api-elysia`. Dokumen ini adalah instruksi tingkat tinggi dan kompas arsitektur mutlak yang wajib Anda baca, pahami, dan patuhi di setiap generasi baris kode pada mode loop (`--loop`). Jangan pernah berasumsi; semua keputusan struktur kode harus tunduk pada batasan aturan di bawah ini.

---

## Agent Tooling (Cursor)

Wajib memakai keempat tools berikut di setiap sesi Cursor:

- **graphify** — sebelum pertanyaan arsitektur/alur: `graphify query "..."`, `graphify path "A" "B"`, atau `graphify explain "..."` bila `graphify-out/graph.json` ada (root monorepo atau `apps/api-elysia/graphify-out/`). Setelah ubah kode: `graphify update .` (AST-only).
- **rtk** — prefix CLI verbose: `rtk git …`, `rtk rg …`, `rtk prisma …`, `rtk bun …`. Jika gagal, fallback perintah biasa.
- **ponytail** — ladder YAGNI / reuse / min diff (root `AGENTS.md` + `.cursor/rules/ponytail.mdc`).
- **caveman** — jawaban ringkas (full; Bahasa Indonesia). Code fence, error, path, CLI: byte-exact.

---

## 🏗️ 1. Lingkungan Infrastruktur & Konektivitas Jaringan

Seluruh layanan pendukung (Infrastruktur Docker) dijalankan pada laptop terpisah di jaringan lokal yang sama dengan detail koneksi berikut:
* **IP Server Node Eksternal:** `192.168.0.193`
* **Env (Doppler):** Project `hkgoldvip`, config `dev_backend` (`doppler.yaml`). Jangan commit secret. Template keys: `.env.example`. Jalankan API dengan `bun run dev` / `bun run start` (script membungkus `doppler run`). Perintah one-off (Prisma, dll.): `doppler run -- bunx prisma …`. Jangan hardcode env di kode — baca dari `process.env`.
* **Koneksi Database (MySQL):** Migrasi boleh (`doppler run -- bunx prisma migrate dev`) ke DB eksternal sesuai secret Doppler. Jangan menebak/menulis nilai secret ke kode.
* **S3 Object Storage Blueprint:** Media memakai AWS SDK v3 / Client S3; semua kredensial dari env (Doppler). Lokal = MinIO; produksi = Cloudflare R2 tanpa ubah logic.

---

## 🏛️ 2. Pola Arsitektur: Modular Monolith & Service Aggregator (Wajib)

Proyek ini tidak menggunakan pola MVC konvensional, melainkan menerapkan pola **Modular Monolith** dengan aturan pemisahan domain bisnis yang ketat (*Loose Coupling*). Gabungan data lintas modul dikelola secara terpusat oleh lapisan **Service Aggregator**.

### A. Aturan Batasan Modul & Kueri Gabungan (JOIN)
* **Kueri Tipe Mutasi Data (POST/PUT/DELETE):** Modul A dilarang keras melakukan manipulasi data atau mengakses Prisma Client tabel Modul B secara langsung. Modul A wajib memanggil fungsi *Service* publik yang diekspor oleh Modul B secara legal.
* **Kueri Tipe Pembacaan Data (GET/Pelaporan/Dashboard):** Untuk menjaga latensi respons di gawai seluler tetap di bawah 50ms, data gabungan lintas modul yang kompleks wajib diolah melalui file **Aggregator khusus** (misalnya pada modul dashboard atau modul laporan). Proses penarikan data dari internal service asal wajib dieksekusi secara paralel menggunakan `Promise.all` guna memaksimalkan performa I/O runtime Bun.

### B. Standardisasi TypeScript (Interface & Type Contract)
Setiap kali Anda membuat modul atau agregator baru, Anda wajib menuliskan kontrak tipe data yang jelas di folder modul terkait:
* **Interface Service (`src/modules/[nama_modul]/interfaces/`):** Digunakan untuk mengunci tipe data kembalian (*return type*) dari fungsi-fungsi service utama yang diekspor ke luar modul.
* **Type Response (`src/modules/[nama_modul]/types/`):** Digunakan untuk mendefinisikan objek final hasil transformasi/agregasi data yang akan dikirim kembali sebagai JSON payload Contract menuju aplikasi klien React Native.

---

## 🧠 3. Kebijakan Autentikasi & Keamanan (JWT Stateless Strategy)

Gerbang login aplikasi mobile React Native melayani pelanggan (Member) menggunakan kombinasi Email, Nomor HP, atau ID Member.
* **Mekanisme Token:** Wajib menggunakan strategi **JWT Stateless murni** melalui `@elysiajs/jwt`. Jangan melakukan pengecekan token ganda atau daftar hitam (*revoked tokens*) ke Redis.
* **Layer Otomatisasi Penonaktifan Global (`User.is_active`):** Jika Administrator pusat menonaktifkan pengguna (`is_active = false`), token JWT yang dibawa oleh *client request* harus otomatis ditolak pada lapisan otorisasi global selanjutnya (ketika token kedaluwarsa atau saat parsing muatan klaim user id tidak lagi valid di database).

---

## 🪙 4. Manajemen Riwayat Transaksi & Point Ledger (Real-time Query)

Sistem ini menganut prinsip *Single Currency, Multi-Path* (1 Saldo Wallet Terpusat, namun nominal pembagi perolehannya dibedakan dinamis via tabel `transaction_types`).
* **Mekanisme Pembacaan:** API ElysiaJS untuk penayangan riwayat mutasi poin (`PointMutation`) wajib **membaca langsung secara real-time** melalui Prisma Client Query menuju tabel master `transaction_types` dan `conversion_rules`.
* **Dilarang Melakukan Caching Redis** untuk alur ini guna menjamin konsistensi angka pembukuan pusat (ledger) dan transparansi mutasi saldo di gawai smartphone pelanggan secara mutlak.

---

## 🎁 5. Siklus Hidup Hybrid Redeem Reward Engine (Token Code Generator)

Saat pelanggan memicu proses klaim kupon hadiah fisik di aplikasi seluler:
* **Penerbitan Kode:** Lahirkan string acak alfanumerik **10 karakter** uppercase yang unik di tabel `RedeemToken` (contoh: `X7R92QK3TM`). Mobile menampilkan QR berisi plain `tokenCode`; kasir scan di Filament (bukan lewat endpoint API ini).
* **Batas Waktu Dinamis (Durasi Kedaluwarsa):** Batas waktu ideal di kolom `expired_at` wajib dikunci sebesar **30 menit**. Durasi ini harus membaca variabel lingkungan (Doppler / `process.env`) secara dinamis agar nilainya dapat diubah sewaktu-waktu oleh tim operasional tanpa membongkar fungsionalitas kode program.
* **Spesifikasi alur:** `memory/dev_phase_redeem.md` + `memory/flow_redeem_point.md` (root monorepo). Konfirmasi kasir (scan/ketik token + OTP + invoice + push FCM) dikerjakan di Filament langsung ke DB; API mobile: reserve/status/history + **cancel** (refund hold) + registrasi token perangkat.

### 5.1 Registrasi Device Push Token (FCM)

Modul: `src/modules/device/`. Tabel shared: `device_push_tokens` (`DevicePushToken` di Prisma).

| Method | Path | Body | Peran |
| --- | --- | --- | --- |
| `POST` | `/api/device/push-token` | `{ token, deviceUuid?, platform? }` | Upsert token FCM/APNs untuk `userId` JWT; platform default mobile |
| `DELETE` | `/api/device/push-token` | `{ token }` | Soft-revoke (`revoked_at`) — dipanggil saat logout mobile |

Auth: JWT member. Filament mengirim push lewat `FcmPushDriver` + token di tabel ini — **jangan** invent endpoint kirim-push di api-elysia.

### 5.2 Cancel & status token (member)

| Method | Path | Peran |
| --- | --- | --- |
| `POST` | `/api/redeem/cancel` | Batalkan reservasi aktif: refund `held_points`, `held_stock -= 1`, set `released_at` |
| `GET` | `/api/redeem/token/:redeemId/status` | `active` \| `completed` (+ `invoiceId`) \| `released` \| `expired` — untuk pull-to-refresh mobile |

Guard cancel: milik member, belum `is_used`, belum `released_at`, belum expired.

---

## 🚨 6. Logika Pengawasan Keamanan Dual-Layer (`is_active` vs `is_suspended`)

Aplikasi harus merespons kode payload status keuangan member secara terpisah demi memisahkan domain keamanan global dan aturan bisnis anti-fraud:
1.  **`User.is_active = false`:** Putus sesi login global secara paksa (*Force Logout*). Dilarang memberikan toleransi akses ke modul apa pun.
2.  **`Member.is_suspended = true`:** Pelanggan **tetap diizinkan login** ke aplikasi mobile React Native. Mereka berhak membaca artikel berita CMS, informasi cabang, dan memantau status tingkat lencana (Tiering). Namun, **kunci total seluruh modul keuangan**:
    * Sistem harus memblokir pemicuan pembuatan `RedeemToken` baru (Tombol redeem otomatis terkunci).
    * Tolak proses pencairan atau penambahan poin baru dari antrean CSV pusat.

---

## 📋 7. Standar Penulisan Kode & Kualitas (Bun + ElysiaJS + Prisma)

* **Strict TypeScript Mode:** Selalu definisikan tipe data untuk request payload, response object, dan Prisma query parameters secara eksplisit. Penggunaan tipe data `any` dilarang keras.
* **Prisma Client Instance:** Pastikan connection pool diatur secara optimal untuk menangani request berlatensi rendah (< 50ms) dari aplikasi seluler pelanggan.
* **Response Format Standard:** Seluruh API Response wajib dibungkus dalam format objek JSON terstandardisasi:
    ```json
    {
      "success": true,
      "message": "Pesan informasi sukses/gagal",
      "data": {} 
    }
    ```

---

## 🧩 8. Gap Schema vs Kontrak Mobile (jangan asumsikan kolom ada)

Prisma / `packages/database` bisa lebih maju dari MySQL lokal. Sebelum SELECT kolom baru, verifikasi migrasi. Catatan aktif untuk agent:

| Model | Field diminta mobile | Status | Catatan |
| --- | --- | --- | --- |
| `PromotionBanner` | `linkUrl`, `sortOrder` | ✅ Ada | Order `sortOrder asc`; `linkUrl` nullable |
| `Content` (EVENT) | `locationAddress`, `locationUrl` | ✅ Ada | Hanya relevan untuk EVENT |
| `Content` (NEWS) | kategori | **Belum ada** | Detail tanpa category |
| `Branch` | lat / lng (nearest) | **Belum ada** (`locationUrl` ada) | Tidak expose nearest geo |
| `Member` | `birthDate` | ✅ Ada | Pastikan migrasi Laravel `birth_date` sudah dijalankan |

Modul publik yang sudah dipakai mobile: `content` (`q`/`dateFrom`/`dateTo`), `branch` (`q`/`city` + `/cities`), `reward` (`sortBy`/`sortOrder` + cursor sort-aware; **list/catalog/home** hanya reward dengan stok tersedia > 0; **detail** tetap return reward stok habis tapi `branchStocks` hanya cabang available > 0), `promotion-banner`, `redeem`, `device` (push-token).

### Reward stock visibility (mobile contract)

Stok tersedia dihitung sebagai `max(actualStock - heldStock, 0)` per cabang; `stockRemaining` = jumlah seluruh cabang.

| Endpoint | Perilaku stok |
| --- | --- |
| `GET /api/reward` | Reward dengan stok total 0 disembunyikan. Filter `branchId` menyembunyikan reward jika cabang tersebut available = 0. Pagination over-fetch hingga `limit` item in-stock (max 10 round DB). |
| `GET /api/reward/catalog` | Sama; kategori tanpa reward in-stock tidak dikembalikan. |
| `GET /api/reward/home` | Sama; skip reward OOS meski terbaru; ambil hingga 2 in-stock per kategori. |
| `GET /api/reward/:sku` | Reward tetap 200 walau stok total 0 (deep link); `branchStocks` hanya cabang available > 0. |

Helper shared: `src/modules/reward/lib/reward-stock.ts`. Tests: `reward-stock.test.ts` (unit, tanpa DB), `reward-stock-filter.test.ts` + `reward-home-preview.test.ts` (integration, butuh `hkgold_membership_test` migrated).

Saat menambah kolom: migrasi Prisma + update OpenAPI + mapper service + CMS Filament — jangan hanya ubah TypeScript response.

---

## 🛠️ 9. Perintah Eksekusi Pertama Anda

Sebelum Anda mulai menulis kode endpoint baru, membuat interface, atau memodifikasi fungsionalitas pengontrol (*controller*), eksekusi langkah-langkah diagnostik berikut:
1. Pastikan Doppler CLI login (`doppler login`) dan `doppler.yaml` mengarah ke `hkgoldvip` / `dev_backend`. Verifikasi secret ter-inject: `doppler secrets`.
2. Periksa kelancaran koneksi database ke target di secret Doppler (`DATABASE_URL`).
3. Lakukan introspeksi database (`doppler run -- bunx prisma db pull` atau skema migrasi dev) untuk memastikan sinkronisasi tabel-tabel utama (`User`, `Member`, `PointMutation`, `RedeemToken`, `DevicePushToken`, `TransactionType`) siap melayani endpoint mobile app. Bandingkan dengan gap di §8.