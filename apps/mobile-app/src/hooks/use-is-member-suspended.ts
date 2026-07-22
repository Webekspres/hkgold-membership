import { useAuth } from '@/hooks/use-auth';
import { useMyProfile } from '@/hooks/use-my-profile';

/** Profil API lebih fresh; fallback session JWT/SecureStore. */
export function useIsMemberSuspended(): boolean {
  const { profile } = useMyProfile();
  const { member } = useAuth();
  return profile?.isSuspended ?? member?.isSuspended ?? false;
}
