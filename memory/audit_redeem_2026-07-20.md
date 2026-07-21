# Audit Redeem — Potensi Masalah (read-only)

**Tanggal:** 2026-07-20  
**Scope:** Alur redeem end-to-end setelah Fase 8 + fitur cancel/profil highlight (api-elysia, Filament, mobile).  
**Metode:** Review kode + graphify; **tidak ada perubahan kode** di audit ini.  
**Referensi:** `memory/flow_redeem_point.md`, `memory/dev_phase_redeem.md`

---

## Ringkasan eksekutif

Fitur redeem sudah jalan untuk jalur happy-path (reserve → QR → OTP kasir → invoice → push). Setelah **cancel member** ditambah, muncul **celah kritis**: konfirmasi kasir **tidak menolak** token yang sudah `released_at` (dibatalkan). Beberapa celah sekunder di UI/cache juga perlu diperbaiki sebelum QA device berat.

| Severity | Jumlah | Prioritas perbaikan |
| --- | --- | --- |
| Critical | 1 | Segera (sebelum produksi cancel) |
| High | 4 | Sprint berikutnya |
| Medium | 5 | Backlog dekat |
| Low | 3 | Opsional |

---

## Critical

### C1. Cancel → confirm kasir = double-spend (poin + barang)

**Evidence**

- Cancel API (`apps/api-elysia/.../redeem.service.ts`): set `releasedAt`, refund `pointBalance`, `heldStock -= 1`. Token tetap `is_used = false`, `expired_at` masih masa depan.
- Confirm Filament (`RedeemConfirmationService.php` L57–63): hanya cek `is_used` dan `expired_at` — **tidak ada cek `released_at`**.
- `RedeemToken::scopeAvailable` (model Filament L49–54): filter `is_used=false` + `expired_at > now()` — **juga tanpa `released_at`**.
- `RedeemInvoice` **tidak punya** FK `redeem_token_id` (schema Prisma).

**Skenario**

1. Member reserve reward di cabang A (poin potong, `held_stock++`).
2. Member batalkan klaim di HP → poin kembali, `held_stock--`, `released_at` terisi.
3. Kasir masih punya / scan `token_code` yang sama (QR tersimpan / Antrean jika masih tampil).
4. Jika ada hold lain di SKU/cabang yang sama (`held_stock >= 1`), confirm lolos: barang keluar, `actual_stock--`, `held_stock--` lagi, invoice dibuat — **tanpa memotong ulang poin member** (sudah di-refund di step 2).

**Likelihood:** Tinggi jika stok/hold concurrent di cabang; gagal dulu dengan `stockInconsistent` jika `held_stock` sudah 0 — race tetap nyata.

**Arah fix (jangan implement di audit ini)**

1. `RedeemConfirmationService::confirm` + `scopeAvailable`: tolak jika `released_at !== null`.
2. Ideal: kolom `redeem_token_id` di `redeem_invoices` + unique partial / soft invariant.

---

## High

### H1. Tidak ada hard-rule 1 active token per member

**Evidence:** `createRedeemToken` tidak cek token aktif lain. `getActiveRedeemToken` hanya `findFirst` terbaru.

**Dampak:** Double-tap / race create → beberapa hold poin+stok; UI hanya tampil token terakhir; stok “tersembunyi” di token lain sampai expire/cancel.

**Likelihood:** Tinggi pada jaringan lambat / double-submit.

**Catatan:** Sudah disebut di `dev_phase_redeem.md` sebagai opsional hard-rule (belum dikunci).

---

### H2. `getRedeemTokenStatus` matching invoice longgar

**Evidence:** Match heuristik `memberId + rewardId + createdAt >= token.createdAt + COMPLETED`, ambil invoice tertua.

**Dampak:** Member redeem SKU yang sama 2× berdekatan → pull-refresh / push deep link bisa buka **invoice salah**.

**Likelihood:** Tinggi jika pola redeem ulang SKU sama.

---

### H3. Push FCM invalidate React Query key salah

**Evidence:** `use-register-push-token.ts` L19–20:

```ts
invalidateQueries({ queryKey: ['active-redeem'] });
invalidateQueries({ queryKey: ['redeem-history'] });
```

Hook sebenarnya pakai `['redeem', 'active']` dan `['redeem', 'history', ...]`.

**Dampak:** Setelah tap push, navigasi ke invoice OK, tapi highlight profil / kartu member / history tetap stale sampai TTL atau pull manual.

**Likelihood:** Tinggi setiap tap push di development build.

---

### H4. Antrean Filament bisa tampilkan / verifikasi token yang sudah di-cancel

**Evidence:** `scopeAvailable` tanpa `released_at` (lihat C1).

**Dampak:** UI kasir menyesatkan; memperbesar permukaan serangan C1.

---

## Medium

### M1. Cancel vs job expire — aman dari double-refund, tapi window “stuck”

Keduanya `FOR UPDATE` + cek `released_at`. Cancel menolak token yang sudah lewat `expired_at` (serahkan ke job).

**Risiko:** Status API bisa `'expired'` sementara poin/stok belum di-release sampai cron `redeem:release-expired-tokens` jalan. UX: member lihat “kedaluwarsa” tapi saldo belum balik sebentar.

---

### M2. Race pull-refresh vs auto-redirect di `redeem-qr.tsx`

`leaveForStatus` set `navigatingRef` **setelah** await status. Effect + pull bisa concurrent; fallback `leaveForStatus === false` → `router.replace('/card')` juga saat fetch status gagal.

**Dampak:** Double toast/nav, atau ke `/card` bukan detail invoice.

---

### M3. Profile highlight: invoice `REFUNDED` terbaru → “Belum ada reward diklaim”

Highlight hanya terima `status === 'selesai'`. History `limit: 1`. Jika invoice terbaru `REFUNDED` (`ditolak`), UI kosong palsu (COMPLETED lama ter-skip).

**Catatan:** `REFUNDED` masih out-of-scope MVP menurut `dev_phase_redeem.md`, tapi mapping status sudah ada di mobile.

---

### M4. Cancel saat kasir sudah confirm — QR stuck tanpa poll

API benar (`TOKEN_ALREADY_USED`). Mobile hanya toast; tidak auto `leaveForStatus` / `refetchInterval`. Member harus pull-refresh untuk masuk ke invoice.

---

### M5. Cancel tidak `FOR UPDATE` baris stock (hanya token)

Expire/confirm Filament lock stock. Cancel Elysia: `findFirst` lalu decrement jika `heldStock > 0`.

**Likelihood underflow:** Rendah (butuh data inkonsisten), tapi lebih lemah dari path PHP.

---

## Low

### L1. Auth cancel tanpa `requireNotSuspended` — by design

Suspend boleh baca/cancel (selaras dual-layer AGENTS). Bukan hole.

### L2. TOCTOU generate `token_code` di luar transaksi create

Unique DB constraint menyelamatkan. Theoretical.

### L3. Confirm sedikit lewat `expired_at` jika OTP lama di dalam TX

Rare; job expire menunggu lock.

---

## Yang sudah relatif aman

| Area | Catatan |
| --- | --- |
| Create reserve (FOR UPDATE stock + member) | Race last-unit sudah diuji |
| Cancel vs confirm **arah confirm-dulu** | Cancel dapat `TOKEN_ALREADY_USED` |
| Cancel vs expire double-refund token yang sama | Blocked oleh `released_at` + lock |
| Create token invalidate cache mobile | `setQueryData` + invalidate active + profile OK |
| Double-tap cancel UI | `isPending` + disable button |
| Expo Go skip `expo-notifications` | Sudah di-guard (bukan bug baru) |

---

## Matriks prioritas perbaikan (usulan, belum dikerjakan)

1. **P0:** Guard `released_at` di `RedeemConfirmationService` + `scopeAvailable`.
2. **P0/P1:** Enforce 1 active token per member di `createRedeemToken` (lock + reject).
3. **P1:** Perbaiki query key invalidate di `use-register-push-token.ts`.
4. **P1:** Link invoice↔token (`redeem_token_id`) atau matching status yang lebih ketat.
5. **P2:** Setelah `TOKEN_ALREADY_USED` di cancel → panggil `leaveForStatus`; harden race `redeem-qr`.
6. **P2:** Profile highlight: skip `REFUNDED` / ambil COMPLETED terbaru; tampilkan loading state.
7. **P3:** Lock stock di cancel Elysia; dokumentasikan SLA job release vs UX “expired”.

---

## Checklist QA manual (setelah fix P0)

- [ ] Cancel di HP → scan token yang sama di Filament → **harus gagal** (token dibatalkan).
- [ ] Double-tap redeem → hanya 1 token aktif / error jelas.
- [ ] Redeem SKU sama 2× → pull-refresh QR #1 → invoice yang benar.
- [ ] Tap push FCM → history + profil highlight refresh.
- [ ] Confirm kasir → pull QR → redirect `/redeem/[id]`.
- [ ] Token expire → saldo balik setelah job (ukur delay cron).

---

## Lampiran file kunci

| Lapisan | Path |
| --- | --- |
| Cancel / status / create | `apps/api-elysia/src/modules/redeem/services/redeem.service.ts` |
| Routes | `apps/api-elysia/src/modules/redeem/routes/redeem.routes.ts` |
| Confirm kasir | `apps/backoffice-filament/app/Services/Redeem/RedeemConfirmationService.php` |
| Scope token | `apps/backoffice-filament/app/Models/RedeemToken.php` |
| Release expired | `apps/backoffice-filament/app/Services/Redeem/ReleaseExpiredRedeemTokenService.php` |
| QR + cancel UI | `apps/mobile-app/src/app/(tabs)/card/redeem-qr.tsx` |
| Profil highlight | `apps/mobile-app/src/hooks/use-profile-redeem-highlight.ts` |
| Push invalidate | `apps/mobile-app/src/hooks/use-register-push-token.ts` |
| Schema | `packages/database/schema.prisma` (`RedeemToken`, `RedeemInvoice`) |
