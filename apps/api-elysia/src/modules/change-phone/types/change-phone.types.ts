export type ChangePhoneSourceValue = 'SELF_SERVICE' | 'ADMIN_ASSISTED';

export type ChangePhoneStatusValue =
  | 'PENDING'
  | 'APPROVED'
  | 'REJECTED'
  | 'CANCELLED';

export type ChangePhoneErrorCode =
  | 'NOT_FOUND'
  | 'PENDING_EXISTS'
  | 'PHONE_IN_USE'
  | 'PHONE_SAME'
  | 'INVALID_CHALLENGE'
  | 'INTENT_MISSING'
  | 'OTP_INVALID'
  | 'OTP_EXPIRED'
  | 'RESEND_COOLDOWN'
  | 'FONNTE_FAILED'
  | 'INVALID_PHONE'
  | 'WA_NOT_SET'
  | 'NO_PENDING'
  | 'VALIDATION'
  | 'ALREADY_PROCESSED';

export class ChangePhoneError extends Error {
  constructor(
    public readonly code: ChangePhoneErrorCode,
    message: string,
  ) {
    super(message);
    this.name = 'ChangePhoneError';
  }
}

export interface ChangePhoneStatusDto {
  id: string;
  status: ChangePhoneStatusValue;
  source: ChangePhoneSourceValue;
  oldPhoneNumber: string;
  newPhoneNumber: string;
  reason: string | null;
  actionNotes: string | null;
  createdAt: string;
  processedAt: string | null;
}

export interface ChangePhoneIntent {
  newPhone: string;
  source: ChangePhoneSourceValue;
  reason: string | null;
}
