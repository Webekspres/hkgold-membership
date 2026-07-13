import { useQuery } from '@tanstack/react-query';

import { fetchActivePromotionBanners } from '@/services/banners';

export const PROMOTION_BANNERS_QUERY_KEY = ['promotion-banners', 'active'] as const;

export function usePromotionBanners() {
  const query = useQuery({
    queryKey: PROMOTION_BANNERS_QUERY_KEY,
    queryFn: fetchActivePromotionBanners,
    staleTime: 4 * 60 * 60_000,
    retry: 1,
  });

  return {
    banners: query.isError ? [] : (query.data ?? []),
    isLoading: query.isLoading,
    isError: query.isError,
    refetch: query.refetch,
  };
}
