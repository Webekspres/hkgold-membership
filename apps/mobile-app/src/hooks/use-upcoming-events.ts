import { useQuery } from '@tanstack/react-query';

import { fetchEventsPage } from '@/services/events';

export const UPCOMING_EVENTS_LIMIT = 3;
export const UPCOMING_EVENTS_QUERY_KEY = [
  'events',
  'upcoming',
  { limit: UPCOMING_EVENTS_LIMIT },
] as const;

export function useUpcomingEvents() {
  const query = useQuery({
    queryKey: UPCOMING_EVENTS_QUERY_KEY,
    queryFn: () => fetchEventsPage({ limit: UPCOMING_EVENTS_LIMIT }),
    staleTime: 15 * 60_000,
    retry: 1,
  });

  return {
    events: query.data?.items ?? [],
    isLoading: query.isLoading,
    isError: query.isError,
    error: query.error,
    refetch: query.refetch,
  };
}
