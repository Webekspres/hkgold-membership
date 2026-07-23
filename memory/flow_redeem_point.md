# Alur Redeem Point — HK GOLD VIP

Dokumen bisnis + pipeline data. Rencana implementasi teknis: `memory/dev_phase_redeem.md`.

---

## Keputusan desain (locked)

| Item | Nilai |
|---|---|
| Durasi kedaluwarsa token | **30 menit** (`REDEEM_TOKEN_EXPIRY_MINUTES`) |
| Durasi kedaluwarsa OTP | **5 menit** (`REDEEM_OTP_EXPIRY_MINUTES`) |
| Format `token_code` | 10 karakter alfanumerik uppercase, **tanpa prefix/dash** (contoh: `X7R92QK3TM`) |
| Channel OTP WhatsApp | **Fonnte** — hanya `api-elysia` yang mengirim; Filament panggil endpoint internal |
| Saldo & inventaris | Poin dipotong + `held_stock` naik saat reservasi; stok fisik turun saat konfirmasi kasir |
| Ledger `PointMutation` | Ditulis **saat konfirmasi kasir** (bersama `RedeemInvoice`), bukan saat reservasi |
| Token expired tanpa klaim | **Wajib release** — refund poin + turunkan `held_stock` (job terjadwal) |
| Cakupan cabang kasir | Kasir hanya boleh konfirmasi token yang `branch_id`-nya sama dengan cabang staf login |

Sumber kebenaran DB: MySQL bersama (`hkgold_membership` untuk dev; `hkgold_membership_test` untuk suite test). Prisma (Elysia) dan Eloquent (Filament) membaca/menulis tabel yang sama.

---

## BAGIAN 1: ALUR PENGGUNA (USER FLOW)

Alur memisahkan tindakan member di smartphone dengan tindakan kasir di toko fisik.

### Fase 1: Reservasi hadiah oleh pelanggan

- **Aktor:** Member
- **Aplikasi:** Mobile App (React Native / Expo)
- **Langkah:**

1. Member membuka menu **Katalog Hadiah**.
2. Member memilih hadiah (misal: Emas Batangan 1 Gram) dan **cabang pengambilan** (misal: HK GOLD ARTOS MALL).
3. Member menekan **"Tukarkan Poin"**.
4. Sistem memvalidasi saldo, stok cabang (`actual_stock - held_stock`), status reward, dan status suspend member.
5. Aplikasi menampilkan **QR Code** berisi `token_code` 10 digit (contoh: `X7R92QK3TM`) + **countdown 30 menit**.
6. Saldo poin di wallet member **langsung berkurang** (ditahan sistem).

### Fase 2: Pengambilan hadiah fisik di toko

- **Aktor:** Member + Kasir (`STORE_MANAGER` / staf cabang)
- **Aplikasi:** Web Admin Filament v5
- **Langkah:**

1. Member datang ke kasir cabang yang dipilih dan menunjukkan QR / kode token di HP.
2. Kasir buka menu **🎫 Antrean Kupon (`RedeemTokenResource`)**.
3. Kasir klik **`[📥 Verifikasi & Scan Token]`** (input ketik atau scan kamera browser).
4. Sistem menampilkan detail hadiah + identitas member untuk dicocokkan KTP; token harus masih valid, belum dipakai, dan **cabang token = cabang kasir**.
5. Kasir klik **"Kirim OTP"** → Fonnte mengirim OTP ke WhatsApp nomor member (via api-elysia).
6. Member sebutkan OTP → kasir input → **"Konfirmasi Selesai"**.
7. Notifikasi sukses → kasir serahkan hadiah fisik ke member.

### Fase 3: Audit keuangan pusat

- **Aktor:** Finance / Superadmin / Administrator
- **Aplikasi:** Filament — **🧾 Riwayat Nota Penukaran (`RedeemInvoiceResource`)** (read-only + export)
- **Langkah:** pantau invoice nasional (cabang, poin, kasir) → export laporan.

### Fase 4 (sistem): Token kedaluwarsa tanpa klaim

Jika 30 menit lewat dan token belum `is_used`:

1. Job terjadwal menemukan `RedeemToken` expired & belum dipakai.
2. **Refund** `held_points` ke `Member.point_balance`.
3. **Turunkan** `RewardBranchStock.held_stock` (−1).
4. Token tetap `is_used = false` tetapi sudah expired — tidak bisa diverifikasi lagi; idealnya tandai log activity (opsional: soft-mark via activity log saja, tanpa kolom schema baru).

---

## BAGIAN 2: ALUR DATA BACKEND (DATA PIPELINE)

```text
[Mobile] ──POST rewardId + branchId──> [api-elysia Prisma]
                                           │ db.$transaction
                                           ├── cek reward aktif + saldo + available stock
                                           ├── Member.point_balance   -= points_required
                                           ├── RewardBranchStock.held_stock += 1
                                           └── INSERT redeem_tokens (is_used=false, expired_at=+30m)

[Filament kasir] ──input/scan token──> Eloquent query redeem_tokens
         │                              (is_used=false, expired_at>now, branch_id=staff.branch)
         ├── Kirim OTP ──HTTP──> api-elysia POST /internal/otp/send ──> Fonnte WhatsApp
         └── Konfirmasi + OTP valid ──> DB::transaction Laravel
                 ├── redeem_tokens.is_used = true
                 ├── RewardBranchStock.actual_stock -= 1
                 ├── RewardBranchStock.held_stock   -= 1
                 ├── INSERT redeem_invoices (snapshot points_redeemed, staff_id)
                 └── INSERT point_mutations (points_redeemed = held_points, balance_snapshot)

[Job terjadwal] ──token expired & !is_used──>
                 ├── Member.point_balance += held_points
                 └── RewardBranchStock.held_stock -= 1
```

### 1. Reservasi di mobile (api-elysia)

Transaksi atomik (`db.$transaction`):

| Langkah | Aksi |
|---|---|
| Read | Reward aktif dalam periode; member tidak suspended; `point_balance >= points_required`; stok tersedia (`actual_stock - held_stock >= 1`) |
| Write A | `Member.point_balance -= points_required` |
| Write B | `RewardBranchStock.held_stock += 1` |
| Write C | INSERT `redeem_tokens` (`token_code` unik 10 char, `held_points`, `branch_id`, `expired_at = now+30m`, `is_used=false`) |

**Tidak** membuat `RedeemInvoice` / `PointMutation` di tahap ini — hanya staging token.

### 2. OTP WhatsApp (api-elysia, dipanggil Filament)

| Langkah | Aksi |
|---|---|
| Send | Generate OTP 6 digit → Redis (TTL 5m) + INSERT `otp_verifications` (`type=REDEEM_VALIDATION`) → Fonnte kirim WA |
| Verify | Cocokkan Redis dulu; fallback baca `otp_verifications` yang belum `is_used` & belum expired → tandai dipakai |

Endpoint internal: `/internal/otp/*` + header `X-Internal-Secret` (= `MOBILE_API_INTERNAL_SECRET`).

### 3. Konfirmasi kasir (Filament Eloquent)

Setelah OTP valid, `DB::transaction` + `lockForUpdate` pada baris stok:

| Langkah | Aksi |
|---|---|
| Guard | Token masih unused, belum expired, `branch_id` = cabang staf |
| Update 1 | `redeem_tokens.is_used = true` |
| Update 2 | `actual_stock -= 1`, `held_stock -= 1` |
| Insert 3 | `redeem_invoices` (`invoice_number`, `staff_id`, `points_redeemed` = snapshot `held_points`, `status=COMPLETED`) |
| Insert 4 | `point_mutations` (`points_redeemed`, `balance_snapshot`, `branch_id`, referensi ke invoice) |

### 4. Release token expired (job)

Query: `is_used=false AND expired_at < now()` (belum di-release).

| Langkah | Aksi |
|---|---|
| Refund poin | `Member.point_balance += held_points` |
| Lepas hold stok | `held_stock -= 1` (jangan turunkan `actual_stock`) |
| Idempotency | Job harus aman di-retry (jangan double-refund) — tandai release via activity log / cek whether hold masih reflektif |

---

## BAGIAN 3: PEMBAGIAN TANGGUNG JAWAB APP

| App | Tanggung jawab |
|---|---|
| **api-elysia** | Create/read token untuk member; history invoice untuk mobile; **satu-satunya** integrasi Fonnte; endpoint internal OTP |
| **backoffice-filament** | UI antrean kupon + wizard scan/OTP/konfirmasi; tulis invoice + mutasi + stok fisik; job release expired; audit/export invoice |
| **mobile-app** | Katalog → redeem → tampil QR + countdown; riwayat redeem dari API |

---

## Catatan skema terkait

- `RedeemToken` — staging kupon (`packages/database/schema.prisma`)
- `RedeemInvoice` + enum `RedeemStatus` (`COMPLETED` / `REFUNDED`) — nota audit
- `RewardBranchStock.actual_stock` / `held_stock` — stok fisik vs ditahan reservasi
- `OtpVerification` + `OtpType.REDEEM_VALIDATION` — audit OTP
- Refund darurat pasca-invoice (enum `REFUNDED`) = jalur terpisah (bukan scope MVP reservasi); boleh dijadwalkan setelah MVP klaim normal jalan
