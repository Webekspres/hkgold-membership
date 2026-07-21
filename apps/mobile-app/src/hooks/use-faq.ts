import { useQuery } from '@tanstack/react-query';

import { fetchFaqList } from '@/services/faq';

export const FAQ_LIST_QUERY_KEY = ['faq', 'list'] as const;

export function useFaq() {
  const query = useQuery({
    queryKey: FAQ_LIST_QUERY_KEY,
    queryFn: fetchFaqList,
    staleTime: 5 * 60_000,
    retry: 1,
  });

  return {
    items: query.data ?? [],
    isLoading: query.isLoading,
    isError: query.isError,
    refetch: query.refetch,
  };
}
