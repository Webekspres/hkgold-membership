export type MemberTier = 'SILVER' | 'GOLD' | 'PLATINUM' | 'SAPPHIRE';

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

export type ApiEnvelope<T> = {
  success: boolean;
  message: string;
  data?: T;
};
