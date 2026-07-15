import { useQuery } from '@tanstack/react-query';

import { fetchActiveRedeem } from '@/services/redeem';

export function useActiveRedeem() {
  const query = useQuery({
    queryKey: ['redeem', 'active'] as const,
    queryFn: fetchActiveRedeem,
    staleTime: 30_000,
    retry: 1,
  });

  return {
    activeRedeem: query.data ?? null,
    isLoading: query.isLoading,
    isError: query.isError,
    error: query.error,
    refetch: query.refetch,
    isFetched: query.isFetched,
  };
}
