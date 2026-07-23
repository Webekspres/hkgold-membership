import { apiClient } from '@/lib/api-client';
import type { PointMutationItem } from '@/types/point-ledger';

type PointLedgerResponse = {
  success: boolean;
  message: string;
  data: PointMutationItem[];
  pagination: {
    nextCursor: string | null;
    hasMore: boolean;
    limit: number;
  };
};

type FetchPointLedgerParams = {
  cursor?: string;
  limit?: number;
  dateFrom?: string;
  dateTo?: string;
};

export async function fetchPointLedger(
  params: FetchPointLedgerParams = {},
): Promise<PointLedgerResponse> {
  const searchParams = new URLSearchParams();

  if (params.cursor) searchParams.set('cursor', params.cursor);
  if (params.limit) searchParams.set('limit', String(params.limit));
  if (params.dateFrom) searchParams.set('dateFrom', params.dateFrom);
  if (params.dateTo) searchParams.set('dateTo', params.dateTo);

  const query = searchParams.toString();
  const url = query ? `/api/point-ledger?${query}` : '/api/point-ledger';

  const response = await apiClient.get<PointLedgerResponse>(url);
  return response.data;
}
