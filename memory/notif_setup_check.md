# Panduan Setup Firebase (FCM) & Cara Mengecek Berjalan

> Dokumen ini melengkapi [`notification_memo.md`](notification_memo.md) §18.  
> Scope: **backoffice-filament** mengirim push via `kreait/laravel-firebase`.  
> Token device didaftarkan oleh **mobile app (Elysia API)** — backoffice hanya membaca dari tabel `device_push_tokens`.

---

## Ringkasan alur

```text
[Firebase Console] → service account JSON → FIREBASE_CREDENTIALS di .env
                                                          ↓
[Mobile app login] → FCM token → Elysia API → device_push_tokens (DB)
                                                          ↓
[Suntik poin / Blast / Batch] → NotificationService → Redis queue `notifications`
                                                          ↓
                              DeliverNotificationJob / BroadcastNotificationJob
                                                          ↓
                                    FcmPushDriver → Firebase Cloud Messaging
                                                          ↓
                                         Push masuk ke HP member
```

**Yang wajib agar push benar-benar sampai ke HP:**

| # | Komponen | Cek cepat |
|---|----------|-----------|
| 1 | `FIREBASE_CREDENTIALS` terisi & file bisa dibaca | `isConfigured() === true` |
| 2 | Redis + queue worker jalan | `php artisan horizon` atau `queue:work` |
| 3 | Token FCM **asli** di `device_push_tokens` | Bukan token `dummy_*` dari seeder |
| 4 | Mobile app pakai **project Firebase yang sama** | `google-services.json` / `GoogleService-Info.plist` cocok |

---

## Bagian 1 — Buat project Firebase

### 1.1 Buat / pilih project

1. Buka [Firebase Console](https://console.firebase.google.com/).
2. Klik **Add project** (atau pilih project yang sudah dipakai mobile app).
3. Ikuti wizard sampai project aktif.

> **Penting:** Project Firebase untuk **mobile app** dan **backoffice (server)** harus **satu project yang sama**. Kalau beda project, push tidak akan sampai meski credential server valid.

### 1.2 Aktifkan Cloud Messaging

1. Di sidebar: **Build** → **Cloud Messaging**.
2. Pastikan fitur FCM aktif (default aktif untuk project baru).

Tidak perlu setup topic untuk v1 — backoffice memakai **multicast ke token dari database**, bukan Firebase topic.

### 1.3 Daftarkan app mobile (jika belum)

| Platform | Langkah |
|----------|---------|
| Android | **Add app** → Android → isi package name → download `google-services.json` |
| iOS | **Add app** → iOS → isi bundle ID → download `GoogleService-Info.plist` |

File ini dipasang di repo mobile (Expo/React Native), **bukan** di backoffice.

### 1.4 Generate Service Account (untuk backoffice)

Ini kredensial **server-to-server** — dipakai `FcmPushDriver` di Laravel.

1. Firebase Console → ikon **⚙ Project settings**.
2. Tab **Service accounts**.
3. Klik **Generate new private key** → konfirmasi → file JSON terunduh.
4. Simpan file dengan nama jelas, misalnya:
   ```
   firebase-service-account.json
   ```

**Jangan commit file ini ke git.**

---

## Bagian 2 — Konfigurasi backoffice

Semua perintah di bawah dijalankan dari folder:

```bash
cd apps/backoffice-filament
```

### 2.1 Simpan file credential

Buat folder khusus (diabaikan git):

```bash
mkdir -p storage/app/firebase
# Salin file JSON ke sini, contoh:
# storage/app/firebase/service-account.json
```

Tambahkan ke `.gitignore` jika belum ada:

```gitignore
/storage/app/firebase/
```

### 2.2 Isi `.env`

Variable yang **benar-benar dipakai** driver saat ini:

```env
NOTIFICATIONS_QUEUE=notifications
QUEUE_CONNECTION=redis

# Path absolut ke file JSON (disarankan)
FIREBASE_CREDENTIALS="D:/Project/hkgold-membership/apps/backoffice-filament/storage/app/firebase/service-account.json"
```

**Catatan path di Windows:** gunakan path absolut dengan `/` atau `\\`. Hindari path relatif jika worker dijalankan dari direktori lain.

**Alternatif:** isi `FIREBASE_CREDENTIALS` dengan isi JSON satu baris (untuk deploy tanpa file), tapi path file lebih mudah di-debug lokal.

Variable ini **tidak dipakai** oleh `FcmPushDriver` saat ini (boleh dikosongkan):

```env
FCM_PROJECT_ID=
FCM_CREDENTIALS_PATH=
```

Driver membaca dari `config/firebase.php` → `firebase.projects.app.credentials` → env `FIREBASE_CREDENTIALS`.

### 2.3 Clear config cache

Setelah ubah `.env`:

```bash
php artisan config:clear
```

---

## Bagian 3 — Jalankan queue worker

Push tidak terkirim hanya dengan menyimpan notifikasi di DB — **worker wajib jalan**.

### Opsi A — Horizon (disarankan dev/staging)

```bash
php artisan horizon
```

Dashboard: `http://127.0.0.1:8800/horizon` (sesuaikan `APP_URL`).

Pastikan supervisor `notifications-supervisor` memproses queue `notifications`.

### Opsi B — Worker tunggal (dev cepat)

```bash
php artisan queue:work redis --queue=notifications --tries=3
```

### Prasyarat Redis

Pastikan Redis hidup (`docker compose up -d redis` atau sesuai setup lokal):

```env
REDIS_HOST=127.0.0.1
REDIS_PORT=6381
```

---

## Bagian 4 — Device push token

### 4.1 Produksi / staging nyata

Token FCM didaftarkan oleh **mobile app** lewat Elysia API ke tabel `device_push_tokens`:

| Kolom | Nilai untuk push mobile |
|-------|-------------------------|
| `user_id` | UUID user member |
| `platform` | `MOBILE` |
| `token` | FCM registration token dari device |
| `revoked_at` | `NULL` (aktif) |

Backoffice **tidak** menulis token — hanya membaca saat kirim.

### 4.2 Testing lokal dengan token asli

Jika mobile app belum siap, sisipkan token FCM asli secara manual untuk 1 user uji:

```sql
-- Ganti USER_ID dan TOKEN_FCM_ASLI
INSERT INTO device_push_tokens (id, user_id, device_uuid, platform, token, last_used_at, revoked_at, created_at, updated_at)
VALUES (
  UUID(),
  'USER_ID_MEMBER',
  UUID(),
  'MOBILE',
  'TOKEN_FCM_ASLI_DARI_HP',
  NULL,
  NULL,
  NOW(),
  NOW()
);
```

Cara dapat token FCM dari HP:

- Log di mobile app saat register push, atau
- Firebase Console → Cloud Messaging → **Send test message** (untuk verifikasi token saja).

### 4.3 Token dummy seeder — hanya untuk uji job, bukan push nyata

`DevicePushTokenSeeder` membuat token seperti `dummy_mobile_xxxxxxxx`.  
Token ini **tidak valid** di FCM — job akan jalan tapi hasilnya `FAILED` dengan error dari Firebase.  
Berguna untuk memastikan queue & job berjalan, **bukan** untuk memastikan push sampai HP.

---

## Bagian 5 — Checklist verifikasi (bertingkat)

Jalankan dari **tingkat terendah** ke **tertinggi**. Jangan loncat ke suntik poin sebelum tingkat 1–2 hijau.

---

### ✅ Level 1 — Credential terbaca

```bash
cd apps/backoffice-filament
php artisan tinker
```

```php
app(\App\Services\Notification\FcmPushDriver::class)->isConfigured();
// Harus: true
```

| Hasil | Arti | Tindakan |
|-------|------|----------|
| `true` | File/JSON credential valid & bisa dibaca | Lanjut Level 2 |
| `false` | Credential kosong atau path salah | Cek `FIREBASE_CREDENTIALS`, `config:clear`, path absolut |

Cek tambahan di tinker:

```php
config('firebase.projects.app.credentials');
// Harus menampilkan path file atau string JSON, bukan null
```

---

### ✅ Level 2 — Kirim 1 push langsung (tanpa loyalty)

Masih di tinker — ganti `TOKEN_FCM_ASLI` dengan token dari HP:

```php
$result = app(\App\Services\Notification\FcmPushDriver::class)->sendToToken(
    token: 'TOKEN_FCM_ASLI',
    title: 'Tes FCM Backoffice',
    body: 'Jika ini muncul di HP, Firebase sudah benar.',
    data: ['type' => 'smoke_test'],
);

$result->success;       // true = FCM menerima
$result->errorMessage;  // null jika sukses
```

| Hasil | Arti |
|-------|------|
| `success = true` + notifikasi di HP | **Firebase setup selesai** |
| `success = false`, pesan credential | Kembali ke Bagian 2 |
| `success = false`, `InvalidRegistration` / `NotRegistered` | Token salah/kadaluarsa — daftar ulang dari app |
| `success = false`, `SenderId mismatch` | Mobile app & server **beda project Firebase** |

---

### ✅ Level 3 — Jalur personalized via NotificationService

```php
use App\Enums\NotificationPlatform;
use App\Models\User;
use App\Services\Notification\NotificationService;

$user = User::query()->whereKey('USER_ID_MEMBER')->first();
app(NotificationService::class)->notifyUser(
    user: $user,
    title: 'Tes notifyUser',
    body: 'Uji jalur NotificationService → queue → FcmPushDriver.',
    platforms: [NotificationPlatform::MobileAppPush],
    payload: ['type' => 'smoke_test'],
);
```

Lalu **pastikan worker jalan**, tunggu beberapa detik, cek DB:

```sql
SELECT id, platform, status, error_message, sent_at, attempt_count, created_at
FROM notifications
WHERE user_id = 'USER_ID_MEMBER'
ORDER BY created_at DESC
LIMIT 5;
```

| `status` | Arti |
|----------|------|
| `PENDING` | Job belum diproses — cek worker/Horizon |
| `SENT` | Push berhasil dikirim ke FCM |
| `FAILED` | Lihat `error_message` (credential, token, dll.) |

Cek `failed_jobs` jika job crash:

```sql
SELECT id, queue, failed_at, LEFT(exception, 200) AS error_preview
FROM failed_jobs
WHERE queue = 'notifications'
ORDER BY failed_at DESC
LIMIT 10;
```

---

### ✅ Level 4 — Uji dari UI: suntik poin manual

Ini menguji integrasi Fase 6 (`ManualPointInjectionService`).

1. Login backoffice sebagai staff yang boleh suntik poin.
2. Suntik poin ke member yang punya **token FCM asli** di `device_push_tokens`.
3. Pastikan worker `notifications` jalan.

**Yang diharapkan:**

| Target | Platform | Di mana dicek |
|--------|----------|---------------|
| Member | `MOBILE_APP_PUSH` | HP member + row `notifications` status `SENT` |
| Staff yang inject | `WEB_ADMIN_IN_APP` | Inbox admin di Filament (tanpa push FCM) |

Query cek:

```sql
-- Notifikasi member setelah suntik
SELECT title, body, platform, status, error_message, sent_at
FROM notifications
WHERE data_payload->>'$.type' = 'point_injection'
ORDER BY created_at DESC
LIMIT 5;

-- Konfirmasi staff
SELECT title, body, platform, status
FROM notifications
WHERE data_payload->>'$.type' = 'point_injection_confirmation'
ORDER BY created_at DESC
LIMIT 5;
```

**Kegagalan notifikasi tidak boleh mengubah hasil suntik poin** — mutasi tetap tercatat meski push gagal.

---

### ✅ Level 5 — Uji broadcast massal

1. Login sebagai `marketing` atau role dengan permission `Create:BroadcastNotification`.
2. Menu **Notifikasi** → Campaign → aksi **Kirim Broadcast**.
3. Pilih audience kecil (mis. 1 tier dengan sedikit member + token asli).

Cek campaign:

```sql
SELECT id, title, status, targeted_count, accepted_count, failed_count, error_message, created_at
FROM notification_campaigns
ORDER BY created_at DESC
LIMIT 5;
```

| `status` | Arti |
|----------|------|
| `PENDING` | Job belum jalan |
| `PROCESSING` | Sedang kirim multicast |
| `COMPLETED` | Selesai — `accepted_count` ≈ jumlah token valid |
| `FAILED` | Lihat `error_message` |

---

### ✅ Level 6 — Uji batch poin otomatis

Setelah proses batch sukses (`ProcessBatchService`), sistem otomatis memanggil `broadcastMass` dengan `criteria: { type: batch, batch_id }`.

```sql
SELECT id, criteria_json, status, targeted_count, accepted_count, failed_count
FROM notification_campaigns
WHERE JSON_EXTRACT(criteria_json, '$.type') = 'batch'
ORDER BY created_at DESC
LIMIT 5;
```

---

## Bagian 6 — Matriks troubleshooting

| Gejala | Penyebab umum | Solusi |
|--------|---------------|--------|
| `isConfigured()` = false | `FIREBASE_CREDENTIALS` kosong / path salah | Path absolut, `config:clear` |
| Notifikasi tetap `PENDING` | Worker tidak jalan / Redis mati | `php artisan horizon` atau `queue:work` |
| `FAILED`: credential tidak dikonfigurasi | Env belum terbaca worker | Restart worker setelah ubah `.env` |
| `FAILED`: tidak ada token push | Member belum daftar FCM | Daftar token via mobile API atau INSERT manual |
| `FAILED`: InvalidRegistration | Token dummy atau expired | Pakai token asli dari app |
| SenderId mismatch | Beda Firebase project | Samakan project mobile & server |
| Campaign `FAILED`, targeted > 0, accepted = 0 | Semua token invalid | Perbarui token di `device_push_tokens` |
| Suntik sukses, push tidak ada, status `SENT` | Notifikasi data-only / channel Android | Cek channel & permission notifikasi di app |
| Job di `failed_jobs` | Exception di worker | Baca kolom `exception` |

---

## Bagian 7 — Perintah cepat (copy-paste)

```bash
# Dari apps/backoffice-filament/

# 1. Cek credential
php artisan tinker --execute="var_export(app(\App\Services\Notification\FcmPushDriver::class)->isConfigured());"

# 2. Cek jumlah token aktif mobile
php artisan tinker --execute="echo \App\Models\DevicePushToken::query()->active()->where('platform','MOBILE')->count();"

# 3. Cek notifikasi terakhir
php artisan tinker --execute="\App\Models\Notification::query()->latest()->limit(3)->get(['platform','status','error_message'])->each(fn(\$n)=>print(\$n->platform->value.' '.\$n->status->value.' '.\$n->error_message.PHP_EOL));"

# 4. Cek campaign terakhir
php artisan tinker --execute="\App\Models\NotificationCampaign::query()->latest()->limit(3)->get(['status','targeted_count','accepted_count','failed_count','error_message'])->each(fn(\$c)=>print(\$c->status->value.' target='.\$c->targeted_count.' ok='.\$c->accepted_count.' fail='.\$c->failed_count.PHP_EOL));"

# 5. Dry-run prune (opsional)
php artisan notifications:prune --dry-run
```

---

## Bagian 8 — Kriteria "sudah berjalan dengan baik"

Centang semua sebelum anggap production-ready:

- [ ] `FcmPushDriver::isConfigured()` → `true`
- [ ] `sendToToken()` ke 1 token asli → `success = true` + notifikasi muncul di HP
- [ ] Worker memproses queue `notifications` (Horizon dashboard hijau / tidak ada job menumpuk)
- [ ] `notifyUser` → row `notifications` berubah `PENDING` → `SENT`
- [ ] Suntik poin manual → member `SENT`, staff dapat inbox in-app
- [ ] Broadcast dari UI → campaign `COMPLETED`, `accepted_count` masuk akal
- [ ] Tanpa credential: loyalty tetap sukses, push `FAILED` dengan pesan jelas (bukan error 500)
- [ ] `failed_jobs` queue `notifications` kosong atau jumlah wajar setelah retry

---

## Referensi kode

| File | Peran |
|------|-------|
| `config/firebase.php` | Sumber env `FIREBASE_CREDENTIALS` |
| `app/Services/Notification/FcmPushDriver.php` | Kirim FCM per-token & multicast |
| `app/Jobs/DeliverNotificationJob.php` | Personalized push |
| `app/Jobs/BroadcastNotificationJob.php` | Mass blast (chunk 500) |
| `app/Services/Notification/DevicePushTokenRegistry.php` | Resolve token dari DB |
| `app/Services/Loyalty/ManualPointInjectionService.php` | Trigger setelah suntik manual |
| `app/Services/Loyalty/ProcessBatchService.php` | Trigger broadcast setelah batch |

Dokumen terkait: [`notification_memo.md`](notification_memo.md) §15 (keputusan final) dan §18 (runbook operasional).
