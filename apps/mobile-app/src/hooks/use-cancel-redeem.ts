import { useMutation, useQueryClient } from '@tanstack/react-query';

import { MEMBER_ME_QUERY_KEY } from '@/hooks/use-my-profile';
import { cancelRedeemToken } from '@/services/redeem';

export function useCancelRedeem() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (redeemId: string) => cancelRedeemToken(redeemId),
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: ['redeem', 'active'] }),
        queryClient.invalidateQueries({ queryKey: ['redeem', 'history'] }),
        queryClient.invalidateQueries({ queryKey: MEMBER_ME_QUERY_KEY }),
      ]);
    },
  });
}
