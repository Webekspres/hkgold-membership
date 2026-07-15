import { AxiosError } from 'axios';

import { apiClient } from '@/lib/api-client';
import { mapRedeemApiStatus } from '@/lib/format/format-redeem-status';
import type { ActiveRedeemItem } from '@/types/active-redeem';
import type { ApiEnvelope } from '@/types/auth';
import type {
  RedeemApiStatus,
  RedeemErrorCode,
  RedeemHistoryItem,
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

const REDEEM_ERROR_MESSAGES: Partial<Record<RedeemErrorCode, string>> = {
  REWARD_NOT_FOUND: 'Reward tidak ditemukan',
  REWARD_NOT_ACTIVE: 'Reward tidak aktif',
  STOCK_NOT_FOUND: 'Stok di cabang tidak ditemukan',
  STOCK_UNAVAILABLE: 'Stok reward di cabang tidak tersedia',
  MEMBER_SUSPENDED: 'Akun member sedang ditangguhkan',
  INSUFFICIENT_POINTS: 'Poin tidak mencukupi',
  TOKEN_NOT_FOUND: 'Token redeem tidak ditemukan',
  HISTORY_NOT_FOUND: 'Riwayat redeem tidak ditemukan',
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
    if (code && REDEEM_ERROR_MESSAGES[code]) {
      return REDEEM_ERROR_MESSAGES[code]!;
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
