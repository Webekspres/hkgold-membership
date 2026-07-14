import { useInfiniteQuery } from '@tanstack/react-query';
import { useMemo } from 'react';

import { fetchBranchesPage } from '@/services/branches';

export const BRANCHES_LIST_LIMIT = 15;

export type BranchesListFilters = {
  q?: string;
  city?: string;
};

export function useBranchesList(filters: BranchesListFilters = {}) {
  const q = filters.q;
  const city = filters.city;

  const query = useInfiniteQuery({
    queryKey: ['branches', 'list', { limit: BRANCHES_LIST_LIMIT, q, city }],
    queryFn: ({ pageParam }) =>
      fetchBranchesPage({
        limit: BRANCHES_LIST_LIMIT,
        cursor: pageParam,
        q,
        city,
      }),
    initialPageParam: undefined as string | undefined,
    getNextPageParam: (last) => (last.hasMore ? (last.nextCursor ?? undefined) : undefined),
    staleTime: 60_000,
    retry: 1,
  });

  const branches = useMemo(
    () => query.data?.pages.flatMap((page) => page.items) ?? [],
    [query.data],
  );

  return {
    branches,
    isLoading: query.isLoading,
    isError: query.isError,
    error: query.error,
    refetch: query.refetch,
    fetchNextPage: query.fetchNextPage,
    hasNextPage: query.hasNextPage,
    isFetchingNextPage: query.isFetchingNextPage,
  };
}
