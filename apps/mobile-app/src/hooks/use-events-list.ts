import { useInfiniteQuery } from '@tanstack/react-query';
import { useMemo } from 'react';

import { fetchEventsPage } from '@/services/events';

export const EVENTS_LIST_LIMIT = 15;

export type EventsListFilters = {
  q?: string;
  dateFrom?: string;
  dateTo?: string;
};

export function useEventsList(filters: EventsListFilters = {}) {
  const q = filters.q;
  const dateFrom = filters.dateFrom;
  const dateTo = filters.dateTo;

  const query = useInfiniteQuery({
    queryKey: ['events', 'list', { limit: EVENTS_LIST_LIMIT, q, dateFrom, dateTo }],
    queryFn: ({ pageParam }) =>
      fetchEventsPage({
        limit: EVENTS_LIST_LIMIT,
        cursor: pageParam,
        q,
        dateFrom,
        dateTo,
      }),
    initialPageParam: undefined as string | undefined,
    getNextPageParam: (last) => (last.hasMore ? (last.nextCursor ?? undefined) : undefined),
    staleTime: 15 * 60_000,
    retry: 1,
  });

  const events = useMemo(
    () => query.data?.pages.flatMap((page) => page.items) ?? [],
    [query.data],
  );

  return {
    events,
    isLoading: query.isLoading,
    isError: query.isError,
    error: query.error,
    refetch: query.refetch,
    fetchNextPage: query.fetchNextPage,
    hasNextPage: query.hasNextPage,
    isFetchingNextPage: query.isFetchingNextPage,
  };
}
