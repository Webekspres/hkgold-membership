import { useQuery } from '@tanstack/react-query';

import { fetchRewardCategories } from '@/services/rewards';

export function useRewardCategories() {
  const query = useQuery({
    queryKey: ['reward', 'categories'] as const,
    queryFn: fetchRewardCategories,
    staleTime: 15 * 60_000,
    retry: 1,
  });

  return {
    categories: query.data ?? [],
    isLoading: query.isLoading,
    isError: query.isError,
    refetch: query.refetch,
  };
}
