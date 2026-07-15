# Rencana Implementasi — Fitur Redeem Point

Referensi: `memory/flow_redeem_point.md`, `packages/database/schema.prisma`.

## Keputusan Desain (Locked)

| Keputusan | Nilai |
|---|---|
| Durasi expired token | **30 menit** (`REDEEM_TOKEN_EXPIRY_MINUTES`, default di kode) — env template saat ini masih `4320`, harus dikoreksi di Fase 0 |
| Durasi expired OTP | 5 menit (`REDEEM_OTP_EXPIRY_MINUTES`) |
| Format `token_code` | 10 karakter alfanumerik random uppercase, **tanpa prefix/dash** (contoh: `X7R92QK3TM`) — muat di kolom `VARCHAR(10)`, tidak perlu migrasi kolom |
| OTP WhatsApp | Full integrasi Fonnte dari awal (token sudah di Doppler) |
| Pembagi tanggung jawab data | Lihat bagian "Pembagian Data" di bawah |

### Pembagian Data (kenapa tidak semua HTTP call)

`api-elysia` dan `backoffice-filament` membaca/menulis **database MySQL yang sama** (`hkgold_backoffice_filament`) — Prisma dan Eloquent hanya dua ORM berbeda di atas satu sumber data (lihat `@@map` di `schema.prisma`). Karena itu:

- **api-elysia (Prisma)** — pemilik alur *mobile-initiated*: membuat `RedeemToken` (reservasi poin + stok), membaca riwayat (`RedeemInvoice`), dan **satu-satunya** yang boleh bicara ke Fonnte (endpoint internal untuk OTP).
- **backoffice-filament (Eloquent)** — pemilik alur *cashier-initiated*: query & update `RedeemToken`/`RewardBranchStock`/`RedeemInvoice` **langsung ke DB sendiri** (tidak perlu HTTP call ke Elysia untuk baca/tulis token). Filament **hanya** memanggil api-elysia lewat endpoint internal untuk kirim/verifikasi OTP (sesuai keputusan sebelumnya: Fonnte logic terpusat di api-elysia).
- Endpoint internal (`/internal/otp/*`) dilindungi header `X-Internal-Secret` = `MOBILE_API_INTERNAL_SECRET` (harus identik di kedua Doppler config).

---

## Fase 0 — Persiapan Lintas Platform

### 0.1 Koreksi env token expiry (3 file)
- `apps/api-elysia/.env.example` → `REDEEM_TOKEN_EXPIRY_MINUTES=30`
- `apps/backoffice-filament/.env.example` & `.env.testing` → `REDEEM_TOKEN_EXPIRY_MINUTES=30`
- Update juga nilai di Doppler (`dev_backend`, `dev_backoffice`) — dilakukan manual oleh user.

### 0.2 Model Eloquent `RedeemToken` (Laravel)
- File: `apps/backoffice-filament/app/Models/RedeemToken.php`
- `HasUuids`, `casts`: `is_used` → bool, `expired_at`/`created_at` → datetime.
- Relasi: `member()` (belongsTo Member), `reward()` (belongsTo Reward), `branch()` (belongsTo Branch).
- Scope `available()`: `where('is_used', false)->where('expired_at', '>', now())`.
- Tidak perlu migration baru — tabel `redeem_tokens` sudah ada.

### 0.3 Kontrak DTO (dicatat, bukan kode)
Disepakati bentuk response supaya mobile & api-elysia selaras:

```ts
// POST /api/redeem/token → 201
{ success: true, message: string, data: {
  redeemId: string; tokenCode: string; heldPoints: number;
  expiresAt: string; reward: { sku, name, imageUrl }; branch: { name, address };
}}

// GET /api/redeem/active → 200 (data: null jika tidak ada redeem aktif)
// GET /api/redeem/history → 200 (paginated, dari tabel redeem_invoices)
```

---

## Fase 1 — `apps/api-elysia`: Modul Redeem (Reservasi Poin & Token)

Folder baru: `apps/api-elysia/src/modules/redeem/` (ikuti struktur modul `reward/`).

### 1.1 `types/redeem.types.ts`
`CreateRedeemTokenRequest`, `RedeemTokenResponse`, `ActiveRedeemResponse`, `RedeemHistoryItemResponse`, `RedeemErrorCode` (`INSUFFICIENT_POINTS`, `INSUFFICIENT_STOCK`, `MEMBER_SUSPENDED`, `REWARD_NOT_ACTIVE`).

### 1.2 `interfaces/redeem.interface.ts`
Kontrak method service: `createRedeemToken`, `getActiveRedeemToken`, `getRedeemHistory`.

### 1.3 `services/redeem.service.ts` — algoritma `createRedeemToken(memberId, rewardId, branchId)`
```
db.$transaction(async (tx) => {
  reward = tx.reward.findUnique(rewardId)
  if (!reward.isActive || now < reward.startAt || now > reward.endAt) throw REWARD_NOT_ACTIVE

  stock = tx.rewardBranchStock.findUnique({ rewardId, branchId })
  available = stock.actualStock - stock.heldStock
  if (available < 1) throw INSUFFICIENT_STOCK

  member = tx.member.findUnique(memberId)
  if (member.isSuspended) throw MEMBER_SUSPENDED
  if (member.pointBalance < reward.pointsRequired) throw INSUFFICIENT_POINTS

  tokenCode = generateUniqueTokenCode(tx)   // loop random 10-char sampai unik
  tx.member.update({ pointBalance: { decrement: reward.pointsRequired } })
  tx.rewardBranchStock.update({ heldStock: { increment: 1 } })
  token = tx.redeemToken.create({
    memberId, rewardId, branchId, tokenCode,
    heldPoints: reward.pointsRequired, isUsed: false,
    expiredAt: now + REDEEM_TOKEN_EXPIRY_MINUTES,
  })
  return token
})
```
> Catatan: poin dipotong saat reservasi tapi **belum** membuat baris `PointMutation` (ledger permanen baru ditulis Laravel saat invoice terbit, sesuai `flow_redeem_point.md`). Ini titik desain yang perlu di-review saat implementasi — jika tim ingin ledger juga tercatat di reservasi, tambahkan `PointMutation` di sini dengan `pointsRedeemed=0` placeholder (didiskusikan saat coding, jangan diasumsikan).

`getActiveRedeemToken(memberId)`: `findFirst` `RedeemToken` where `memberId`, `isUsed=false`, `expiredAt>now`, order `createdAt desc`.

`getRedeemHistory(memberId, cursor)`: query `RedeemInvoice` (bukan `RedeemToken`) where `memberId`, cursor pagination (pola sama seperti `reward.service.ts`).

### 1.4 `routes/redeem.routes.ts`
- `POST /api/redeem/token` — `requireActiveUser` + `requireNotSuspended` (middleware sudah ada) → body `{ rewardId, branchId }`.
- `GET /api/redeem/active` — auth.
- `GET /api/redeem/history` — auth, paginated.
- Register di `src/index.ts`: `.use(redeemRoutes)`.

### 1.5 `__tests__/redeem-token-creation.test.ts`
Integration test (pola sama `reward-home-preview.test.ts`): seed member+reward+stock, assert saldo terpotong, `heldStock` naik, token unik & `expiredAt` = now+30menit. Test kasus gagal: poin kurang, stok habis, member suspended.

---

## Fase 2 — `apps/api-elysia`: OTP & Fonnte (Endpoint Internal)

Folder: `apps/api-elysia/src/modules/otp/` (modul baru, dipisah dari `redeem` karena dipakai lintas domain).

### 2.1 `services/fonnte.service.ts`
`sendWhatsappMessage(phone: string, message: string)` → `fetch(`${FONNTE_BASE_URL}/send`, { headers: { Authorization: FONNTE_TOKEN }, body: { target: phone, message } })`.

### 2.2 `services/otp.service.ts` — hybrid Redis + DB
```
generateOtp(identifier, type):
  code = random 6 digit
  redis.set(`otp:${type}:${identifier}`, code, EX = OTP_EXPIRY_MINUTES * 60)
  db.otpVerification.create({ identifier, otpCode: code, type, expiredAt: now+OTP_EXPIRY_MINUTES })
  fonnte.sendWhatsappMessage(identifier, `Kode OTP HK GOLD VIP Anda: ${code}`)

verifyOtp(identifier, type, inputCode):
  cached = redis.get(`otp:${type}:${identifier}`)
  if (cached === inputCode) { redis.del(key); db.otpVerification.updateMany(...isUsed=true); return true }
  // fallback jika Redis miss (restart/expired cache tapi DB belum expired)
  row = db.otpVerification.findFirst({ identifier, type, otpCode: inputCode, isUsed: false, expiredAt: { gt: now } })
  if (row) { db.otpVerification.update({ isUsed: true }); return true }
  return false
```

### 2.3 `middleware/internal-auth.middleware.ts`
Cek header `X-Internal-Secret === process.env.MOBILE_API_INTERNAL_SECRET`, else `401`.

### 2.4 `routes/otp.routes.ts` (prefix `/internal/otp`)
- `POST /internal/otp/send` — guarded `internal-auth` → body `{ identifier, type }`.
- `POST /internal/otp/verify` — guarded `internal-auth` → body `{ identifier, type, otpCode }` → `{ valid: boolean }`.
- Register di `src/index.ts` dengan prefix `/internal`.

### 2.5 Test
Mock `fetch` Fonnte, assert Redis+DB konsisten, assert fallback DB jalan saat Redis key sengaja dihapus manual di test.

---

## Fase 3 — `apps/backoffice-filament`: Resource Dasar `RedeemToken`

Folder: `apps/backoffice-filament/app/Filament/Resources/RedeemTokens/` (struktur split, ikuti pola `Members/`).

| File | Isi |
|---|---|
| `RedeemTokenResource.php` | Thin; `navigationGroup = 'Redeem Poin'`; `navigationLabel = 'Antrean Kupon'`; `canCreate()` → `false` (token hanya lahir dari mobile) |
| `Schemas/RedeemTokenInfolist.php` | Detail: nama member, no. HP, reward, cabang, token code, held points, countdown expired, badge status |
| `Tables/RedeemTokensTable.php` | Kolom: `token_code`, `member.full_name`, `reward.name`, `branch.name`, `held_points`, badge `is_used`/expired, filter cabang & status |
| `Pages/ListRedeemTokens.php`, `Pages/ViewRedeemToken.php` | Tanpa Create/Edit page |

Registrasi: pastikan `RedeemToken` masuk grup `Redeem Poin` yang sama dengan `RedeemInvoiceResource` (grup sudah ada di `AppPanelProvider`).

---

## Fase 4 — `apps/backoffice-filament`: Wizard Verifikasi & Konfirmasi

Folder: `app/Filament/Resources/RedeemTokens/Actions/VerifyRedeemTokenAction.php` + `app/Services/Redeem/`.

### 4.1 `app/Services/Redeem/FonnteOtpClient.php`
Wrapper `Http::withHeaders(['X-Internal-Secret' => config('redeem.mobile_api.internal_secret')])`:
- `send(string $phone): void` → `POST {mobile_api.url}/internal/otp/send`
- `verify(string $phone, string $otpCode): bool` → `POST {mobile_api.url}/internal/otp/verify`

### 4.2 `VerifyRedeemTokenAction` — wizard 3 langkah (mirror `PointMutations/Actions/InjectManualPointAction.php`)
```
Step 1 "Masukkan Token":
  input token_code → query RedeemToken::available()->where('token_code', $input)->with(member,reward,branch)->first()
  jika null → Halt + notifikasi error "Token tidak valid/sudah dipakai/expired"
  simpan record ke wizard state, tampilkan detail utk dicocokkan KTP

Step 2 "Kirim OTP":
  tombol "Kirim OTP" → FonnteOtpClient::send($token->member->phone_number)
  notifikasi sukses "OTP terkirim via WhatsApp"

Step 3 "Konfirmasi":
  input otp_code → submit →
    valid = FonnteOtpClient::verify($token->member->phone_number, $otp_code)
    jika !valid → Halt + notifikasi error "Kode OTP salah/expired"
    jika valid → RedeemConfirmationService::confirm($token, auth staff id)
```

### 4.3 `app/Services/Redeem/RedeemConfirmationService.php`
```php
DB::transaction(function () use ($token, $staffId) {
    $token->refresh();
    abort_if($token->is_used || $token->expired_at->isPast(), 422, 'Token tidak lagi valid.');

    $token->update(['is_used' => true]);

    $stock = RewardBranchStock::where('reward_id', $token->reward_id)
        ->where('branch_id', $token->branch_id)
        ->lockForUpdate()->first();
    $stock->decrement('actual_stock');
    $stock->decrement('held_stock');

    $invoice = RedeemInvoice::create([
        'invoice_number'  => $this->generateInvoiceNumber($token->branch),
        'member_id'       => $token->member_id,
        'staff_id'        => $staffId,
        'branch_id'       => $token->branch_id,
        'reward_id'       => $token->reward_id,
        'points_redeemed' => $token->held_points, // snapshot murni, tidak berubah lagi
        'status'          => RedeemStatus::Completed,
    ]);

    ActivityLog::create([...'auditable_type' => RedeemInvoice::class, 'auditable_id' => $invoice->id]);
});
```
Format `invoice_number` disarankan: `INV-{branchCode}-{YYYYMMDD}-{sequence4digit}`.

### 4.4 Wiring
Tombol utama `[📥 Verifikasi & Scan Token]` di `Pages/ListRedeemTokens.php::getHeaderActions()` — panggil `VerifyRedeemTokenAction`. Jangan aktifkan `canCreate()` (sesuai konvensi non-CRUD action project).

### 4.5 Test
`tests/Feature/Redeem/RedeemConfirmationServiceTest.php` (pola `ManualPointInjectionServiceTest.php`):
- Happy path: stok berkurang 1, token `is_used=true`, invoice tercipta dengan snapshot poin benar.
- Idempotency: confirm token yang sudah `is_used=true` → harus gagal (`abort_if`), tidak boleh decrement stok dua kali.
- Token expired → gagal.

---

## Fase 5 — `apps/mobile-app`: Wiring Redeem Flow ke API

### 5.1 `src/services/redeem.ts` (baru)
`createRedeemToken(rewardId, branchId)`, `getActiveRedeem()`, `getRedeemHistory(cursor?)` — pakai `apiClient` (axios) + `ApiEnvelope<T>`, pola sama `src/services/rewards.ts`.

### 5.2 Selaraskan tipe
- `src/types/active-redeem.ts` & `src/types/redeem.ts` → mapping status API (`COMPLETED`/`REFUNDED`) ke label UI (`selesai`/`ditolak`) di satu tempat (`src/lib/format/` atau helper baru), jangan duplikat mapping di banyak file.

### 5.3 `src/components/reward/reward-redeem-dialog.tsx`
Ganti dummy `"Fitur penukaran belum tersedia"` → panggil `redeem.ts#createRedeemToken` → sukses: `router.push('/(tabs)/card/redeem-qr')`; gagal: toast pesan sesuai `RedeemErrorCode` (poin kurang / stok habis / akun ditangguhkan).

### 5.4 `src/app/reward/[sku].tsx`
Confirm handler pakai dialog di atas (bukan `toast.info` placeholder).

### 5.5 `src/app/(tabs)/card/redeem-qr.tsx` + `src/services/active-redeem.ts`
Ganti mock → `getActiveRedeem()`. Handle `data === null` (tidak ada redeem aktif) dengan empty state, bukan crash.

### 5.6 `src/app/(tabs)/card/index.tsx`
Un-comment kartu active-redeem, wire ke service baru.

### 5.7 `src/app/redeem/index.tsx`, `src/app/redeem/[id].tsx`, `src/services/redeem-history.ts`
Ganti mock → `getRedeemHistory()` / detail by id dari API.

### 5.8 QA manual
Redeem end-to-end di device/emulator (poin habis di UI setelah redeem, QR muncul, countdown jalan) lalu cross-check hasilnya muncul benar di Filament `RedeemTokenResource`.

---

## Fase 6 — Integrasi End-to-End & Hardening

### 6.1 Uji flow penuh lintas 3 app
Mobile create token → Filament verifikasi token → kirim OTP → input OTP → konfirmasi → cek: poin mobile tetap terpotong, stok cabang berkurang, invoice muncul di `RedeemInvoiceResource`, riwayat redeem mobile terupdate.

### 6.2 Race condition & idempotency
- Prisma `$transaction` (Fase 1) sudah atomik untuk reservasi.
- Laravel `lockForUpdate()` pada `RewardBranchStock` (Fase 4) wajib ada agar dua kasir tidak bisa confirm token yang sama bersamaan.
- Double-submit tombol "Konfirmasi" di Filament → `abort_if($token->is_used)` mencegah invoice dobel.

### 6.3 Token expired tanpa dipakai (usulan tambahan — **perlu konfirmasi user sebelum dikerjakan**)
Saat ini flow doc tidak menjelaskan pengembalian poin/stok jika member reservasi tapi tidak datang ke toko sampai token expired (30 menit). Tanpa job pembersihan, poin & `heldStock` akan "hangus tertahan" selamanya. Opsi: scheduled command Laravel (`redeem:release-expired-tokens`) atau cron job Elysia yang query `RedeemToken` `isUsed=false && expiredAt<now`, refund `member.pointBalance += heldPoints`, `RewardBranchStock.heldStock -= 1`. **Tanyakan ke user dulu** apakah ini termasuk scope sebelum implementasi.

### 6.4 Observability
Log kegagalan kirim Fonnte (jangan gagalkan create token karena WA down — hanya OTP send yang bergantung Fonnte, bukan reservasi poin).

### 6.5 Security review
Pastikan `/internal/*` di api-elysia tidak diekspos ke mobile client (hanya dipanggil server-to-server dari Filament) — minimal via header secret check (Fase 2.3), idealnya juga dibatasi di level network/firewall saat production.

---

## Ringkasan Folder per App

| App | Folder utama |
|---|---|
| `apps/api-elysia` | `src/modules/redeem/`, `src/modules/otp/` |
| `apps/backoffice-filament` | `app/Models/RedeemToken.php`, `app/Filament/Resources/RedeemTokens/`, `app/Services/Redeem/` |
| `apps/mobile-app` | `src/services/redeem.ts`, `src/components/reward/reward-redeem-dialog.tsx`, `src/app/(tabs)/card/`, `src/app/redeem/`, `src/app/reward/[sku].tsx` |

## Urutan Pengerjaan Disarankan

```
Fase 0 (wajib duluan)
   ├─▶ Fase 1 + Fase 2 (api-elysia, bisa paralel)
   ├─▶ Fase 3 (Filament model & resource dasar, bisa paralel dengan Fase 1/2)
   └─▶ Fase 4 (Filament wizard — butuh Fase 2 selesai utk OTP client, Fase 3 utk resource)
Fase 5 (mobile — butuh Fase 1 selesai)
Fase 6 (integrasi & hardening — setelah 1-5 selesai)
```
