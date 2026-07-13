import { useQuery } from '@tanstack/react-query';

import { fetchEventById } from '@/services/events';

export function useEventDetail(id: string | undefined) {
  const query = useQuery({
    queryKey: ['events', 'detail', id],
    queryFn: () => fetchEventById(id!),
    enabled: typeof id === 'string' && id.length > 0,
    staleTime: 15 * 60_000,
    retry: 1,
  });

  return {
    event: query.data ?? null,
    isLoading: query.isLoading,
    isError: query.isError,
    error: query.error,
    refetch: query.refetch,
  };
}
