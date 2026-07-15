export type OtpTypeValue = 'REDEEM_VALIDATION' | 'STAFF_DEVICE_REGISTRATION';

export interface GenerateOtpRequest {
  phone: string;
  redeemTokenCode: string;
}

export interface VerifyOtpRequest {
  phone: string;
  redeemTokenCode: string;
  otp: string;
}

export interface GenerateOtpResult {
  expiresAt: string;
}

export type OtpErrorCode =
  | 'OTP_INVALID'
  | 'OTP_EXPIRED'
  | 'INVALID_PHONE'
  | 'INVALID_TOKEN_CODE'
  | 'FONNTE_FAILED';

export class OtpError extends Error {
  constructor(
    public readonly code: OtpErrorCode,
    message: string,
  ) {
    super(message);
    this.name = 'OtpError';
  }
}

export const DEFAULT_OTP_MESSAGE_TEMPLATE =
  'Kode OTP HK GOLD VIP Anda: {otp}. Berlaku 5 menit. Jangan bagikan kepada siapapun.';
