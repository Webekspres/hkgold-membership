import { useQuery } from '@tanstack/react-query';

import { fetchNewsById } from '@/services/news';

export function useNewsDetail(id: string | undefined) {
  const query = useQuery({
    queryKey: ['news', 'detail', id],
    queryFn: () => fetchNewsById(id!),
    enabled: typeof id === 'string' && id.length > 0,
    staleTime: 15 * 60_000,
    retry: 1,
  });

  return {
    article: query.data ?? null,
    isLoading: query.isLoading,
    isError: query.isError,
    error: query.error,
    refetch: query.refetch,
  };
}
