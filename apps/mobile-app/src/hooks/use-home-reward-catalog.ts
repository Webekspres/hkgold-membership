import { useQuery } from '@tanstack/react-query';

import { fetchHomeRewardPreview } from '@/services/rewards';

export const HOME_REWARD_CATALOG_QUERY_KEY = ['reward', 'home-preview'] as const;

export function useHomeRewardCatalog() {
  const query = useQuery({
    queryKey: HOME_REWARD_CATALOG_QUERY_KEY,
    queryFn: fetchHomeRewardPreview,
    staleTime: 15 * 60_000,
    retry: 1,
  });

  return {
    categories: query.data ?? [],
    isLoading: query.isLoading,
    isError: query.isError,
    error: query.error,
    refetch: query.refetch,
  };
}
