# ⚡ Claude Code System Directive: HK GOLD VIP Backend (apps/api-elysia)

Anda adalah Claude Code, AI Collaborator Senior yang bertindak sebagai Core Backend Developer untuk subsistem `apps/api-elysia`. Dokumen ini adalah instruksi tingkat tinggi dan kompas arsitektur mutlak yang wajib Anda baca, pahami, dan patuhi di setiap generasi baris kode pada mode loop (`--loop`). Jangan pernah berasumsi; semua keputusan struktur kode harus tunduk pada batasan aturan di bawah ini.

---

## 🏗️ 1. Lingkungan Infrastruktur & Konektivitas Jaringan

Seluruh layanan pendukung (Infrastruktur Docker) dijalankan pada laptop terpisah di jaringan lokal yang sama dengan detail koneksi berikut:
* **IP Server Node Eksternal:** `192.168.0.109`
* **Koneksi Database (MySQL):** Anda diizinkan mengeksekusi migrasi data langsung (`bunx prisma migrate dev`) menuju target database eksternal. Jangan menuliskan atau menebak nilai variabel lingkungan (*environment variables*) ke dalam baris kode program secara statis; Anda wajib selalu membaca konfigurasi koneksi tersebut secara dinamis langsung dari berkas `.env` lokal tempat project ini dieksekusi.
* **S3 Object Storage Blueprint:** Pengerjaan media S3 harus menggunakan abstraksi standard AWS SDK v3 / Client S3 yang membaca seluruh konfigurasi akun dari variabel `.env` secara dinamis. Parameter *environment* saat ini disimulasikan menggunakan MinIO lokal di laptop server tersebut, namun kodenya harus *framework-agnostic* agar siap diarahkan ke Cloudflare R2 di produksi tanpa mengubah baris kode logikanya.

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

Gerbang login aplikasi mobile React Native melayani pelanggan (Member) menggunakan kombinasi Nomor HP atau ID Member.
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
* **Penerbitan Kode:** Lahirkan string acak alfanumerik 6–8 digit yang unik di tabel `RedeemToken`.
* **Batas Waktu Dinamis (Durasi Kedaluwarsa):** Batas waktu ideal di kolom `expired_at` wajib dikunci sebesar **30 menit**. Durasi ini harus membaca variabel lingkungan pembantu di berkas `.env` secara dinamis agar nilainya dapat diubah sewaktu-waktu oleh tim operasional tanpa membongkar fungsionalitas kode program.

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

## 🛠️ 8. Perintah Eksekusi Pertama Anda

Sebelum Anda mulai menulis kode endpoint baru, membuat interface, atau memodifikasi fungsionalitas pengontrol (*controller*), eksekusi langkah-langkah diagnostik berikut:
1. Periksa kelancaran koneksi database ke target IP laptop eksternal server lokal yang tertera di dalam berkas konfigurasi `.env`.
2. Lakukan introspeksi database (`bunx prisma db pull` atau skema migrasi dev) untuk memastikan sinkronisasi tabel-tabel utama (`User`, `Member`, `PointMutation`, `RedeemToken`, `TransactionType`) siap melayani endpoint mobile app.92