import * as Clipboard from 'expo-clipboard';

import { toast } from '@/lib/sonner';

export async function copyRedeemToken(redeemToken: string) {
  await Clipboard.setStringAsync(redeemToken);
  toast.success('Token redeem berhasil disalin', {
    duration: 3500,
    closeButton: true,
  });
}
