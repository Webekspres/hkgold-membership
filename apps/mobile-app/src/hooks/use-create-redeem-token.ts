import { useMutation, useQueryClient } from '@tanstack/react-query';

import { MEMBER_ME_QUERY_KEY } from '@/hooks/use-my-profile';
import { createRedeemToken } from '@/services/redeem';
import type { ActiveRedeemItem } from '@/types/active-redeem';

type CreateRedeemTokenVars = {
  rewardId: string;
  branchId: number;
};

export function useCreateRedeemToken() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ rewardId, branchId }: CreateRedeemTokenVars): Promise<ActiveRedeemItem> =>
      createRedeemToken(rewardId, branchId),
    onSuccess: (data) => {
      queryClient.setQueryData(['redeem', 'active'], data);
      void queryClient.invalidateQueries({ queryKey: ['redeem', 'active'] });
      void queryClient.invalidateQueries({ queryKey: MEMBER_ME_QUERY_KEY });
    },
  });
}
