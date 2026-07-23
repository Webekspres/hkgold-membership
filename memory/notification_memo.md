# Memo Requirement: Custom Notification System

> Dokumen ini merangkum keputusan desain, requirement produk, dan arah implementasi sistem notifikasi custom untuk `apps/backoffice-filament` (dan konsumsi mobile/web terkait).
>
> Disusun dari diskusi requirement (Juli 2026) + kondisi schema/kode awal yang sudah ada.
>
> **Status:** requirement disepakati sebagian besar; roadmap fase di **§17**; implementasi service/driver FCM/campaign/UI masih outstanding.

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

### Belum ada (to-build) — lihat roadmap §17

| Area | Fase |
|------|------|
| Tabel/model `notification_campaigns` | 0, 2 |
| `NotificationService` (`notifyUser` + `broadcastMass`) | 0 |
| Jobs queue `notifications` + Horizon supervisor | 0, 3 |
| FCM / WebPush drivers + env skeleton | 0, 3, 5 |
| Token storage (`device_push_tokens`) | 4 |
| Filament inbox admin + campaign UI | 1, 2 |
| Integrasi loyalty (inject/batch/redeem) | 6 |
| Shield / policy | 2, 7 |
| Feature tests + prune | 7 |

**Catatan kode existing:** `NotificationDispatcher` sudah punya `dispatchToPlatforms`, `markAsSent`, `markAsFailed` — belum enqueue job, belum `markAsRead`, belum dipanggil dari call-site bisnis. `TrustedDevice` belum simpan FCM token.

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

## 15. Pertanyaan Terbuka — Keputusan Final (Juli 2026)

| # | Pertanyaan | Keputusan |
|---|------------|-----------|
| 1 | Nama & kolom `notification_campaigns` | `CampaignStatus` enum (`PENDING`, `PROCESSING`, `COMPLETED`, `FAILED`); kolom `criteria_json`, `platforms` (JSON array), `targeted_count`, `accepted_count`, `failed_count` |
| 2 | Format audience filter | `criteria_json` v1: `{ "type": "all_active_members" }`, `{ "type": "tier", "tier": "..." }`, `{ "type": "batch", "batch_id": "..." }` |
| 3 | Blast setelah bulk injection | **Otomatis** — `ProcessBatchService::process()` memanggil `broadcastMass()` setelah commit, best-effort |
| 4 | Multi-platform per campaign | **1 row per blast**, field `platforms` JSON array; v1 blast UI hanya `MOBILE_APP_PUSH` |
| 5 | Package FCM | **`kreait/laravel-firebase`** via `FcmPushDriver`; env `FIREBASE_CREDENTIALS` |
| 6 | Penyimpanan token | **Tabel baru `device_push_tokens`**; register/revoke via Elysia API (out of scope backoffice) |
| 7 | Retensi inbox & campaign | **`notifications:prune --months=3`** — hapus notifikasi sudah dibaca + campaign `COMPLETED`/`FAILED` |

**Keputusan tambahan Fase 6:**
- Suntik manual: member dapat `MOBILE_APP_PUSH` + staff yang inject dapat `WEB_ADMIN_IN_APP`
- Firebase credentials belum tersedia di staging → driver graceful fail (`FAILED` + `error_message`), siap hidup saat env diisi

---

## 16. Definition of Done (keseluruhan v1)

Checklist global — detail per fase ada di **§17**.

- [x] Migrasi + model `notification_campaigns` (sync Prisma)
- [x] `NotificationService` dengan dua jalur jelas (`notifyUser` + `broadcastMass`)
- [x] Redis queue `notifications` + jobs + Horizon supervisor
- [x] Env skeleton kosong + driver FCM/WebPush graceful (no-op bila credential kosong)
- [x] Token push tersimpan & bisa di-resolve per user
- [x] Filament inbox admin (`WEB_ADMIN_IN_APP`) + badge unread
- [x] Filament campaign list/view + aksi broadcast (role terbatas)
- [x] Integrasi call-site loyalty (`ManualPointInjectionService` + `ProcessBatchService`)
- [x] Shield permission + `canAccess()` konsisten
- [ ] Minimal tests: personalized multi-platform → N rows; mass → 1 campaign + `targeted_count` (opsional, belum diminta)
- [x] Runbook worker & retry (§18)

---

## 17. Fase Pengembangan (Roadmap)

> Disusun Juli 2026 berdasarkan kondisi kode saat ini (`NotificationDispatcher` scaffold, belum ada campaign/jobs/UI/driver).
>
> **Prinsip urutan:** deliver value bertahap — inbox admin in-app bisa hidup **tanpa** FCM; driver push di fase belakang.
>
> **Referensi pola internal:** `ActivityLogger` → `PersistActivityLogJob` (`afterCommit`, queue dedicated, try/catch best-effort).

### Diagram dependensi

```text
Fase 0 (Foundation)
    ├── Fase 1 (In-app inbox Filament)     ← nilai admin cepat
    ├── Fase 2 (Campaign + UI blast)       ← butuh Fase 0
    └── Fase 3 (Queue worker + driver stub)
            ├── Fase 4 (Token storage)
            │       └── Fase 5 (FCM/WebPush production)
            └── Fase 6 (Integrasi loyalty)
                    └── Fase 7 (Hardening & ops)
```

---

### Fase 0 — Foundation domain & infrastruktur

**Tujuan:** Satu pintu masuk tulis notifikasi; schema campaign; queue siap; tanpa UI dulu.

| Item | Detail |
|------|--------|
| Prasyarat | Tabel `notifications` + model sudah ada |
| Scope | `apps/backoffice-filament/` + sync `packages/database/schema.prisma` |

**Deliverables**

1. **Prisma + migration** `notification_campaigns` (+ enum status campaign di Prisma & PHP).
2. **Model** `NotificationCampaign` + factory + seeder ringkas.
3. **`config/notifications.php`** — queue name (`notifications`), retry/backoff, key env FCM/WebPush (boleh kosong).
4. **`NotificationService`** (pintu masuk domain):
   - `notifyUser(User, title, body, platforms[], ?payload)` → generate `notification_key`, persist via dispatcher, enqueue `DeliverNotificationJob` per row push / skip enqueue untuk in-app yang sudah `SENT`.
   - `broadcastMass(title, body, audience criteria, platforms[])` → hitung `targeted_count`, create campaign `PENDING`, enqueue `BroadcastNotificationJob`.
   - Pola **`afterCommit`** + **try/catch** (jangan gagalkan transaksi bisnis) — salin dari `ActivityLogger`.
5. **Perkaya `NotificationDispatcher`:** `markAsRead()`, `markAllAsReadForUser()`, query unread count (`WEB_ADMIN_IN_APP` only).
6. **Jobs skeleton:** `DeliverNotificationJob`, `BroadcastNotificationJob` — `onQueue('notifications')`, `tries`/`backoff` selaras activity-log.
7. **Horizon:** supervisor `notifications-supervisor` di `config/horizon.php` (local + production).
8. **`.env.example`:** `NOTIFICATIONS_QUEUE`, `FCM_*`, `WEBPUSH_VAPID_*`.

**Definition of Done**

- [ ] `NotificationService::notifyUser()` dengan 2 platform → 2 row, unique `(user_id, notification_key, platform)`.
- [ ] `NotificationService::broadcastMass()` → 1 row campaign + `targeted_count` benar.
- [ ] Job ter-dispatch `afterCommit`; gagal enqueue hanya log warning.
- [ ] `php artisan horizon` memproses queue `notifications` (job skeleton boleh no-op).

**Estimasi:** kecil–sedang.

---

### Fase 1 — In-app inbox admin (Filament)

**Tujuan:** Admin panel punya inbox DB (`WEB_ADMIN_IN_APP`) — baca & tandai dibaca — **tanpa** FCM.

| Item | Detail |
|------|--------|
| Prasyarat | Fase 0 (`markAsRead`, unread count) |
| Catatan | **Bukan** Laravel default `notifications` morph table / Filament Echo — pakai model custom `App\Models\Notification`. |

**Deliverables**

1. **Badge unread** di panel header (Livewire polling atau render on navigate) — hanya `WEB_ADMIN_IN_APP` + `read_at IS NULL`.
2. **Halaman inbox** — pilih salah satu (putuskan saat implementasi, default **custom Page**):
   - **Opsi A (disarankan):** `NotificationInboxPage` — list read-only + filter belum dibaca, pola `MemberLookupPage` / `PointMutationResource` (list read-only).
   - **Opsi B:** `NotificationResource` list-only (tanpa create/edit) jika ingin RelationManager nanti.
3. **Aksi:** tandai dibaca (1 item), tandai semua dibaca — di `Actions/` + `getHeaderActions()`.
4. **Label UI:** Bahasa Indonesia (judul, empty state, notifikasi sukses).
5. **Akses:** semua user panel yang login; query **scoped** `user_id = auth()->id()` — bukan global inbox.

**Definition of Done**

- [ ] Seed `NotificationSeeder` tampil di inbox admin yang login.
- [ ] Badge berkurang setelah mark read.
- [ ] Tidak ada route create/edit notifikasi manual dari UI (notifikasi hanya dari service/event).

**Estimasi:** sedang.

---

### Fase 2 — Campaign mass blast (UI + orkestrasi)

**Tujuan:** Admin/marketing bisa trigger broadcast massal; rekapan angka di campaign; push masih boleh stub.

| Item | Detail |
|------|--------|
| Prasyarat | Fase 0 (campaign model + `broadcastMass`) |
| Referensi UI | `InjectManualPointAction` (wizard modal), `PointMutationResource` (list read-only + stat) |

**Deliverables**

1. **`NotificationCampaignResource`** — list + view (infolist angka: ditarget, diterima, gagal, status, pembuat, waktu).
2. **`BroadcastNotificationAction`** — wizard: judul, isi, platform, filter audience (tier / semua member aktif / batch id — format `criteria_json` dikunci di fase ini).
3. **`navigationGroup`:** usulan **`Notifikasi`** (baru) atau tanya user — jangan campur `Loyalty Point`.
4. **Shield:** permission `View:NotificationCampaignResource`, `Create:BroadcastNotification` (nama final saat generate Shield); `administrator` full; `super_admin` + `marketing` via permission eksplisit; `store_manager` read-only campaign (opsional).
5. **Stat card widget** (opsional): total campaign bulan ini, total ditarget.

**Keputusan yang harus dikunci di fase ini**

- Format `criteria_json` v1 (mis. `{ "type": "all_active_members" }`, `{ "type": "tier", "tier_id": "..." }`, `{ "type": "batch", "batch_id": "..." }`).
- Satu campaign row per operasi blast (multi-platform dalam 1 row vs 1 row per platform) — **default: 1 row, field `platforms` JSON array**.

**Definition of Done**

- [ ] Trigger blast dari UI → 1 campaign `PENDING` → job `BroadcastNotificationJob` jalan.
- [ ] `targeted_count` = hasil query audience saat submit (bukan dari Firebase).
- [ ] Role tanpa permission tidak melihat menu / tidak bisa trigger.

**Estimasi:** sedang.

---

### Fase 3 — Worker delivery & driver stub

**Tujuan:** Jalur push end-to-end di worker; credential kosong = graceful `FAILED` + log, bukan exception bisnis.

| Item | Detail |
|------|--------|
| Prasyarat | Fase 0 (jobs skeleton) |
| Referensi | `PersistActivityLogJob`, pola `failed_jobs` monitoring |

**Deliverables**

1. **Interface** `PushDriver` / `PushDeliveryResult` DTO.
2. **`FcmPushDriver`** — baca `config/notifications.php`; jika credential kosong → return failure message jelas, jangan throw.
3. **`WebPushDriver`** — sama (VAPID kosong → skip).
4. **`DeliverNotificationJob::handle()`** — resolve token user → kirim → `markAsSent` / `markAsFailed`; increment `attempt_count`.
5. **`BroadcastNotificationJob::handle()`** — update campaign `PROCESSING` → `COMPLETED`/`FAILED`; isi `accepted_count`/`failed_count` dari response driver (atau 0 jika stub).
6. **Pilih package** (keputusan §15.5): implementasi minimal di fase ini; bisa `kreait/laravel-firebase` atau channel FCM — **jangan** block Fase 1–2.

**Definition of Done**

- [ ] Personalized row `MOBILE_APP_PUSH` tanpa credential → `FAILED` + `error_message` terisi.
- [ ] Campaign blast tanpa credential → `FAILED` di campaign, bukan 500 di Filament.
- [ ] Retry job mengikuti `tries`/`backoff` Horizon.

**Estimasi:** sedang–besar (tergantung package).

---

### Fase 4 — Device push token storage

**Tujuan:** Personalized push bisa resolve token per user/device; siap konsumsi API mobile.

| Item | Detail |
|------|--------|
| Prasyarat | Fase 3 (driver butuh token) |
| Keputusan | §15.6 — **default usulan:** tabel baru `device_push_tokens` (jangan overload `trusted_devices` dulu) |

**Deliverables**

1. **Prisma + migration** `device_push_tokens`:
   - `user_id`, `device_uuid` (nullable), `platform` (`MOBILE`/`WEB`), `token`, `last_used_at`, `revoked_at`.
   - Unique `(user_id, token)` atau `(device_uuid, platform)`.
2. **Model + `DevicePushTokenRegistry` service:** register, refresh, revoke, `activeTokensForUser()`.
3. **Wire ke `DeliverNotificationJob`** — ambil token aktif; multi-device = chunk kirim.
4. **Dokumentasi kontrak API** (endpoint register/revoke di Elysia — **out of scope implementasi backoffice**, cukup catat di memo atau OpenAPI stub).

**Definition of Done**

- [ ] Token dummy di seeder → personalized push job memanggil driver dengan token tersebut.
- [ ] Token revoked tidak dipakai.

**Estimasi:** sedang.

---

### Fase 5 — FCM / WebPush production-ready

**Tujuan:** Credential terisi → push benar-benar terkirim; multicast/topic untuk blast.

| Item | Detail |
|------|--------|
| Prasyarat | Fase 3 + 4 |
| Lingkungan | Firebase project + VAPID keys di staging |

**Deliverables**

1. Isi env production/staging; dokumentasi setup di §18 runbook.
2. **Personalized:** FCM per device token; update `sent_at` per row.
3. **Mass:** FCM multicast batch (chunk 500) **atau** topic — pilih satu strategi v1 (disarankan **multicast dari query token** agar audience tetap dari DB, bukan Firebase CRM).
4. **Web push staf** (opsional v1): subscription flow di front-end staf — bisa ditunda sub-fase 5b.
5. Monitoring: alert jika queue `notifications` `failed_jobs` > threshold.

**Definition of Done**

- [ ] Test manual staging: suntik poin → member terima push (1 device).
- [ ] Test blast ke audience kecil → campaign `COMPLETED` + angka masuk akal.

**Estimasi:** sedang (bergantung akses Firebase).

---

### Fase 6 — Integrasi call-site bisnis

**Tujuan:** Notifikasi terpicu dari alur loyalty yang sudah ada — best-effort setelah commit.

| Item | Detail |
|------|--------|
| Prasyarat | Fase 0 minimal; Fase 1 untuk alert admin; Fase 5 untuk push member nyata |
| Referensi | `ManualPointInjectionService`, `ProcessBatchService` |

**Deliverables (prioritas)**

| Call-site | Jalur | Platform | Catatan |
|-----------|-------|----------|---------|
| `ManualPointInjectionService` | Personalized | `MOBILE_APP_PUSH` + opsional `WEB_ADMIN_IN_APP` ke staff yang inject | Body: poin + nominal |
| `ProcessBatchService` (batch sukses) | Mass **atau** manual trigger | `MOBILE_APP_PUSH` | Putuskan §15.3: auto blast vs tombol di `ViewPointInjectionBatch` |
| Redeem status change | Personalized | `MOBILE_APP_PUSH` | Tergantung Redeem Fase 2–7 |
| Alert operasional admin | Personalized | `WEB_ADMIN_IN_APP` | Fraud/anomaly nanti |

**Pola kode**

```php
// Di akhir transaksi bisnis — jangan di dalam DB::transaction throw path
try {
    app(NotificationService::class)->notifyUser(...);
} catch (Throwable $e) {
    Log::warning('Notifikasi gagal diantre.', [...]);
}
```

**Definition of Done**

- [ ] Suntik manual → member dapat row inbox/push (sesuai platform aktif).
- [ ] Kegagalan notifikasi tidak mengubah hasil suntik poin.
- [ ] Activity log tetap independen dari notifikasi.

**Estimasi:** kecil per call-site.

---

### Fase 7 — Hardening, testing, operasional

**Tujuan:** Production confidence — test, retensi, permission final, dokumentasi ops.

**Deliverables**

1. **Feature tests** (jika diminta user):
   - `notifyUser` multi-platform → N rows + job dispatched.
   - `broadcastMass` → 1 campaign, `targeted_count` correct.
   - Inbox mark-read scoped ke user.
2. **`notifications:prune`** command (opsional, pola `activity-log:prune`) — retensi inbox & campaign.
3. **Shield seeder** update idempotent di `ShieldRolesSeeder.php`.
4. **Resolve pertanyaan terbuka §15** — catat keputusan final di memo.
5. **Runbook §18** — worker, env, debug `failed_jobs`.

**Definition of Done**

- [ ] `vendor/bin/pint --dirty` bersih setelah perubahan PHP.
- [ ] Semua checklist §16 terpenuhi untuk scope v1.

**Estimasi:** kecil–sedang.

---

### Ringkasan fase & prioritas implementasi

| Fase | Nama | Nilai bisnis | Blokir FCM? |
|------|------|--------------|-------------|
| 0 | Foundation | Infrastruktur | Tidak |
| 1 | Inbox admin Filament | Tinggi (admin langsung pakai) | Tidak |
| 2 | Campaign + UI blast | Tinggi (marketing) | Tidak (angka + stub push) |
| 3 | Worker + driver stub | Transport siap | Tidak |
| 4 | Token storage | Prasyarat push nyata | Ya |
| 5 | FCM/WebPush prod | Push member/staf hidup | Ya |
| 6 | Integrasi loyalty | End-to-end produk | Sebagian |
| 7 | Hardening | Production | Tidak |

**Urutan disarankan untuk sesi coding:** `0 → 1 → 2 → 3 → 6 (in-app only) → 4 → 5 → 6 (push) → 7`.

---

## 18. Runbook Operasional

### Worker

```bash
# Dari apps/backoffice-filament/
php artisan horizon
# atau queue worker tunggal (dev):
php artisan queue:work redis --queue=notifications --tries=3
```

Pastikan Redis berjalan (`REDIS_HOST`, `REDIS_PORT` di `.env`).

### Setup Firebase (FCM)

1. Buka [Firebase Console](https://console.firebase.google.com/) → pilih project (atau buat baru).
2. **Project Settings** → tab **Service accounts** → **Generate new private key** → simpan JSON.
3. Letakkan file di server (mis. `storage/app/firebase/service-account.json`) — **jangan commit ke git**.
4. Isi `.env`:

```env
NOTIFICATIONS_QUEUE=notifications
FIREBASE_CREDENTIALS=/absolute/path/to/service-account.json
```

Alternatif: isi `FIREBASE_CREDENTIALS` dengan JSON inline (satu baris) jika deploy tidak memakai file.

5. Verifikasi driver terkonfigurasi:

```bash
php artisan tinker
>>> app(\App\Services\Notification\FcmPushDriver::class)->isConfigured()
# true = siap kirim push
```

6. Pastikan device token terdaftar di tabel `device_push_tokens` (via Elysia API mobile).

### Env Web Push (opsional, staf — sub-fase 5b)

```env
WEBPUSH_VAPID_PUBLIC_KEY=
WEBPUSH_VAPID_PRIVATE_KEY=
WEBPUSH_VAPID_SUBJECT=mailto:admin@example.com
```

Web Push masih stub di `WebPushDriver` — tidak wajib untuk v1.

### Strategi multicast (mass blast)

- `BroadcastNotificationJob` resolve token dari DB via `DevicePushTokenRegistry::tokensForCriteria()`.
- Kirim via `FcmPushDriver::sendMulticast()` dengan **chunk 500** token per request.
- Audience tetap dari DB (`criteria_json`), bukan Firebase topic/CRM.

### Retensi data

```bash
# Lihat jumlah yang akan dihapus (default 3 bulan)
php artisan notifications:prune --dry-run

# Hapus notifikasi sudah dibaca + campaign COMPLETED/FAILED
php artisan notifications:prune --months=3
```

Jadwalkan via cron/scheduler di production (mis. mingguan).

### Monitoring & alert

**Threshold disarankan:** `failed_jobs` pada queue `notifications` > 10 dalam 1 jam → investigasi.

```sql
-- Cek failed jobs notifikasi
SELECT id, queue, failed_at, exception
FROM failed_jobs
WHERE queue = 'notifications'
ORDER BY failed_at DESC
LIMIT 20;
```

**Row personalized** (`notifications` table):

| Kolom | Arti |
|-------|------|
| `status` | `PENDING`, `SENT`, `FAILED` |
| `error_message` | Pesan gagal (credential kosong, token tidak ada, FCM error) |
| `attempt_count` | Jumlah percobaan kirim |
| `sent_at` | Waktu push berhasil |

**Campaign** (`notification_campaigns`):

| Kolom | Arti |
|-------|------|
| `status` | `PENDING` → `PROCESSING` → `COMPLETED` / `FAILED` |
| `targeted_count` | Audience dari query DB saat submit |
| `accepted_count` / `failed_count` | Hasil multicast FCM |

### Debug alur end-to-end

1. **Suntik manual poin** → cek row `notifications` untuk member (`MOBILE_APP_PUSH`) dan staff (`WEB_ADMIN_IN_APP`).
2. **Proses batch** → cek 1 row baru di `notification_campaigns` dengan `criteria_json.type = batch`.
3. Jalankan worker → cek `status` campaign / row notifikasi berubah.
4. Tanpa credential: expect `FAILED` + `error_message` jelas, **bukan** 500 di UI loyalty.

### Test manual staging (setelah credential diisi)

- [ ] Suntik poin → member terima push (1 device dengan token di `device_push_tokens`).
- [ ] Blast ke audience kecil → campaign `COMPLETED` + `accepted_count` masuk akal.

---

## 19. Ringkas One-Liner

**Personalized = row inbox per user × platform + token push via Redis.**  
**Mass = push-only Firebase + 1 campaign record berisi angka target (tanpa kota, tanpa ribuan inbox rows).**
