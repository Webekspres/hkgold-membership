# Memo Requirement: Custom Notification System

> Dokumen ini merangkum keputusan desain, requirement produk, dan arah implementasi sistem notifikasi custom untuk `apps/backoffice-filament` (dan konsumsi mobile/web terkait).
>
> Disusun dari diskusi requirement (Juli 2026) + kondisi schema/kode awal yang sudah ada.
>
> **Status:** requirement disepakati sebagian besar; implementasi lengkap service/driver FCM/campaign masih outstanding.

---

## 1. Ringkasan Keputusan

| Aspek | Keputusan |
|-------|-----------|
| Model store personalized | **1 platform = 1 row** di tabel `notifications` (kirim ke 2 platform → 2 row) |
| Platform didukung | `WEB_BROWSER_PUSH`, `MOBILE_APP_PUSH`, `WEB_ADMIN_IN_APP` |
| Dedup personalized | Unique key: `(user_id, notification_key, platform)` |
| `notification_key` | Generate UUID di aplikasi (per event personalized) |
| Delivery status | `PENDING` / `SENT` / `FAILED` + `sent_at`, `failed_at`, `error_message`, `attempt_count` |
| `read_at` | Hanya relevan untuk `WEB_ADMIN_IN_APP`; push channel boleh `null` |
| Strategy kirim mobile | **Hybrid:** token per device (personalized) + channel massal/topic (blast) |
| Persistensi | **Hybrid:** personalized → record DB inbox; blast massal → **push-only** + **campaign angka** (bukan ribuan row inbox) |
| Statistik blast | **Opsi 1 (angka saja):** campaign log berisi total target / ringkasan kirim — **tanpa** track kota |
| Firebase sebagai sumber kota | **Tidak** — Firebase tidak dipakai untuk geografi business; kota tidak dilacak di v1 |
| Queue transport | **Redis dedicated** queue `notifications` (konsisten dengan `activity-log` / `bulk-injection`) |
| Env provider | Siapkan skeleton credential **kosong** dulu (FCM/WebPush) untuk diisi kemudian |
| Transaksi bisnis vs notifikasi | **Best-effort / afterCommit** — gagal notifikasi tidak boleh rollback proses poin/redeem/dsb. |
| Library arah | Laravel Notifications sebagai orkestrasi opsional; persist custom; FCM/WebPush via package channel / Firebase SDK — detail package final saat implementasi driver |

---

## 2. Goal Produk

1. Mengirim notifikasi ke staf/member lewat salah satu atau lebih platform:
   - Web push browser (staf)
   - Mobile app push (member / React Native)
   - In-app database notification (khusus admin Filament / inbox)
2. Mendukung dua pola konten:
   - **Personalized** (beda body per user, mis. “Poin kamu +120”)
   - **Same-content blast** (pesan sama ke banyak orang)
3. Menjaga aplikasi **ringan** pada request HTTP/transaksi bisnis:
   - Persist + enqueue cepat
   - Push/heavy I/O di worker Redis
4. Memberikan rekapan angka untuk blast massal (berapa yang ditarget / ringkasan status) tanpa menyimpan inbox per-user untuk blast.

---

## 3. Dua Jalur Notifikasi (Wajib Terpisah)

Sistem **bukan** satu tabel untuk semua kasus. Ada dua jalur:

### 3.1 Personalized Notification

| Item | Requirement |
|------|-------------|
| Kapan | Event penting/personal: suntik poin 1 member, status redeem, approval, dll. |
| Storage | Tabel `notifications` — **1 row per user per platform** |
| Grouping | Semua row platform yang sama eventnya share `notification_key` |
| Inbox | Ya — history & `read_at` (khusus in-app) |
| Kirim push | FCM **token** (dan/atau WebPush subscription) |
| Statistik | Hitung dari rows (`SENT`/`FAILED`/`PENDING`) per `notification_key` atau per user |

### 3.2 Mass Notification (Blast)

| Item | Requirement |
|------|-------------|
| Kapan | Broadcast massal (promo, announcement, blast setelah poin massal sukses dengan pesan generik, dll.) |
| Storage inbox | **Tidak** membuat ribuan row di `notifications` |
| Storage rekap | **1 record campaign** (entitas baru, mis. `notification_campaigns`) berisi angka |
| Kirim push | Firebase (FCM topic dan/atau multicast batch) |
| Statistik | Angka di campaign: targeted count, accepted/failed ringkas, metadata filter — **tanpa breakdown kota** |
| Inbox user | Tidak wajib muncul di in-app history untuk blast |

### 3.3 Kriteria memilih jalur

| Ciri event | Jalur |
|------------|-------|
| Body/payload beda per user | Personalized |
| Perlu history/read state per user | Personalized |
| Pesan sama ke audience besar + push saja cukup | Mass |
| Perlu rekapan “dikirim ke berapa orang” tanpa inbox | Mass (+ campaign angka) |

Jika satu operasional punya keduanya (mis. batch poin):  
- personal detail per member → personalized rows (opsional selektif), **atau**  
- satu blast generik → mass campaign.  
**Tidak otomatis dual-write** kecuali requirement use-case eksplisit.

---

## 4. Platform

Enum `NotificationPlatform`:

| Value | Arti | Catatan |
|-------|------|---------|
| `WEB_BROWSER_PUSH` | Web Push (VAPID/FCM web) untuk browser staf | Perlu subscription/token browser |
| `MOBILE_APP_PUSH` | FCM/APNS via Firebase untuk app member | Token device atau topic |
| `WEB_ADMIN_IN_APP` | Inbox/database notification admin (Filament-oriented) | Persist DB; `read_at` dipakai |

**Aturan multi-platform:**

- Satu event personalized boleh target 1..N platform.
- Setiap platform = **row terpisah**.
- Contoh: user A + in-app + mobile = 2 row dengan `notification_key` sama.

---

## 5. Schema & Data Model

### 5.1 Tabel `notifications` (personalized / per-delivery row)

Sudah ada di Prisma & migration:

| Kolom | Requirement |
|-------|-------------|
| `id` | UUID PK |
| `user_id` | FK users, nullable (edge case system), cascade delete |
| `notification_key` | UUID grouping event; generate di app |
| `title` | varchar(150) |
| `body` | text |
| `platform` | enum string platform |
| `status` | `PENDING` \| `SENT` \| `FAILED` (default `PENDING`) |
| `data_payload` | JSON nullable (deep link, reference id, screen, dll.) |
| `read_at` | datetime nullable — **hanya meaningful untuk in-app** |
| `sent_at` | datetime nullable |
| `failed_at` | datetime nullable |
| `error_message` | varchar(500) nullable |
| `attempt_count` | unsigned int default 0 |
| timestamps | `created_at`, `updated_at` |

**Index/unique:**

- `(user_id, read_at)`
- `(user_id, platform, created_at)`
- `(status, created_at)`
- **UNIQUE** `(user_id, notification_key, platform)` — dedup retry/race

**Status semantics (draft rule):**

| Platform | Insert awal (contoh) | Update setelah push |
|----------|----------------------|---------------------|
| `WEB_ADMIN_IN_APP` | Boleh langsung `SENT` (+ `sent_at`) setelah insert sukses | `read_at` saat user baca |
| Push channels | `PENDING` | Worker → `SENT` / `FAILED` |

### 5.2 Tabel campaign (mass) — **belum diimplementasi, wajib untuk v1 mass**

Nama usulan: `notification_campaigns`

Field minimum yang disepakati (angka saja, tanpa kota):

| Field | Tujuan |
|-------|--------|
| `id` | UUID |
| `title` / `body` | Konten blast (atau template key) |
| `platform`(s) | Platform target (biasanya mobile push; bisa multi) |
| `audience_filter` / `criteria_json` | Ringkasan filter audience (tier, cabang, batch id, dsb.) |
| `targeted_count` | Jumlah orang yang ditarget saat blast disusun (dari query DB) |
| `accepted_count` / `failed_count` (opsional) | Ringkasan dari response FCM / worker |
| `status` | `PENDING` / `PROCESSING` / `COMPLETED` / `FAILED` (final naming saat implementasi) |
| `provider_message_id` | ID dari FCM bila ada |
| `error_message` | Ringkas gagal campaign |
| `created_by` | user admin (opsional) |
| timestamps | |

**Bukan bagian v1:** breakdown per kota, snapshot daftar member penuh, drill-down member (kecuali diputus nanti).

### 5.3 Device / token storage

Saat ini `TrustedDevice` **belum** menyimpan FCM token (`device_uuid`, platform approval saja).  
**Requirement follow-up (sebelum push production):**

- Tempat persist FCM/WebPush token per user/device (extend `trusted_devices` atau tabel `device_push_tokens`).
- Lifecycle: register, refresh, revoke on logout/uninstall.
- Mapping token → user untuk jalur personalized.

---

## 6. Strategi Pengiriman (FCM Hybrid)

### 6.1 Token path (personalized)

- Ambil token aktif milik user.
- Kirim via worker (chunk jika multi-device).
- Update status per row `notifications`.
- Cocok untuk personalized & audit per user.

### 6.2 Mass / topic path (blast)

- Audience ditentukan dari **query DB aplikasi** (target count).
- Kirim FCM topic atau multicast batch — Firebase tonggak heavy lift.
- Simpan **satu campaign** dengan `targeted_count` (+ ringkasan provider bila ada).
- **Tidak** insert inbox row per member.

### 6.3 Yang tidak bergantung ke Firebase

- Definisi audience membership
- Angka `targeted_count`
- Inbox personalized
- `read_at` admin

Firebase = transport push + (opsional) cobertura teknis delivery/open — **bukan** CRM kota/cabang.

---

## 7. Queue, Redis, dan “Aplikasi Ringan”

### 7.1 Keputusan transport

- Queue Redis **dedicated**: `notifications`
- Dispatch job **`afterCommit`**
- Worker terpisah dari `bulk-injection` dan `activity-log`
- Horizon supervisor dapat menambah `notifications` (timeout pendek seperti activity-log, kecuali blast chunk butuh lebih panjang)

### 7.2 Definisi “ringan”

Request / transaksi bisnis hanya:

1. Commit data bisnis
2. Persist row personalized **atau** create campaign record
3. Enqueue job

**Bukan** synchronous loop FCM 1..N di request Filament/Loyalty.

### 7.3 Blast volume

- Insert personalized massal (jika suatu hari dibutuhkan) harus chunked.
- Untuk blast yang dipilih sekarang: **hindari** N row inbox; campaign 1 row + FCM batch/topic.

### 7.4 Env skeleton (isi kemudian)

Nilai boleh kosong di awal; service harus **graceful**:

- Jika credential kosong → jangan crash bisnis; tandai `FAILED`/`PENDING` dengan error jelas atau skip push dengan log warning.

Contoh key (final naming saat implementasi):

```env
NOTIFICATIONS_QUEUE=notifications

FCM_PROJECT_ID=
FCM_CREDENTIALS_PATH=
# atau FCM_CREDENTIALS_JSON=

WEBPUSH_VAPID_PUBLIC_KEY=
WEBPUSH_VAPID_PRIVATE_KEY=
WEBPUSH_VAPID_SUBJECT=
```

---

## 8. Service Architecture (Target)

Satu pintu masuk domain, dua method utama (nama final bebas):

```text
NotificationService
├── notifyUser(user, title, body, platforms[], payload?)  // personalized
│     → generate notification_key
│     → persist 1 row / platform
│     → enqueue DeliverNotificationJob(s) afterCommit
│
└── broadcastMass(title, body, audienceQuery|filter, platforms[])  // mass
      → hitung targeted_count dari DB
      → create notification_campaigns
      → enqueue BroadcastNotificationJob afterCommit
      → FCM topic/multicast; update angka campaign
```

Komponen pendukung:

| Komponen | Tanggung jawab |
|----------|----------------|
| `NotificationDispatcher` (sudah ada, perlu diperkaya) | Persist personalized + status helpers |
| `DeliverNotificationJob` | Kirim 1 row push / retry |
| `BroadcastNotificationJob` | Kirim mass + update campaign counts |
| FCM Driver / WebPush Driver | Abstraksi provider; no-op/safe bila env kosong |
| Mark as read API/Action | Hanya in-app rows |

**Best-effort:** exception transport → `markAsFailed` / campaign failed; jangan throw ke transaksi bisnis.

---

## 9. Statistik — Masa Blast (Opsi 1 Angka Saja)

Disepakati:

- Simpan rekap di **campaign record**
- Field fokus: **berapa orang ditarget** (+ ringkasan sukses/gagal provider jika tersedia)
- **Tidak** ada track kota / breakdown geografi di v1

Label UI sebaiknya jelas:

- **Ditarget** = hasil query audience DB saat blast
- **Diterima provider** (opsional) = FCM accepted
- **Dibuka** (fase nanti, butuh Analytics di app) — out of scope v1 kecuali disepakati ulang

---

## 10. Use Case Prioritas (Draft)

| Use case | Jalur | Platform tipikal |
|----------|-------|------------------|
| Suntik poin 1 member | Personalized | Mobile + opsional in-app |
| Batch poin sukses → broadcast generik “poin telah diproses” | Mass | Mobile topic/multicast |
| Batch poin sukses → pesan beda per member | Personalized (berat; chunk + Redis) | Mobile token |
| Redeem status ke member | Personalized | Mobile |
| Alert admin Filament | Personalized in-app | `WEB_ADMIN_IN_APP` |
| Promo nasional same content | Mass | Mobile (± web push staf) |

---

## 11. Authorization & Akses (Draft)

| Area | Usulan |
|------|--------|
| Inbox admin | User yang login panel; hanya notifikasi miliknya (`user_id`) |
| Campaign list / stats | Role admin/marketing (Shield policy nanti) |
| Trigger broadcast | Role yang berwenang + audit siapa yang trigger |
| Member mobile | Hanya notifikasi personalized milik user |

Detail permission Shield belum final — dicatat sebagai tugas implementasi UI.

---

## 12. Non-Goals (v1)

- Breakdown statistik per kota/cabang
- Jadikan Firebase sources of truth untuk audience CRM
- Sync full Spatie / Laravel default `notifications` morph table sebagai pengganti schema custom
- Delivery guarantee 100% (push tetap best-effort)
- Real-time open tracking lengkap tanpa instrumentation mobile
- Mengganti OTP WhatsApp / channel non-push lain

---

## 13. Kondisi Kode Saat Ini (Snapshot)

### Sudah ada

| Artefak | Lokasi |
|---------|--------|
| Prisma `Notification` + enums platform/status | `packages/database/schema.prisma` |
| Migration create `notifications` (skema final) | `database/migrations/2026_06_24_100033_create_notifications_table.php` |
| Enums PHP | `App\Enums\NotificationPlatform`, `NotificationDeliveryStatus` |
| Model `Notification` + relasi `User::notifications()` | `app/Models/...` |
| Factory + `NotificationSeeder` | `database/factories`, `database/seeders` |
| Scaffold `NotificationDispatcher` | `app/Services/Notification/NotificationDispatcher.php` |

### Belum ada (to-build)

- Tabel/model `notification_campaigns`
- `NotificationService` full (notifyUser + broadcastMass)
- Jobs queue `notifications` + Horizon/dev worker entry
- FCM / WebPush drivers + env skeleton
- Token storage (FCM token column/table)
- Filament UI inbox / campaign stats
- Integrasi call-site di loyalty (inject/batch/redeem)
- Policy / Shield resources
- Feature tests

---

## 14. Prinsip Kualitas yang Mengikat

1. **Satu pintu tulis domain** lewat service — hindari `Notification::create()` tersebar tanpa kebijakan jalur.
2. **afterCommit + best-effort** untuk side-effect push.
3. **Dedup** personalized via unique `(user_id, notification_key, platform)`.
4. **Jangan** blast massal ke ribuan row inbox tanpa keputusan produk eksplisit.
5. **Campaign angka** wajib jika blast push-only agar bisnis tetap punya rekapan.
6. **Env kosong ≠ exception bisnis** — degrade dengan status/log.
7. Selaraskan naming status & error dengan pola activity-log (monitoring `failed_jobs` queue `notifications`).

---

## 15. Pertanyaan Terbuka (belum mengunci implementasi detail)

1. Nama final & kolom tepat `notification_campaigns` (status enum naming).
2. Audience filter format (`criteria_json` vs kolom eksplisit batch_id/tier).
3. Apakah blast setelah bulk injection otomatis atau manual trigger admin.
4. Apakah satu blast boleh multi-platform dalam 1 campaign row atau 1 campaign per platform.
5. Package FCM tepat (`laravel-notification-channels/fcm` vs `kreait/laravel-firebase`).
6. Penyimpanan token: extend `trusted_devices` vs tabel baru.
7. Retensi row `notifications` & campaign (perlu prune seperti activity-log?).

---

## 16. Definition of Done (fase berikutnya)

- [ ] Migrasi + model campaign angka
- [ ] `NotificationService` dengan dua jalur jelas
- [ ] Redis queue `notifications` + jobs + env skeleton kosong
- [ ] Driver FCM/WebPush stub (no-op / pending bila credential kosong)
- [ ] Token registration plan (schema update) terdokumentasi atau diimplementasi
- [ ] Minimal tests: personalized multi-platform creates N rows; mass creates 1 campaign + targeted_count
- [ ] Runbook singkat worker & retry di doc ini atau AGENTS

---

## 17. Ringkas One-Liner

**Personalized = row inbox per user × platform + token push via Redis.**  
**Mass = push-only Firebase + 1 campaign record berisi angka target (tanpa kota, tanpa ribuan inbox rows).**
