import type { RedeemErrorCode } from '@/types/redeem';

export const REDEEM_ERROR_MESSAGES: Partial<Record<RedeemErrorCode, string>> = {
  REWARD_NOT_FOUND: 'Reward tidak ditemukan',
  REWARD_NOT_ACTIVE: 'Reward tidak aktif',
  STOCK_NOT_FOUND: 'Stok di cabang tidak ditemukan',
  STOCK_UNAVAILABLE: 'Stok reward di cabang tidak tersedia',
  MEMBER_SUSPENDED: 'Akun member sedang ditangguhkan',
  INSUFFICIENT_POINTS: 'Poin tidak mencukupi',
  TOKEN_NOT_FOUND: 'Token redeem tidak ditemukan',
  TOKEN_ALREADY_USED: 'Token redeem sudah dikonfirmasi kasir',
  TOKEN_ALREADY_RELEASED: 'Token redeem sudah dibatalkan',
  TOKEN_ALREADY_ACTIVE: 'Anda masih punya klaim reward aktif. Batalkan atau selesaikan dulu.',
  TOKEN_EXPIRED: 'Token redeem sudah kedaluwarsa',
  HISTORY_NOT_FOUND: 'Riwayat redeem tidak ditemukan',
};

export function resolveRedeemErrorMessage(
  code: RedeemErrorCode | undefined,
  fallback: string,
): string {
  if (code && REDEEM_ERROR_MESSAGES[code]) {
    return REDEEM_ERROR_MESSAGES[code]!;
  }
  return fallback;
}
