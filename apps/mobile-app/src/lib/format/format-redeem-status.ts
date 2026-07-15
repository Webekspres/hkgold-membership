import type { RedeemApiStatus, RedeemStatus } from '@/types/redeem';

const STATUS_LABELS: Record<RedeemStatus, string> = {
  selesai: 'Selesai',
  ditolak: 'Ditolak',
};

export function mapRedeemApiStatus(status: RedeemApiStatus): RedeemStatus {
  return status === 'REFUNDED' ? 'ditolak' : 'selesai';
}

export function formatRedeemStatus(status: RedeemStatus): string {
  return STATUS_LABELS[status];
}
