import { useQuery } from '@tanstack/react-query';

import { fetchRewardBySku } from '@/services/rewards';

export function useRewardDetail(sku: string | undefined) {
  const query = useQuery({
    queryKey: ['reward', 'detail', sku],
    queryFn: () => fetchRewardBySku(sku!),
    enabled: typeof sku === 'string' && sku.length > 0,
    staleTime: 60_000,
    retry: 1,
  });

  return {
    reward: query.data ?? null,
    isLoading: query.isLoading,
    isError: query.isError,
    error: query.error,
    refetch: query.refetch,
  };
}
