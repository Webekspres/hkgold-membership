
Berikut adalah penjelasan detil mengenai **Alur Pengguna (Siapa membuka menu apa)** dan **Alur Data di Backend (Database & Service)** untuk fitur penukaran poin (_Redeem Point_) HK GOLD VIP.

---

## 👥 BAGIAN 1: ALUR PENGGUNA (_USER FLOW_)

Alur ini memisahkan tindakan pelanggan di smartphone dengan tindakan kasir di toko fisik.

### Fase 1: Reservasi Hadiah oleh Pelanggan

- **Aktor:** `Customer` (Member)
- **Aplikasi:** Mobile App (React Native)
- **Langkah Pengguna:**

1. Member membuka menu **Katalog Hadiah** di aplikasi mobile.
2. Member memilih suvenir/hadiah (misal: Emas Batangan 1 Gram) dan memilih **Cabang Toko** (misal: `HK GOLD ARTOS MALL`) tempat ia ingin mengambil barang tersebut secara fisik.
3. Member menekan tombol **"Tukarkan Poin"**.
4. Aplikasi memunculkan QR Code / 10-digit **Kode Token** (misal: `GOLD-X7R92`) beserta waktu hitung mundur kedaluwarsa kupon (3 hari).

### Fase 2: Pengambilan Hadiah Fisik di Toko

- **Aktor:** Pelanggan (`Customer`) & Kasir Toko (`Store Manager` atau `Staff`)
- **Aplikasi:** Platform Web Admin (Laravel Filament v5)
- **Langkah Pengguna:**

1. Pelanggan datang ke meja kasir cabang Artos Mall dan menunjukkan QR Code/Kode Token dari HP-nya.
2. Kasir membuka Web Admin Filament, lalu masuk ke menu **🎫 Antrean Kupon (`RedeemTokenResource`)**.
3. Kasir mengklik tombol utama di pojok kanan atas: **`[📥 Verifikasi & Scan Token]`**.
4. Muncul modal pop-up, Kasir mengetikkan atau memindai kode `GOLD-X7R92` pelanggan.
5. Sistem menampilkan detail hadiah dan nama member di layar kasir untuk dicocokkan dengan KTP fisik pelanggan.
6. Kasir mengklik **"Kirim OTP"**, sistem otomatis menembakkan kode OTP via WhatsApp ke nomor HP pelanggan.
7. Pelanggan menyebutkan angka OTP-nya, Kasir menginput ke modal Filament dan mengklik **"Konfirmasi Selesai"**.
8. Sistem memunculkan notifikasi sukses hijau. Kasir mengambil hadiah fisik dari etalase toko dan menyerahkannya ke tangan pelanggan.

### Fase 3: Audit Keuangan Pusat

- **Aktor:** Tim Keuangan (`Finance`) & Manajemen Pusat (`Superadmin` / `Administrator`)
- **Aplikasi:** Platform Web Admin (Laravel Filament v5)
- **Langkah Pengguna:**

1. Tim Finance masuk ke menu khusus **🧾 Riwayat Nota Penukaran (`RedeemInvoiceResource`)** yang terpisah di sidebar.
2. Finance memantau rekap data seluruh invoice keluar nasional, melihat cabang mana yang mengeluarkan barang, poin yang terpotong, dan kasir mana yang bertanggung jawab. Mereka mengklik **`[📥 Export Laporan Keuangan]`** untuk pembukuan akhir bulan.

---

## 🗄️ BAGIAN 2: ALUR DATA BACKEND (_DATA PIPELINE_)

Berikut adalah pergerakan data di database MySQL melalui Prisma Client (ElysiaJS) dan Eloquent (Laravel) pada saat proses di atas terjadi:

```text
[Aplikasi Mobile] ──(Kirim Poin & Reward ID)──> [ElysiaJS API]
                                                    │ (Mulai db.$transaction)
                                                    ├──> Cek Saldo & Stok Cabang
                                                    ├──> Potong 'point_balance' di tabel Member
                                                    └──> INSERT row ke 'redeem_tokens' (isUsed: false)
                                                            │
[Web Admin Filament] <──(Kasir Input Token Code)────┴── [Staging Area]
         │
         ├──> QUERY 'redeem_tokens' WHERE tokenCode = input AND isUsed = false AND expiredAt > now()
         │
         └──> (Klik Konfirmasi Selesai -> Mulai DB::transaction() Laravel)
                 ├──> UPDATE 'redeem_tokens' SET isUsed = true
                 ├──> DECREMENT (-1) Stok pada tabel 'RewardBranchStock' khusus Cabang terkait
                 └──> INSERT row permanen ke 'redeem_invoices' (Lock points_redeemed & staff_id)

```

### 1. Manipulasi Data Saat Member Klik "Tukarkan" di Mobile (ElysiaJS API)

Jalankan blok transaksi atomik (`db.$transaction`) untuk mengamankan poin:

- **Read Check:** Sistem membaca data master `Reward` untuk tahu harga poin, dan mengecek apakah `Member.point_balance` mencukupi.
- **Write 1 (Potong Saldo):** Sistem mengurangi `point_balance` pada tabel `Member` saat itu juga (Poin ditahan sistem agar tidak bisa dipakai belanja lagi).
- **Write 2 (Buat Token):** Sistem menyisipkan baris baru ke tabel `redeem_tokens` dengan status `isUsed = false`, mengunci kolom `held_points`, mengisi target `branch_id`, dan mencatat `expired_at`.

### 2. Manipulasi Data Saat Kasir Klik "Konfirmasi" di Toko (Filament Web)

Ketika kasir memasukkan kode OTP WhatsApp yang valid dan menekan tombol konfirmasi, Laravel Eloquent mengeksekusi transaksi penutupan buku (`DB::transaction`):

- **Update 1 (Bakar Kupon):** Mengubah baris pada `RedeemToken` dari `is_used = false` menjadi `is_used = true`. Langkah ini mengunci token agar tidak bisa disalahgunakan lagi (Anti-_Double Spend_).
- **Update 2 (Potong Inventaris):** Sistem mengurangi jumlah stok fisik barang (`RewardBranchStock`) sebanyak 1 unit khusus pada ID cabang tempat kasir tersebut login.
- **Insert 3 (Terbitkan Nota Abadi):** Sistem membuat baris baru pada tabel `redeem_invoices` dengan melahirkan `invoice_number` formal baru. Di bagian ini, sistem mencatat `staff_id` (ID kasir dari session login) dan menyalin nilai poin ke kolom `points_redeemed` sebagai data _snapshot_ murni yang tidak akan berubah selamanya untuk kebutuhan audit kantor pusat.
