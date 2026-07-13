export interface JWTPayload {
  userId: string;
  memberId: string;
  role: string;
  isActive: boolean;
  isSuspended: boolean;
  iat?: number;
  exp?: number;
}

export interface TokenPair {
  accessToken: string;
  refreshToken: string;
}
