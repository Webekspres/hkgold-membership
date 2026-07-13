import { useInfiniteQuery } from '@tanstack/react-query';
import { useMemo } from 'react';

import { fetchNewsPage } from '@/services/news';

export const NEWS_LIST_LIMIT = 15;

export type NewsListFilters = {
  q?: string;
  dateFrom?: string;
  dateTo?: string;
};

export function useNewsList(filters: NewsListFilters = {}) {
  const q = filters.q;
  const dateFrom = filters.dateFrom;
  const dateTo = filters.dateTo;

  const query = useInfiniteQuery({
    queryKey: ['news', 'list', { limit: NEWS_LIST_LIMIT, q, dateFrom, dateTo }],
    queryFn: ({ pageParam }) =>
      fetchNewsPage({
        limit: NEWS_LIST_LIMIT,
        cursor: pageParam,
        q,
        dateFrom,
        dateTo,
      }),
    initialPageParam: undefined as string | undefined,
    getNextPageParam: (last) => (last.hasMore ? (last.nextCursor ?? undefined) : undefined),
    staleTime: 60_000,
    retry: 1,
  });

  const articles = useMemo(
    () => query.data?.pages.flatMap((page) => page.items) ?? [],
    [query.data],
  );

  return {
    articles,
    isLoading: query.isLoading,
    isError: query.isError,
    error: query.error,
    refetch: query.refetch,
    fetchNextPage: query.fetchNextPage,
    hasNextPage: query.hasNextPage,
    isFetchingNextPage: query.isFetchingNextPage,
  };
}
