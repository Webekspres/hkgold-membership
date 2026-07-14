import { useInfiniteQuery } from '@tanstack/react-query';

import { fetchRewardCatalogPage } from '@/services/rewards';
import type { RewardFilterState } from '@/types/filter';

export const REWARD_CATALOG_QUERY_KEY = ['reward', 'list'] as const;
const PAGE_SIZE = 15;

export type UseRewardCatalogOptions = {
  /** Sudah di-debounce & digate (>2) di screen; undefined = tanpa search */
  search?: string;
  appliedFilter: RewardFilterState;
  pointsBounds: { min: number; max: number };
};

export function useRewardCatalog({
  search,
  appliedFilter,
  pointsBounds,
}: UseRewardCatalogOptions) {
  const categoryIds =
    appliedFilter.categoryIds.length > 0 ? appliedFilter.categoryIds : undefined;

  const pointsMin =
    appliedFilter.pointsMin > pointsBounds.min ? appliedFilter.pointsMin : undefined;
  const pointsMax =
    appliedFilter.pointsMax < pointsBounds.max ? appliedFilter.pointsMax : undefined;

  const { sortBy, sortOrder } = appliedFilter;

  const query = useInfiniteQuery({
    queryKey: [
      ...REWARD_CATALOG_QUERY_KEY,
      {
        limit: PAGE_SIZE,
        search,
        categoryIds,
        pointsMin,
        pointsMax,
        sortBy,
        sortOrder,
      },
    ],
    queryFn: ({ pageParam }: { pageParam: string | undefined }) =>
      fetchRewardCatalogPage({
        cursor: pageParam,
        limit: PAGE_SIZE,
        search,
        categoryIds,
        pointsMin,
        pointsMax,
        sortBy,
        sortOrder,
      }),
    initialPageParam: undefined as string | undefined,
    getNextPageParam: (lastPage) =>
      lastPage.pagination.hasMore ? (lastPage.pagination.nextCursor ?? undefined) : undefined,
    staleTime: 60_000,
    retry: 1,
  });

  const rewards = query.data?.pages.flatMap((page) => page.data) ?? [];

  return {
    rewards,
    isLoading: query.isLoading,
    isFetchingNextPage: query.isFetchingNextPage,
    isError: query.isError,
    fetchNextPage: query.fetchNextPage,
    hasNextPage: query.hasNextPage,
    refetch: query.refetch,
  };
}
