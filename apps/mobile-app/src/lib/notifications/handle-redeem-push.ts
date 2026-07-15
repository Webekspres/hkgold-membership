export type RedeemPushPayload = {
  type?: string;
  invoiceId?: string;
  invoiceNumber?: string;
  path?: string;
};

export type RedeemPushRoute =
  | { pathname: '/redeem/[id]'; params: { id: string } }
  | null;

/** Parse FCM/data payload → Expo Router params for redeem invoice detail. */
export function handleRedeemPushPayload(
  data: Record<string, unknown> | RedeemPushPayload | null | undefined,
): RedeemPushRoute {
  if (!data || typeof data !== 'object') {
    return null;
  }

  const type = typeof data.type === 'string' ? data.type : '';
  const invoiceId =
    typeof data.invoiceId === 'string' ? data.invoiceId.trim() : '';

  if (type !== 'redeem_invoice' || !invoiceId) {
    return null;
  }

  return {
    pathname: '/redeem/[id]',
    params: { id: invoiceId },
  };
}
