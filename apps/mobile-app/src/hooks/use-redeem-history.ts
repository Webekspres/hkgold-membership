import { useInfiniteQuery, useQuery } from '@tanstack/react-query';
import { useMemo } from 'react';

import { fetchRedeemHistory, fetchRedeemHistoryById } from '@/services/redeem';

export const REDEEM_HISTORY_LIMIT = 20;

export function useRedeemHistory() {
  const query = useInfiniteQuery({
    queryKey: ['redeem', 'history', { limit: REDEEM_HISTORY_LIMIT }] as const,
    queryFn: ({ pageParam }) =>
      fetchRedeemHistory({
        limit: REDEEM_HISTORY_LIMIT,
        cursor: pageParam,
      }),
    initialPageParam: undefined as string | undefined,
    getNextPageParam: (last) => (last.hasMore ? (last.nextCursor ?? undefined) : undefined),
    staleTime: 60_000,
    retry: 1,
  });

  const items = useMemo(
    () => query.data?.pages.flatMap((page) => page.items) ?? [],
    [query.data],
  );

  return {
    items,
    isLoading: query.isLoading,
    isError: query.isError,
    error: query.error,
    refetch: query.refetch,
    fetchNextPage: query.fetchNextPage,
    hasNextPage: query.hasNextPage,
    isFetchingNextPage: query.isFetchingNextPage,
  };
}

export function useRedeemHistoryById(id: string | undefined) {
  const query = useQuery({
    queryKey: ['redeem', 'history', id] as const,
    queryFn: () => fetchRedeemHistoryById(id!),
    enabled: typeof id === 'string' && id.length > 0,
    staleTime: 60_000,
    retry: 1,
  });

  return {
    item: query.data ?? null,
    isLoading: query.isLoading,
    isError: query.isError,
    error: query.error,
    refetch: query.refetch,
  };
}
