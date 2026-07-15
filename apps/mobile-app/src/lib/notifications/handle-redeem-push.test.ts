import { describe, expect, test } from 'bun:test';

import { handleRedeemPushPayload } from './handle-redeem-push';

describe('handleRedeemPushPayload', () => {
  test('returns redeem route for valid payload', () => {
    expect(
      handleRedeemPushPayload({
        type: 'redeem_invoice',
        invoiceId: 'inv-uuid-1',
        path: '/redeem/inv-uuid-1',
      }),
    ).toEqual({
      pathname: '/redeem/[id]',
      params: { id: 'inv-uuid-1' },
    });
  });

  test('returns null for wrong type', () => {
    expect(
      handleRedeemPushPayload({
        type: 'other',
        invoiceId: 'inv-uuid-1',
      }),
    ).toBeNull();
  });

  test('returns null for empty invoiceId', () => {
    expect(
      handleRedeemPushPayload({
        type: 'redeem_invoice',
        invoiceId: '   ',
      }),
    ).toBeNull();
  });

  test('returns null for null/undefined data', () => {
    expect(handleRedeemPushPayload(null)).toBeNull();
    expect(handleRedeemPushPayload(undefined)).toBeNull();
  });
});
