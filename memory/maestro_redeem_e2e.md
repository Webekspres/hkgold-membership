# Maestro E2E — Redeem (rencana, belum implement)

Dokumen rencana flow device test redeem. **Belum** setup Maestro / CI device farm.

## Prasyarat

- Dev build mobile (bukan Expo Go) — FCM & native modules butuh build dev/client
- `google-services.json` / Firebase project terpasang di app
- API Elysia + backoffice Filament jalan (DB test/staging)
- Akun member test dengan poin cukup + reward aktif di cabang test
- Akun kasir/staff untuk konfirmasi manual di Filament (flow 2)

## Script usulan (belum commit `.yaml`)

| File usulan | Flow |
|-------------|------|
| `maestro/redeem-cancel-refund.yaml` | Login → klaim reward → lihat QR → batalkan → poin balik |
| `maestro/redeem-confirm-invoice.yaml` | Login → QR → kasir confirm manual → pull refresh di app → detail invoice |
| `maestro/redeem-double-tap-active.yaml` | Login → double-tap redeem → toast/error `TOKEN_ALREADY_ACTIVE` |

### Flow 1: cancel + refund

1. Launch app, login member test
2. Buka katalog reward → pilih reward + cabang → klaim
3. Assert navigasi ke layar QR / teks token aktif
4. Tap "Batalkan klaim", konfirmasi dialog
5. Pull refresh profil/home — poin kembali (atau assert saldo di wallet card)

### Flow 2: confirm → invoice

1. Login member, buat klaim, tampilkan QR
2. **Manual / note:** kasir buka Filament Verify Redeem → OTP → confirm
3. Di app: pull-to-refresh di redeem-qr atau profil
4. Assert highlight "Reward terakhir diklaim" atau navigasi ke `/redeem/[id]`

### Flow 3: double active token

1. Login, buat klaim aktif
2. Coba klaim reward lagi (double-tap CTA)
3. Assert pesan: "Anda masih punya klaim reward aktif..."

## Out of scope (sekarang)

- Install Maestro CLI di mesin dev / CI
- Device farm (BrowserStack, Maestro Cloud, dll.)
- E2E lintas proses API↔Filament dalam satu job otomatis (confirm kasir tetap manual atau API seed terpisah)

## Verifikasi interim (CI tanpa device)

Gunakan suite unit/integration yang sudah ada:

```bash
cd apps/api-elysia && doppler run -- bun test src/modules/redeem
cd apps/backoffice-filament && php artisan test --filter=Redeem
cd apps/mobile-app && bun test src/lib/notifications src/lib/redeem src/lib/active-redeem
```
