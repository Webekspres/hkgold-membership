import { useInfiniteQuery } from '@tanstack/react-query';

import { fetchPointLedger } from '@/services/point-ledger';
import type { PointMutationItem } from '@/types/point-ledger';

type UsePointLedgerOptions = {
  dateFrom?: string;
  dateTo?: string;
  enabled?: boolean;
};

export function usePointLedger(options: UsePointLedgerOptions = {}) {
  const { dateFrom, dateTo, enabled = true } = options;

  return useInfiniteQuery({
    queryKey: ['point-ledger', { dateFrom, dateTo }],
    queryFn: async ({ pageParam }) => {
      const response = await fetchPointLedger({
        cursor: pageParam as string | undefined,
        limit: 20,
        dateFrom,
        dateTo,
      });
      return response;
    },
    initialPageParam: undefined as string | undefined,
    getNextPageParam: (lastPage) => {
      if (!lastPage.pagination.hasMore || !lastPage.pagination.nextCursor) {
        return undefined;
      }
      return lastPage.pagination.nextCursor;
    },
    select: (data) => ({
      items: data.pages.flatMap((page) => page.data) as PointMutationItem[],
      pagination: data.pages[data.pages.length - 1]?.pagination ?? {
        nextCursor: null,
        hasMore: false,
        limit: 20,
      },
    }),
    enabled,
    staleTime: 60_000, // 1 minute
  });
}

export function usePointLedgerReset() {
  return () => {
    // React Query will refetch on next mount if stale
  };
}
