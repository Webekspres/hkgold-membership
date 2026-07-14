import { useQuery } from '@tanstack/react-query';

import { useAuth } from '@/hooks/use-auth';
import {
  getMyProfile,
  mapProfileToCardView,
  mapSessionToCardView,
  type MemberCardView,
} from '@/services/member';

export const MEMBER_ME_QUERY_KEY = ['member', 'me'] as const;

/**
 * Hybrid: tampilkan session SecureStore dulu, refresh GET /api/member/me di background.
 * Gagal fetch → tetap pakai session (tanpa toast).
 */
export function useMyProfile() {
  const { user, member, isLoading: authLoading } = useAuth();
  const sessionView = mapSessionToCardView(user, member);

  const query = useQuery({
    queryKey: MEMBER_ME_QUERY_KEY,
    queryFn: getMyProfile,
    enabled: !authLoading && !!user && !!member,
    staleTime: 30_000,
    retry: 1,
  });

  const card: MemberCardView | null = query.data
    ? mapProfileToCardView(query.data)
    : sessionView;

  return {
    card,
    isLoading: authLoading || (query.isLoading && !card),
    isRefreshing: query.isFetching && !!card,
    isError: query.isError,
    refetch: query.refetch,
  };
}
