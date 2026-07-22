import { ApiResponse } from '../../../shared/types/response.types';

export interface RegisterRequest {
  email: string;
  password: string;
  fullName: string;
  phoneNumber: string;
}

export interface LoginRequest {
  identifier: string; // email, phone number, atau member number
  password: string;
}

export interface ChangePasswordRequest {
  oldPassword: string;
  newPassword: string;
}

export interface ForgotPasswordSendOtpRequest {
  identifier?: string;
}

export interface ForgotPasswordResetRequest {
  identifier?: string;
  otp: string;
  newPassword: string;
}

export interface ForgotPasswordSendOtpResult {
  expiresAt: string;
  resendAvailableAt: string;
  maskedPhone: string;
}

// Field profil pada tabel User yang boleh diubah pemiliknya sendiri.
// profilePhotoId di-set null untuk menghapus foto profil.
export interface UpdateUserProfileRequest {
  fullName?: string;
  profilePhotoId?: string | null;
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
