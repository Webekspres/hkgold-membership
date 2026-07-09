import { ApiResponse } from '../../../shared/types/response.types';

export interface RegisterRequest {
  email: string;
  password: string;
  fullName: string;
  phoneNumber: string;
}

export interface LoginRequest {
  identifier: string; // phone number atau member number
  password: string;
}

export interface ChangePasswordRequest {
  oldPassword: string;
  newPassword: string;
}

export interface UserData {
  id: string;
  email: string;
  fullName: string;
  role: string;
  isActive: boolean;
}

export interface MemberData {
  id: string;
  memberNumber: string;
  phoneNumber: string;
  currentTier: string;
  pointBalance: number;
  isSuspended: boolean;
}

export interface AuthResponse {
  accessToken: string;
  refreshToken: string;
  user: UserData;
  member: MemberData;
}

export type RegisterResponse = ApiResponse<AuthResponse>;
export type LoginResponse = ApiResponse<AuthResponse>;
export type ChangePasswordResponse = ApiResponse<{ message: string }>;
