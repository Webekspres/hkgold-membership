import { describe, expect, test } from 'bun:test';

import {
  REDEEM_ERROR_MESSAGES,
  resolveRedeemErrorMessage,
} from '@/lib/redeem/redeem-error-messages';

describe('redeem error messages', () => {
  test('TOKEN_ALREADY_ACTIVE and TOKEN_ALREADY_RELEASED have user-facing copy', () => {
    expect(REDEEM_ERROR_MESSAGES.TOKEN_ALREADY_ACTIVE).toBe(
      'Anda masih punya klaim reward aktif. Batalkan atau selesaikan dulu.',
    );
    expect(REDEEM_ERROR_MESSAGES.TOKEN_ALREADY_RELEASED).toBe(
      'Token redeem sudah dibatalkan',
    );
  });

  test('resolveRedeemErrorMessage returns mapped message or fallback', () => {
    expect(resolveRedeemErrorMessage('TOKEN_ALREADY_ACTIVE', 'fallback')).toBe(
      REDEEM_ERROR_MESSAGES.TOKEN_ALREADY_ACTIVE,
    );
    expect(resolveRedeemErrorMessage(undefined, 'Gagal membuat token redeem')).toBe(
      'Gagal membuat token redeem',
    );
  });
});
