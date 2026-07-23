export type MemberTier = 'SILVER' | 'GOLD' | 'PLATINUM' | 'ELITE';

export type AuthUser = {
  id: string;
  email: string;
  fullName: string;
  role: string;
  isActive: boolean;
};

export type AuthMember = {
  id: string;
  memberNumber: string;
  phoneNumber: string;
  currentTier: MemberTier | string;
  pointBalance: number;
  isSuspended: boolean;
};

export type AuthResponse = {
  accessToken: string;
  refreshToken: string;
  user: AuthUser;
  member: AuthMember;
};

export type ChangePasswordRequest = {
  oldPassword: string;
  newPassword: string;
};

export type ApiEnvelope<T> = {
  success: boolean;
  message: string;
  data?: T;
  error?: string;
  pagination?: {
    nextCursor: string | null;
    hasMore: boolean;
    limit: number;
  };
};
