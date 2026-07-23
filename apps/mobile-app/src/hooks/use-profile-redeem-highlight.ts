import { useQuery } from '@tanstack/react-query';
import { useMemo } from 'react';

import { useActiveRedeem } from '@/hooks/use-active-redeem';
import {
  resolveProfileRedeemHighlight,
  type ProfileRedeemHighlight,
} from '@/lib/redeem/resolve-profile-redeem-highlight';
import { fetchRedeemHistory } from '@/services/redeem';

export type { ProfileRedeemHighlight };

export function useProfileRedeemHighlight() {
  const active = useActiveRedeem();

  const latestHistory = useQuery({
    queryKey: ['redeem', 'history', { limit: 1, highlight: true }] as const,
    queryFn: () => fetchRedeemHistory({ limit: 1 }),
    enabled: !active.activeRedeem && !active.isLoading,
    staleTime: 60_000,
    retry: 1,
  });

  const highlight = useMemo(
    (): ProfileRedeemHighlight =>
      resolveProfileRedeemHighlight({
        activeRedeem: active.activeRedeem,
        latestHistoryItem: latestHistory.data?.items[0],
      }),
    [active.activeRedeem, latestHistory.data],
  );

  return {
    highlight,
    isLoading: active.isLoading || (!active.activeRedeem && latestHistory.isLoading),
    refetch: async () => {
      await Promise.all([active.refetch(), latestHistory.refetch()]);
    },
  };
}
