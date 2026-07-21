import { useQuery } from '@tanstack/react-query';

import { fetchTierBenefitSlides } from '@/services/tier-benefits';

export const TIER_BENEFITS_QUERY_KEY = ['tier', 'levels'] as const;

export function useTierBenefits() {
  const query = useQuery({
    queryKey: TIER_BENEFITS_QUERY_KEY,
    queryFn: fetchTierBenefitSlides,
    staleTime: 5 * 60_000,
    retry: 1,
  });

  return {
    slides: query.data ?? [],
    isLoading: query.isLoading,
    isError: query.isError,
    refetch: query.refetch,
  };
}
