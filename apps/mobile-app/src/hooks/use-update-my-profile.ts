import { useMutation, useQueryClient } from '@tanstack/react-query';

import { MEMBER_ME_QUERY_KEY } from '@/hooks/use-my-profile';
import { updateMyProfile, uploadMyAvatar } from '@/services/member';
import type { MemberProfile, UpdateMyProfileInput } from '@/types/member';

export function useUpdateMyProfile() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (input: UpdateMyProfileInput) => updateMyProfile(input),
    onSuccess: (profile: MemberProfile) => {
      queryClient.setQueryData(MEMBER_ME_QUERY_KEY, profile);
    },
  });
}

export function useUploadMyAvatar() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (asset: {
      uri: string;
      mimeType?: string | null;
      fileName?: string | null;
    }) => uploadMyAvatar(asset),
    onSuccess: (profile: MemberProfile) => {
      queryClient.setQueryData(MEMBER_ME_QUERY_KEY, profile);
    },
  });
}
