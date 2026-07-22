import { useQuery } from '@tanstack/react-query';

import { fetchChangePhoneStatus } from '@/services/change-phone';

export const CHANGE_PHONE_STATUS_QUERY_KEY = ['change-phone', 'status'] as const;

export function useChangePhoneStatus() {
  return useQuery({
    queryKey: CHANGE_PHONE_STATUS_QUERY_KEY,
    queryFn: fetchChangePhoneStatus,
    staleTime: 30_000,
  });
}
