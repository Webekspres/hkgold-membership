# Flow: Bulk Update Poin (PointInjectionBatch)

> Dokumen ini adalah laporan analisis alur fitur bulk update poin berdasarkan pembacaan kode eksisting.
> Dibuat untuk menjadi panduan implementasi fitur end-to-end.

---

## Gambaran Alur Keseluruhan

```
[User klik Upload Bulk]
        ↓
[Upload file Excel/CSV → Buat PointInjectionBatch]
        ↓
[Dispatch Background Job → Parse tiap baris → Insert PointInjectionDetail]
        ↓
[Halaman View Batch — polling/loading indicator]
        ↓
[User review: VALIDATED vs FAIL — edit/hapus row FAIL]
        ↓
[Semua row VALIDATED → klik Process → Modal ringkasan]
        ↓
[Konfirmasi → PointInjectionDetail → PointMutation + Update saldo member]
        ↓
[Batch ditandai resolved = true]
```

---

## Kondisi Kode Saat Ini

### Yang Sudah Ada ✅
| Komponen | File | Status |
|---|---|---|
| Model `PointInjectionBatch` | `app/Models/PointInjectionBatch.php` | ✅ Lengkap (incl. `resolved` boolean) |
| Model `PointInjectionDetail` | `app/Models/PointInjectionDetail.php` | ✅ Lengkap |
| Model `PointMutation` | `app/Models/PointMutation.php` | ✅ Lengkap |
| Service Kalkulasi Poin | `app/Services/Loyalty/PointCalculationService.php` | ✅ Siap pakai |
| Service Hitung Ulang Detail | `app/Services/Loyalty/RecalculateDetailPointsService.php` | ✅ Siap pakai |
| Service Injeksi Manual | `app/Services/Loyalty/ManualPointInjectionService.php` | ✅ Lengkap (inject + preview) |
| Tabel Detail (Filament) | `PointInjectionDetailsTable.php` | ✅ Edit baris, filter status |
| Halaman View Batch | `ViewPointInjectionBatch.php` | ✅ Custom blade, tabel detail |
| Button Upload (shell) | `UploadBulkAction.php` | ⚠️ Dummy — belum implementasi |
| Button Process (shell) | di `ViewPointInjectionBatch` | ⚠️ Dummy — belum implementasi |

### Yang Belum Ada / Perlu Dibuat ❌
| Komponen | Keterangan |
|---|---|
| **Queue Job** | `app/Jobs/ProcessBulkInjectionJob.php` — belum ada direktori `Jobs/` |
| **Parsing Excel/CSV** | Belum ada (perlu package seperti `maatwebsite/excel` atau `league/csv`) |
| **Logika Upload di Action** | `UploadBulkAction` masih dummy notification |
| **Loading/polling indicator** | Halaman View belum punya indikator progress saat job jalan |
| **Action "Hapus" di row detail** | `PointInjectionDetailsTable` belum ada action delete |
| **Logika Process Batch** | Konversi `PointInjectionDetail` → `PointMutation` + update member saldo |
| **Modal ringkasan Process** | Saat ini hanya `requiresConfirmation()` sederhana |
| **Enum status `VALIDATED`** | `InjectionStatus` hanya punya `PENDING`, `SUCCESS`, `FAILED` — perlu `VALIDATED`? |

---

## Detail Tiap Tahap

### Tahap 1 — Upload File → Buat PointInjectionBatch

**Trigger**: Header action `UploadBulkAction` di `ListPointInjectionBatches`

**Yang perlu dibuat:**
- Modal upload dengan `FileUpload` (accept `.xlsx`, `.csv`)
- Simpan file ke disk (bisa `r2` atau local temp)
- Buat record `PointInjectionBatch` dengan `staff_id`, `media_id`, `resolved = false`
- Dispatch `ProcessBulkInjectionJob::dispatch($batch)`
- Redirect ke halaman View batch yang baru dibuat

**Kolom PointInjectionBatch yang diisi:**
```
staff_id           = auth()->user()->staff->id
media_id           = media record dari file yang diupload
total_rows         = (dihitung saat parsing)
successful_rows    = 0 (update incremental saat job jalan)
failed_rows        = 0 (update incremental saat job jalan)
total_points_injected = 0
resolved           = false
uploaded_at        = now()
```

---

### Tahap 2 — Background Job: Parse & Insert PointInjectionDetail

**File yang perlu dibuat**: `app/Jobs/ProcessBulkInjectionJob.php`

**Logika per baris:**
1. Baca baris Excel/CSV → kolom yang diharapkan:
   - `member_number` → `raw_member_number`
   - `branch_code` → `raw_branch_code`
   - `purchase_nominal`
   - `transaction_type` (kode atau ID)
   - `transaction_date`
   - `receipt_number` (optional)

2. Validasi setiap baris:
   - Member dengan `member_number` ada di DB? → jika tidak: `status = FAILED`, `error_message = "Member tidak ditemukan"`
   - Member suspended? → `FAILED`
   - Branch code valid? → jika tidak: `FAILED`
   - Nominal > 0? → jika tidak: `FAILED`
   - Receipt number duplikat? → `FAILED`
   - Jika semua valid → jalankan `RecalculateDetailPointsService::recalculate()` → `status = VALIDATED` atau `PENDING`?

3. Insert `PointInjectionDetail` per baris
4. Update counter di `PointInjectionBatch` setelah semua baris selesai

**Catatan penting — status enum:**
> Saat ini `InjectionStatus` hanya punya `PENDING | SUCCESS | FAILED`.  
> Kita butuh status `VALIDATED` (sudah dicek, siap diproses) sebelum jadi `SUCCESS` (sudah masuk ke PointMutation).

---

### Tahap 3 — Halaman View: Progress Indicator

**File**: `ViewPointInjectionBatch.php` + `view-point-injection-batch.blade.php`

**Yang perlu ditambahkan:**
- Cek apakah masih ada baris `PENDING` di detail → jika ada, tampilkan loading spinner + progress bar
- Bisa pakai Livewire `wire:poll` setiap 3 detik untuk refresh count
- Setelah semua baris selesai (tidak ada `PENDING`), tampilkan ringkasan: berapa `VALIDATED`, berapa `FAILED`
- Tombol "Process" hanya aktif jika `failed_rows == 0` dan `total_rows > 0`

**Logika kondisi tombol Process:**
```php
// Aktif hanya jika semua validated (0 FAILED, 0 PENDING)
$batch->failed_rows === 0 && $batch->successful_rows === $batch->total_rows
```

---

### Tahap 4 — Edit / Hapus Row FAILED

**File**: `PointInjectionDetailsTable.php`

**Edit (sudah ada ✅)**:
- Edit `purchase_nominal`, `transaction_type_id`, `raw_branch_code`
- Setelah edit: panggil `RecalculateDetailPointsService::recalculate($detail)` → update `calculated_points` dan ubah `status` ke `VALIDATED` jika valid

**Hapus (belum ada ❌)**:
- Perlu tambah action `Delete` di tabel detail
- Setelah hapus: kurangi `total_rows` di batch, update `failed_rows`

**Alasan fail yang tampil (sudah ada ✅)**:
- Kolom `error_message` sudah ada di `PointInjectionDetail`
- Perlu ditampilkan di tabel (belum ada kolom `error_message` di `PointInjectionDetailsTable`)

---

### Tahap 5 — Process Batch → PointMutation + Update Member

**Trigger**: Tombol "Process" di `ViewPointInjectionBatch` header actions

**Modal yang perlu dibuat** (menggantikan `requiresConfirmation()` sederhana):
- Ringkasan: total member, total poin yang akan diinjeksi, total nominal
- Tombol konfirmasi "Ya, Proses Sekarang"

**Logika Process (perlu dibuat `ProcessBatchAction` atau `ProcessBatchService`):**
```
Untuk setiap PointInjectionDetail WHERE status = VALIDATED:
  1. Load member (lockForUpdate)
  2. Hitung poin dari purchase_nominal + conversion rule tier member
  3. Buat PointMutation (source_id = batch_id)
  4. Update member: point_balance, highest_point, current_tier, last_activity_at
  5. Update detail.status = SUCCESS, detail.processed_at = now()

Setelah semua:
  6. Update batch: total_points_injected = SUM(calculated_points), resolved = true
  7. Catat ActivityLog
```

**Service yang bisa digunakan kembali:**
- `PointCalculationService::resolveEligibleTierUpgrade()` — sudah ada ✅
- `PointCalculationService::calculateIssuedPoints()` — sudah ada ✅
- `ManualPointInjectionService` — logikanya bisa diadaptasi untuk bulk

---

## Keputusan Final (Sudah Dikonfirmasi)

| Topik | Keputusan |
|---|---|
| **Enum Status** | 4 status: `PENDING` (dalam queue) → `VALIDATED` (ok, siap proses) / `FAILED` (tidak valid) → `SUCCESS` (sudah masuk PointMutation) |
| **Parsing Library** | `maatwebsite/excel` |
| **Loading Indicator** | Livewire `wire:poll` (auto-refresh beberapa detik) |
| **Cek Duplikat Receipt** | Terhadap DB `point_mutations` yang sudah ada **+ antar baris dalam batch yang sama** |

---

## Urutan Implementasi yang Disarankan

1. Update `InjectionStatus` enum — tambah `Validated = 'VALIDATED'` dan `Pending = 'PENDING'` (rename `Success` menjadi `Success = 'SUCCESS'`)
2. Install `maatwebsite/excel`
3. Buat `ProcessBulkInjectionJob` — parse file, insert detail, validasi tiap baris
4. Implementasi `UploadBulkAction` — modal upload, dispatch job, redirect ke View
5. Tambah `wire:poll` + loading indicator di `view-point-injection-batch.blade.php`
6. Tambah kolom `error_message` + action hapus di `PointInjectionDetailsTable`
7. Implementasi logika Process: `PointInjectionDetail` → `PointMutation` + update member
8. Update modal Process dengan ringkasan data


---

## File yang Akan Dibuat / Dimodifikasi

### Baru (NEW)
- `app/Jobs/ProcessBulkInjectionJob.php`
- `app/Jobs/ProcessBatchToMutationJob.php` (atau inline di action)
- Importer/Parser class untuk Excel/CSV

### Dimodifikasi
- `app/Enums/InjectionStatus.php` — tambah `Validated = 'VALIDATED'` (jika disetujui)
- `app/Filament/Resources/PointInjectionBatches/Actions/UploadBulkAction.php` — implementasi penuh
- `app/Filament/Resources/PointInjectionBatches/Tables/PointInjectionDetailsTable.php` — tambah kolom `error_message`, action hapus
- `app/Filament/Resources/PointInjectionBatches/Pages/ViewPointInjectionBatch.php` — polling, disable tombol Process kondisional
- `resources/views/filament/resources/point-injection-batches/view-point-injection-batch.blade.php` — progress indicator UI

---

## Fase-Fase Development

### Fase 1 — Fondasi: Enum & Schema

**Tujuan**: Menyiapkan kontrak data sebelum logika apapun ditulis.

**Tugas:**

#### 1.1 — Update `InjectionStatus` Enum
- File: `app/Enums/InjectionStatus.php`
- Ubah dari 3 status menjadi 4 status:
  ```php
  enum InjectionStatus: string
  {
      case Pending   = 'PENDING';    // Baris baru masuk, belum divalidasi
      case Validated = 'VALIDATED';  // Lolos validasi, siap diproses ke PointMutation
      case Failed    = 'FAILED';     // Gagal validasi, ada error_message
      case Success   = 'SUCCESS';    // Sudah berhasil masuk ke PointMutation
  }
  ```
- Pastikan semua referensi ke enum lama di `PointInjectionDetailsTable.php` dan tempat lain diperbarui untuk mencakup case `Validated`

#### 1.2 — Update Tampilan Enum di Tabel
- File: `PointInjectionDetailsTable.php`
- Tambahkan case `Validated` pada `badge()->color()` dan `formatStateUsing()`:
  - Warna: `info` (biru), Label: `"Tervalidasi"`

**Output Fase 1**: Enum siap, tabel menampilkan 4 status dengan benar.

---

### Fase 2 — Infrastruktur: Parsing Excel & Upload Action

**Tujuan**: Membuat pipeline upload file → buat batch → dispatch job background.

**Tugas:**

#### 2.1 — Install Package `maatwebsite/excel`
```bash
composer require maatwebsite/excel
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```

#### 2.2 — Buat Excel Importer Class
- File baru: `app/Imports/PointInjectionImport.php`
- Implementasikan `ToCollection` atau `ToModel` dari Maatwebsite
- Mapping kolom header (case-insensitive, flexible):

  | Kolom Excel | Field Internal |
  |---|---|
  | `tgl transaksi` / `transaction_date` | `transaction_date` |
  | `nomor member` / `member_number` | `raw_member_number` |
  | `nomor struk` / `receipt_number` | `receipt_number` |
  | `nominal transaksi` / `purchase_nominal` | `purchase_nominal` |
  | `jenis transaksi` / `transaction_type` | `transaction_type_key` (dicocokkan ke `type_key` atau `display_name` di `transaction_types`) |
  | `branch code` / `branch_code` | `raw_branch_code` (nullable) |

- Importer hanya mengumpulkan data mentah ke array — **tidak ada validasi di sini**

#### 2.3 — Buat Background Job
- File baru: `app/Jobs/ProcessBulkInjectionJob.php`
- Constructor: menerima `PointInjectionBatch $batch`
- Queue: gunakan queue default atau buat queue bernama `bulk-injection`
- Logika per baris:
  1. Parse collection dari file Excel (`Media->file_url`)
  2. Hitung `total_rows`, update di batch
  3. Untuk setiap baris, jalankan validasi:
     - `raw_member_number` ada di `members.member_number`? → jika tidak: `FAILED`, "Member tidak ditemukan"
     - Member `is_suspended = true`? → `FAILED`, "Member dinonaktifkan"
     - `raw_branch_code` diisi dan tidak ada di `branches.branch_code`? → `FAILED`, "Kode cabang tidak valid"
     - `purchase_nominal` > 0? → jika tidak: `FAILED`, "Nominal tidak valid"
     - `transaction_type` bisa dicocokkan? → jika tidak: `FAILED`, "Jenis transaksi tidak ditemukan"
     - `receipt_number` duplikat di `point_mutations`? → `FAILED`, "Nomor struk sudah dipakai"
     - `receipt_number` duplikat dalam batch yang sama? → `FAILED`, "Nomor struk duplikat dalam file"
  4. Jika semua valid: hitung `calculated_points` via `RecalculateDetailPointsService`, set `status = VALIDATED`
  5. Insert `PointInjectionDetail`
  6. Update counter `successful_rows` / `failed_rows` di batch setiap N baris (chunk)
  7. Setelah semua selesai: update `total_rows` final di batch

#### 2.4 — Implementasi `UploadBulkAction`
- File: `app/Filament/Resources/PointInjectionBatches/Actions/UploadBulkAction.php`
- Ubah dari dummy menjadi modal dengan:
  - `FileUpload` yang hanya menerima `.xlsx`, `.xls`, `.csv`
  - Maksimal 1 file, ukuran maks 5MB
  - Setelah submit:
    1. Upload file ke disk, simpan sebagai record `Media`
    2. Buat `PointInjectionBatch` (staff dari `auth()->user()->staff`)
    3. Dispatch `ProcessBulkInjectionJob::dispatch($batch)`
    4. Redirect ke `PointInjectionBatchResource::getUrl('view', ['record' => $batch->id])`
    5. Tampilkan notifikasi: "File sedang diproses di background..."

**Output Fase 2**: User bisa upload file, batch terbuat, job berjalan di background.

---

### Fase 3 — UX: Loading Indicator & Progress Polling

**Tujuan**: User mendapatkan feedback real-time tentang status pemrosesan file.

**Tugas:**

#### 3.1 — Tambah `wire:poll` ke Halaman View
- File: `resources/views/filament/resources/point-injection-batches/view-point-injection-batch.blade.php`
- Logika kondisi:
  - Jika masih ada baris `PENDING` (job belum selesai) → tampilkan progress section
  - Jika tidak ada `PENDING` → sembunyikan progress section, tampilkan ringkasan hasil
- Implementasi `wire:poll`:
  ```blade
  @if($this->hasPendingRows())
      <div wire:poll.3000ms="refreshBatch"> ... </div>
  @endif
  ```

#### 3.2 — Tambah Metode `hasPendingRows()` & `refreshBatch()`
- File: `app/Filament/Resources/PointInjectionBatches/Pages/ViewPointInjectionBatch.php`
- `hasPendingRows()`: cek apakah ada `PointInjectionDetail` dengan `status = PENDING` untuk batch ini
- `refreshBatch()`: reload `$this->record` dari DB untuk mendapatkan counter terbaru

#### 3.3 — Desain UI Progress Section di Blade
- Tampilkan:
  - Spinner animasi + teks "Sedang memproses baris..."
  - Progress bar: `(processed / total) * 100%` (processed = total - pending)
  - Counter: `X dari Y baris selesai`
- Setelah selesai, tampilkan summary card:
  - ✅ `N baris tervalidasi` (hijau)
  - ❌ `M baris gagal` (merah, jika ada)
  - Banner peringatan jika ada baris gagal: "Terdapat baris yang gagal divalidasi. Perbaiki atau hapus sebelum memproses."

#### 3.4 — Kondisi Tombol "Process"
- Tombol `Process` di header actions hanya aktif jika:
  - `failed_rows == 0`
  - `total_rows > 0`
  - Tidak ada baris `PENDING` (job selesai)
  - `resolved == false` (batch belum pernah diproses)
- Jika kondisi tidak terpenuhi: tombol disabled dengan tooltip penjelasan

**Output Fase 3**: Halaman View menampilkan progress real-time, user tahu kapan bisa menekan Process.

---

### Fase 4 — Review: Error Message & Manajemen Baris

**Tujuan**: User bisa melihat alasan gagal, memperbaiki, atau menghapus baris yang bermasalah.

**Tugas:**

#### 4.1 — Tambah Kolom `error_message` di Tabel Detail
- File: `PointInjectionDetailsTable.php`
- Tambahkan `TextColumn::make('error_message')` dengan konfigurasi:
  - Hanya tampil jika ada isi (`placeholder('-')`)
  - Warna merah jika berisi pesan error
  - Bisa di-toggle (tersembunyi by default, hanya visible jika perlu)
  - Atau: tampilkan sebagai tooltip / badge di kolom Status

#### 4.2 — Tambah Action "Hapus" di Tabel Detail
- File: `PointInjectionDetailsTable.php`
- Tambahkan action `DeleteAction` atau custom `Action::make('delete')`:
  - Hanya tampil untuk baris dengan `status = FAILED` (atau semua, tapi konfirmasi)
  - Setelah hapus: update `failed_rows--` dan `total_rows--` di batch parent
  - Tampilkan notifikasi sukses

#### 4.3 — Perbaiki Logika Action "Edit"
- File: `PointInjectionDetailsTable.php` (sudah ada, perlu diperluas)
- Setelah edit disimpan: jalankan ulang `RecalculateDetailPointsService::recalculate($detail)`
- Jika kalkulasi berhasil (calculated_points > 0): ubah `status = VALIDATED`, hapus `error_message`
- Jika gagal: pertahankan `status = FAILED`, update `error_message`
- Update counter `failed_rows` dan `successful_rows` di batch

**Output Fase 4**: User bisa melihat detail error, edit data yang salah, dan menghapus baris yang tidak bisa diperbaiki.

---

### Fase 5 — Proses Akhir: Batch → PointMutation

**Tujuan**: Mengeksekusi semua baris `VALIDATED` menjadi mutasi poin yang nyata dan memperbarui saldo member.

**Tugas:**

#### 5.1 — Buat `ProcessBatchService`
- File baru: `app/Services/Loyalty/ProcessBatchService.php`
- Menerima `PointInjectionBatch $batch` dan `User $actor`
- Jalankan dalam satu DB Transaction besar:
  ```
  Untuk setiap PointInjectionDetail WHERE batch_id = X AND status = VALIDATED:
    1. Load Member (lockForUpdate) dari raw_member_number
    2. Resolve Branch dari raw_branch_code (nullable)
    3. Hitung poin via PointCalculationService
    4. Buat PointMutation:
       - member_id, branch_id, source_id = batch_id
       - receipt_number, transaction_type_id
       - purchase_nominal, points_issued = calculated_points
       - points_redeemed = 0
       - balance_snapshot = previous_balance + points_issued
       - transaction_date, uploaded_at
    5. Update Member:
       - point_balance += points_issued
       - highest_point = max(highest_point, new_balance)
       - current_tier = resolveEligibleTierUpgrade(...)
       - last_activity_at = transaction_date
    6. Update detail: status = SUCCESS, processed_at = now()
  
  Setelah semua detail diproses:
    7. Update Batch:
       - total_points_injected = SUM(calculated_points of SUCCESS rows)
       - resolved = true
    8. Catat ActivityLog (action = 'bulk_point_injection')
  ```
- Handle error: jika satu baris gagal saat process (misalnya member terhapus), rollback seluruh transaksi dan lempar exception

#### 5.2 — Implementasi Modal Ringkasan di `ViewPointInjectionBatch`
- File: `ViewPointInjectionBatch.php`
- Ganti `requiresConfirmation()` sederhana dengan `Action` yang memiliki modal custom:
  - Tampilkan ringkasan sebelum konfirmasi:
    - Total member unik yang akan menerima poin
    - Total baris yang akan diproses
    - Total poin yang akan diinjeksi (sum `calculated_points`)
    - Total nominal transaksi (sum `purchase_nominal`)
  - Tombol: "Ya, Proses Sekarang" dan "Batal"
- Setelah konfirmasi: panggil `ProcessBatchService::process($batch, auth()->user())`
- Tampilkan notifikasi sukses: "Berhasil memproses X baris, total Y poin diinjeksi"
- Redirect atau reload halaman

#### 5.3 — Integrasi ActivityLog
- Catat log dengan:
  - `action = 'bulk_point_injection'`
  - `auditable_type = 'PointInjectionBatch'`, `auditable_id = $batch->id`
  - `before_json`: total_points_injected sebelumnya (biasanya 0), resolved sebelumnya
  - `after_json`: total_points_injected setelah, resolved = true
  - `user_id` dari actor yang menekan Process

**Output Fase 5**: Batch terproses penuh, semua VALIDATED → SUCCESS, saldo member ter-update, resolved = true.

---

### Fase 6 — Keamanan & Edge Cases

**Tujuan**: Memastikan sistem tahan terhadap kasus-kasus tepi dan tidak bisa disalahgunakan.

**Tugas:**

#### 6.1 — Guard di Tombol Process
- Validasi ulang di server side (bukan hanya di UI) sebelum `ProcessBatchService` dijalankan:
  - Batch sudah `resolved`? → lempar error "Batch ini sudah pernah diproses"
  - Masih ada `PENDING` rows? → lempar error "Masih ada baris yang belum selesai divalidasi"
  - Ada `FAILED` rows? → lempar error "Masih ada baris yang gagal. Perbaiki dulu sebelum memproses"

#### 6.2 — Idempotency Check
- Jika user menekan Process dua kali (race condition), pastikan tidak ada `PointMutation` duplikat
- Gunakan `unique constraint` pada `point_mutations.receipt_number + transaction_type_id`
- Di `ProcessBatchService`: sebelum membuat mutation, cek apakah `detail.status` masih `VALIDATED` (bukan sudah `SUCCESS`)

#### 6.3 — Proteksi Upload Ulang
- Setelah batch `resolved = true`: sembunyikan tombol Upload di list, atau disable aksi terkait
- Batch yang sudah resolved hanya bisa dilihat (view only)

#### 6.4 — Penanganan Job Failure
- Jika `ProcessBulkInjectionJob` gagal di tengah jalan (server crash, timeout):
  - Baris yang belum diproses tetap `PENDING`
  - Perlu mekanisme "Retry Job" atau "Reset ke Pending" agar user bisa mencoba ulang
  - Pertimbangkan: tambahkan field `processing_started_at` di batch atau gunakan Laravel Queue retry

**Output Fase 6**: Sistem aman, tidak bisa double-process, tidak ada data korup.

---

### Ringkasan Urutan & Estimasi Kompleksitas

| Fase | Nama | Kompleksitas | Catatan |
|---|---|---|---|
| **1** | Enum & Schema | Rendah | Fondasi untuk semua fase |
| **2** | Parsing & Upload | **Tinggi** | Core logic — job background + mapping kolom Excel |
| **3** | Loading Indicator | Sedang | Livewire polling + UI |
| **4** | Review Baris | Sedang | Edit/hapus + recalculate |
| **5** | Process Batch | **Tinggi** | Transaction besar + update banyak member |
| **6** | Keamanan | Sedang | Guard, idempotency, edge cases |
