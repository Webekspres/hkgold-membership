# Rencana Implementasi — Fitur Redeem Point

Referensi bisnis/pipeline: `memory/flow_redeem_point.md`.  
Schema: `packages/database/schema.prisma`.

---

## Keputusan desain (locked)

| Keputusan                                               | Nilai                                                                       |
| ------------------------------------------------------- | --------------------------------------------------------------------------- |
| Durasi expired token                                    | **30 menit** (`REDEEM_TOKEN_EXPIRY_MINUTES`)                                |
| Durasi expired OTP                                      | **5 menit** (`REDEEM_OTP_EXPIRY_MINUTES`)                                   |
| Format `token_code`                                     | 10 char alfanumerik uppercase, **tanpa prefix/dash** (contoh: `X7R92QK3TM`) |
| OTP WhatsApp                                            | Full Fonnte dari awal — hanya lewat `api-elysia`                            |
| `PointMutation`                                         | Ditulis **saat konfirmasi kasir** (bersama invoice), bukan saat reservasi   |
| Token expired tanpa klaim                               | **Termasuk scope** — job release + refund poin + `held_stock -= 1`          |
| Cabang kasir                                            | Hanya boleh konfirmasi token dengan `branch_id` = cabang staf login         |
| Refund darurat pasca-invoice (`RedeemStatus::REFUNDED`) | **Di luar MVP** — dijadwalkan setelah klaim normal jalan                    |

### Pembagian data (kenapa tidak semua HTTP)

`api-elysia` dan `backoffice-filament` memakai **MySQL yang sama**. Prisma & Eloquent = dua ORM, satu DB.

- **api-elysia** — alur mobile: buat `RedeemToken` (+ potong poin + naikkan `held_stock`); baca riwayat `RedeemInvoice`; **satu-satunya** yang bicara ke Fonnte (`/internal/otp/*`).
- **backoffice-filament** — alur kasir: query/update token/stok/invoice **langsung DB**; HTTP ke api-elysia **hanya untuk OTP**; job release expired.
- Internal OTP dilindungi header `X-Internal-Secret` = `MOBILE_API_INTERNAL_SECRET` (sama di `dev_backend` + `dev_backoffice`).

### Status persiapan (sudah dikerjakan)

| Item                                                                      | Status                                                       |
| ------------------------------------------------------------------------- | ------------------------------------------------------------ |
| Dependensi `qrcode` + `ioredis` (api-elysia)                              | ✅                                                           |
| Dependensi `simplesoftwareio/simple-qrcode` + `html5-qrcode` (backoffice) | ✅                                                           |
| Template env Fonnte / Redis / `MOBILE_API_*` / `REDEEM_*`                 | ✅ (`REDEEM_TOKEN_EXPIRY_MINUTES=30`)                         |
| `config/redeem.php` (default expiry 30)                                   | ✅                                                           |
| Secret Doppler (`FONNTE_TOKEN`, Redis, `MOBILE_API_*`, expiry 30)         | ✅ diisi user                                                |
| Model + factory + seeder `RedeemToken` (Laravel)                          | ✅ Fase 0                                                    |
| Kontrak DTO mobile↔API (§0.3)                                           | ✅ dokumen saja — file types di Fase 1                       |
| Kode bisnis redeem / OTP / Filament wizard / wire mobile                  | ❌ belum (Fase 1+)                                           |

---

## Fase 0 — Persiapan lintas platform

### 0.1 Koreksi env token expiry → 30 — ✅ selesai

- Template `.env.example` (api + backoffice) + `.env.testing` + default `config/redeem.php` = `30`
- Doppler diset manual oleh user

### 0.2 Model Eloquent `RedeemToken` — ✅ selesai

- `app/Models/RedeemToken.php` + factory + seeder (campuran available/used/expired)
- Scope `available()`, tanpa `updated_at` (sesuai Prisma)
- Relasi inverse `redeemTokens()` di Member / Reward / Branch

### 0.3 Kontrak DTO (mobile ↔ api-elysia) — ✅ dokumen saja

Tidak ada file types di Fase 0. Implementasi TypeScript di `src/modules/redeem/types/` baru di **Fase 1**.

```ts
// POST /api/redeem/token → 201
{ success: true, message: string, data: {
  redeemId: string;       // = RedeemToken.id
  tokenCode: string;      // 10 char A-Z0-9, tanpa prefix
  heldPoints: number;
  expiresAt: string;      // ISO — now + REDEEM_TOKEN_EXPIRY_MINUTES (30)
  reward: { sku: string; name: string; imageUrl: string | null };
  branch: { id: number; name: string; address: string };
}}

// GET /api/redeem/active → 200 (data: null jika tidak ada token available)
// GET /api/redeem/history → 200 paginated dari redeem_invoices (bukan tokens)
// GET /api/redeem/history/:id → 200 detail invoice milik member
```

Error bisnis (HTTP 4xx + `message` jelas): `INSUFFICIENT_POINTS`, `INSUFFICIENT_STOCK`, `MEMBER_SUSPENDED`, `REWARD_NOT_ACTIVE`, `BRANCH_STOCK_MISSING`.

---

## Fase 1 — `apps/api-elysia`: modul redeem (reservasi)

Folder: `apps/api-elysia/src/modules/redeem/` (pola `reward/`).

### 1.1–1.2 Types & interface

`CreateRedeemTokenRequest`, response DTO, `RedeemErrorCode`, interface service: `createRedeemToken`, `getActiveRedeemToken`, `getRedeemHistory`, `getRedeemHistoryById`.

### 1.3 `services/redeem.service.ts` — `createRedeemToken`

```
db.$transaction(async (tx) => {
  reward = find reward; guard isActive + window startAt/endAt
  stock = find RewardBranchStock(rewardId, branchId); guard exists
  available = actualStock - heldStock; guard available >= 1
  member = find; guard !isSuspended; guard pointBalance >= pointsRequired

  tokenCode = generateUniqueTokenCode() // 10 char A-Z0-9 uppercase
  update member.pointBalance -= pointsRequired
  update stock.heldStock += 1
  create RedeemToken { heldPoints, isUsed:false, expiredAt: now+30m }
  // TIDAK create PointMutation / RedeemInvoice di sini
  return token + join reward/branch/media
})
```

`getActiveRedeemToken`: `isUsed=false && expiredAt>now`, order `createdAt desc`, max satu yang ditampilkan (jika ada >1 legacy, ambil terbaru).

`getRedeemHistory`: dari `RedeemInvoice` milik member, cursor pagination (pola reward).

### 1.4 Routes

- `POST /api/redeem/token` — `requireActiveUser` + `requireNotSuspended`
- `GET /api/redeem/active`, `GET /api/redeem/history`, `GET /api/redeem/history/:id`
- Register di `src/index.ts`

### 1.5 Test

`__tests__/redeem-token-creation.test.ts`: happy path + poin kurang + stok habis + suspended + reward nonaktif.

---

## Fase 2 — `apps/api-elysia`: OTP & Fonnte (internal)

Folder: `apps/api-elysia/src/modules/otp/`.

### 2.1 `fonnte.service.ts`

`sendWhatsappMessage(phone, message)` → `POST ${FONNTE_BASE_URL}/send` + `Authorization: FONNTE_TOKEN`.  
Normalisasi phone: pastikan format yang diterima Fonnte (biasanya `62…` tanpa `+`).

### 2.2 `otp.service.ts` — hybrid Redis DB2 + `otp_verifications`

- `generateOtp(identifier, type=REDEEM_VALIDATION)` → Redis EX + INSERT DB + kirim WA
- `verifyOtp` → Redis hit dulu; fallback DB row unused & belum expired → mark `is_used`
- Rate-limit sederhana (opsional MVP): max N kirim OTP / token / 5 menit (Redis counter)

### 2.3 `middleware/internal-auth.middleware.ts`

Header `X-Internal-Secret` === `MOBILE_API_INTERNAL_SECRET`, else 401.

### 2.4 Routes `/internal/otp/send` + `/internal/otp/verify`

Register terpisah; **jangan** di-mount di balik JWT member.

### 2.5 Test

Mock `fetch` Fonnte; Redis+DB konsisten; fallback DB saat Redis key hilang.

---

## Fase 3 — `apps/backoffice-filament`: resource dasar `RedeemToken`

Folder: `app/Filament/Resources/RedeemTokens/` (split seperti `Members/`).

| File                                                | Isi                                                                   |
| --------------------------------------------------- | --------------------------------------------------------------------- |
| `RedeemTokenResource.php`                           | Group `Redeem Poin`; label **Antrean Kupon**; `canCreate=false`       |
| `Schemas/RedeemTokenInfolist.php`                   | Member, HP, reward, cabang, token, held points, expired, badge status |
| `Tables/RedeemTokensTable.php`                      | Kolom + filter cabang/status/expired; default sort terbaru            |
| `Pages/ListRedeemTokens.php`, `ViewRedeemToken.php` | Tanpa Create/Edit                                                     |

Policy/Shield: hanya role staf cabang + admin pusat (ikuti pola resource sejenis).

---

## Fase 4 — `apps/backoffice-filament`: wizard verifikasi & konfirmasi

### 4.1 `app/Services/Redeem/FonnteOtpClient.php`

HTTP ke `{config('redeem.mobile_api.url')}/internal/otp/*` + header secret.

### 4.2 `VerifyRedeemTokenAction` (header action di List)

Mirror wizard `InjectManualPointAction` — 3 step:

1. **Token** — input teks **atau** scan (`html5-qrcode`); query `available()` + `branch_id = auth staff branch` (superadmin/administrator boleh semua cabang); tampil detail KTP-check.
2. **Kirim OTP** — `FonnteOtpClient::send(member.phone)`.
3. **Konfirmasi** — input OTP → verify → `RedeemConfirmationService::confirm`.

### 4.3 `RedeemConfirmationService`

```php
DB::transaction(function () {
  $token->refresh();
  abort_unless(!$token->is_used && $token->expired_at->isFuture(), ...);
  abort_unless(branchAllowed(...), ...);

  $token->update(['is_used' => true]);

  $stock = RewardBranchStock::...->lockForUpdate()->firstOrFail();
  abort_if($stock->actual_stock < 1 || $stock->held_stock < 1, ...);
  $stock->decrement('actual_stock');
  $stock->decrement('held_stock');

  $invoice = RedeemInvoice::create([..., 'points_redeemed' => $token->held_points, 'status' => COMPLETED]);

  PointMutation::create([
    'member_id' => $token->member_id,
    'branch_id' => $token->branch_id,
    'reference_id' => $invoice->invoice_number, // atau invoice id
    'points_redeemed' => $token->held_points,
    'balance_snapshot' => $member->fresh()->point_balance,
    'transaction_date' => now(),
    // transaction_type_id: pakai type key REDEEM jika sudah ada di master; jika belum → seeder tipis di Fase 0/4
  ]);

  ActivityLog::... auditable RedeemInvoice
});
```

Format invoice: `INV-{branchCode}-{YYYYMMDD}-{seq4}`.

### 4.4 Pastikan `TransactionType` dengan `type_key=REDEEM` ada (seeder/cek master) sebelum write `PointMutation`.

### 4.5 Test

`tests/Feature/Redeem/RedeemConfirmationServiceTest.php`: happy path, double confirm, expired, salah cabang, stok inkonsisten.

---

## Fase 5 — `apps/backoffice-filament`: job release token expired

**Termasuk scope MVP** (karena token hanya 30 menit — tanpa ini poin & `held_stock` macet).

### 5.1 `app/Services/Redeem/ReleaseExpiredRedeemTokenService.php`

Per token expired unused (batch kecil + `lockForUpdate`):

- `point_balance += held_points`
- `held_stock -= 1` (guard `held_stock > 0`)
- tulis activity log `redeem_token_expired_release`
- **jangan** set `is_used=true` (token memang tidak dipakai); cukup expired alami
- idempotent: skip jika `held_stock` sudah tidak mencerminkan hold / pakai activity log marker

### 5.2 `app/Console/Commands/ReleaseExpiredRedeemTokensCommand.php`

Signature: `redeem:release-expired-tokens`. Jadwalkan di `routes/console.php` / scheduler (mis. tiap 5 menit).

### 5.3 Test

Token expired → poin balik, held turun, tidak double-refund pada run kedua.

---

## Fase 6 — `apps/mobile-app`: wire ke API

### 6.1 `src/services/redeem.ts`

`createRedeemToken`, `getActiveRedeem`, `getRedeemHistory`, `getRedeemHistoryById` via `apiClient` + `ApiEnvelope`.

### 6.2 Mapping status

API `COMPLETED`/`REFUNDED` → label UI `selesai`/`ditolak` di **satu** helper (`src/lib/format/`…).

### 6.3 `reward-redeem-dialog.tsx` + `reward/[sku].tsx`

Ganti dummy → POST create → navigate `/(tabs)/card/redeem-qr`; toast error kode bisnis.

### 6.4 `active-redeem.ts` + `card/redeem-qr.tsx` + `card/index.tsx`

Ganti mock; empty state jika `data=null`; un-comment kartu active redeem.

### 6.5 `redeem/index.tsx`, `redeem/[id].tsx`, `redeem-history.ts`

History dari API (bukan mock).

### 6.6 QA device

Poin turun setelah redeem, QR + countdown 30m, muncul di Filament Antrean Kupon.

---

## Fase 7 — Integrasi end-to-end & hardening

### 7.1 Uji lintas 3 app

Reservasi → OTP → konfirmasi → invoice + mutasi → history mobile.  
Skenario expired → job release → poin balik + held turun.

### 7.2 Race / idempotency

- Prisma transaction saat reserve
- `lockForUpdate` stok + `abort_if is_used` saat confirm
- Job release idempotent
- Double-submit tombol konfirmasi Filament aman

### 7.3 Observability

Gagal Fonnte = gagalkan **kirim OTP** saja, bukan create token. Log error Fonnte jelas.

### 7.4 Security

`/internal/*` tidak boleh dipakai mobile JWT; secret wajib; prod: batasi network jika memungkinkan.  
Scan QR di browser butuh HTTPS / permission kamera (dokumentasikan untuk kasir).

### 7.5 Di luar MVP (catat saja)

- Emergency refund invoice (`REFUNDED`) + balikin stok/poin
- Batasi 1 active token per member (opsional hard-rule)
- Push notifikasi mobile saat invoice selesai / token hampir expired

---

## Ringkasan folder per app

| App                        | Folder / file utama                                                                                                       |
| -------------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| `apps/api-elysia`          | `src/modules/redeem/`, `src/modules/otp/`                                                                                 |
| `apps/backoffice-filament` | `Models/RedeemToken.php`, `Filament/Resources/RedeemTokens/`, `Services/Redeem/`, command `redeem:release-expired-tokens` |
| `apps/mobile-app`          | `services/redeem.ts`, `reward-redeem-dialog`, `card/redeem-qr`, `redeem/*`, `reward/[sku]`                                |

## Urutan pengerjaan

```
Fase 0
  ├─▶ Fase 1 + Fase 2 (api-elysia, paralel)
  ├─▶ Fase 3 (resource dasar Filament, paralel)
  └─▶ Fase 4 (wizard) — butuh Fase 2 + 3
Fase 5 (job release) — boleh paralel setelah model RedeemToken (0.2) + confirm service siap
Fase 6 (mobile) — butuh Fase 1
Fase 7 — setelah 1–6
```
