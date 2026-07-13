import type { MemberTier } from '@/types/auth';

export type MemberProfilePhoto = {
  id: string;
  fileUrl: string;
};

export type MemberProfileUser = {
  id: string;
  email: string;
  fullName: string;
  role: string;
  isActive: boolean;
  profilePhoto: MemberProfilePhoto | null;
};

/** Subset `GET /api/member/me` yang dipakai UI card. */
export type MemberProfile = {
  id: string;
  memberNumber: string;
  phoneNumber: string;
  birthDate: string | null;
  currentTier: MemberTier | string;
  pointBalance: number;
  highestPoint: number;
  isSuspended: boolean;
  user: MemberProfileUser;
};
