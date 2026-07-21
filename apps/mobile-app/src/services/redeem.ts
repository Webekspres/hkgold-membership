import { AxiosError } from 'axios';

import { apiClient } from '@/lib/api-client';
import { mapRedeemApiStatus } from '@/lib/format/format-redeem-status';
import { resolveRedeemErrorMessage } from '@/lib/redeem/redeem-error-messages';
import type { ActiveRedeemItem } from '@/types/active-redeem';
import type { ApiEnvelope } from '@/types/auth';
import type {
  RedeemApiStatus,
  RedeemErrorCode,
  RedeemHistoryItem,
  RedeemTokenStatus,
} from '@/types/redeem';

type RedeemTokenApi = {
  redeemId: string;
  tokenCode: string;
  heldPoints: number;
  isUsed: boolean;
  expiresAt: string;
  reward: ActiveRedeemItem['reward'];
  branch: ActiveRedeemItem['branch'];
};

type RedeemInvoiceApi = {
  id: string;
  invoiceNumber: string;
  pointsRedeemed: number;
  status: RedeemApiStatus;
  createdAt: string;
  reward: RedeemHistoryItem['reward'];
  branch: RedeemHistoryItem['branch'];
};

type RedeemTokenStatusApi = {
  status: RedeemTokenStatus['status'];
  invoiceId?: string;
};

function mapInvoice(invoice: RedeemInvoiceApi): RedeemHistoryItem {
  return {
    id: invoice.id,
    invoiceNumber: invoice.invoiceNumber,
    pointsRedeemed: invoice.pointsRedeemed,
    redeemedAt: invoice.createdAt,
    status: mapRedeemApiStatus(invoice.status),
    reward: invoice.reward,
    branch: invoice.branch,
  };
}

function messageFromRedeemError(error: unknown, fallback: string): string {
  if (error instanceof AxiosError) {
    const payload = error.response?.data as ApiEnvelope<unknown> | undefined;
    const code = payload?.error as RedeemErrorCode | undefined;
    const mapped = resolveRedeemErrorMessage(code, fallback);
    if (code && mapped !== fallback) {
      return mapped;
    }
    if (payload?.message) {
      const msg = payload.message;
      return msg.length > 180 ? `${msg.slice(0, 180)}…` : msg;
    }
    if (!error.response) {
      if (error.code === 'ECONNABORTED') {
        return 'Timeout ke API. Cek jaringan & server.';
      }
      return 'Tidak bisa terhubung ke API. Pastikan API jalan.';
    }
  }
  if (error instanceof Error) return error.message;
  return fallback;
}

export async function createRedeemToken(
  rewardId: string,
  branchId: number,
): Promise<ActiveRedeemItem> {
  try {
    const { data } = await apiClient.post<ApiEnvelope<RedeemTokenApi>>('/api/redeem/token', {
      rewardId,
      branchId,
    });

    if (!data.success || !data.data) {
      throw new Error(data.message || 'Gagal membuat token redeem');
    }

    return data.data;
  } catch (error) {
    throw new Error(messageFromRedeemError(error, 'Gagal membuat token redeem'));
  }
}

export async function fetchActiveRedeem(): Promise<ActiveRedeemItem | null> {
  try {
    const { data } = await apiClient.get<ApiEnvelope<RedeemTokenApi | null>>('/api/redeem/active');

    if (!data.success) {
      throw new Error(data.message || 'Gagal mengambil token redeem aktif');
    }

    return data.data ?? null;
  } catch (error) {
    throw new Error(messageFromRedeemError(error, 'Gagal mengambil token redeem aktif'));
  }
}

export async function fetchRedeemHistory(params: {
  cursor?: string;
  limit?: number;
}): Promise<{ items: RedeemHistoryItem[]; nextCursor: string | null; hasMore: boolean }> {
  try {
    const { data } = await apiClient.get<ApiEnvelope<RedeemInvoiceApi[]>>('/api/redeem/history', {
      params: {
        limit: params.limit ?? 20,
        ...(params.cursor ? { cursor: params.cursor } : {}),
      },
    });

    if (!data.success || !data.data) {
      throw new Error(data.message || 'Gagal mengambil riwayat redeem');
    }

    return {
      items: data.data.map(mapInvoice),
      nextCursor: data.pagination?.nextCursor ?? null,
      hasMore: data.pagination?.hasMore ?? false,
    };
  } catch (error) {
    throw new Error(messageFromRedeemError(error, 'Gagal mengambil riwayat redeem'));
  }
}

export async function fetchRedeemHistoryById(id: string): Promise<RedeemHistoryItem> {
  try {
    const { data } = await apiClient.get<ApiEnvelope<RedeemInvoiceApi>>(
      `/api/redeem/history/${encodeURIComponent(id)}`,
    );

    if (!data.success || !data.data) {
      throw new Error(data.message || 'Riwayat redeem tidak ditemukan');
    }

    return mapInvoice(data.data);
  } catch (error) {
    throw new Error(messageFromRedeemError(error, 'Riwayat redeem tidak ditemukan'));
  }
}

export async function cancelRedeemToken(redeemId: string): Promise<void> {
  try {
    const { data } = await apiClient.post<ApiEnvelope<null>>('/api/redeem/cancel', {
      redeemId,
    });

    if (!data.success) {
      throw new Error(data.message || 'Gagal membatalkan klaim reward');
    }
  } catch (error) {
    throw new Error(messageFromRedeemError(error, 'Gagal membatalkan klaim reward'));
  }
}

export async function fetchRedeemTokenStatus(redeemId: string): Promise<RedeemTokenStatus> {
  try {
    const { data } = await apiClient.get<ApiEnvelope<RedeemTokenStatusApi>>(
      `/api/redeem/token/${encodeURIComponent(redeemId)}/status`,
    );

    if (!data.success || !data.data) {
      throw new Error(data.message || 'Gagal mengambil status token redeem');
    }

    return {
      status: data.data.status,
      invoiceId: data.data.invoiceId,
    };
  } catch (error) {
    throw new Error(messageFromRedeemError(error, 'Gagal mengambil status token redeem'));
  }
}
