import { AxiosError } from 'axios';

import { apiClient } from '@/lib/api-client';

type ApiEnvelope<T> = {
  success: boolean;
  message: string;
  data?: T;
  error?: string;
};

export type ChangePhoneStatus = {
  id: string;
  status: 'PENDING' | 'APPROVED' | 'REJECTED' | 'CANCELLED';
  source: 'SELF_SERVICE' | 'ADMIN_ASSISTED';
  oldPhoneNumber: string;
  newPhoneNumber: string;
  reason: string | null;
  actionNotes: string | null;
  createdAt: string;
  processedAt: string | null;
};

export type ChangePhoneOtpResult = {
  expiresAt: string;
  resendAvailableAt: string;
  maskedPhone: string;
};

export type ChangePhoneConfirmResult = {
  outcome: 'APPROVED';
  status: ChangePhoneStatus;
  forceLogout: true;
};

export class ChangePhoneApiError extends Error {
  constructor(
    message: string,
    public readonly code?: string,
  ) {
    super(message);
    this.name = 'ChangePhoneApiError';
  }
}

function messageFromError(error: unknown, fallback: string): string {
  if (error instanceof AxiosError) {
    const payload = error.response?.data as ApiEnvelope<unknown> | undefined;
    if (payload?.message) {
      const msg = payload.message;
      return msg.length > 180 ? `${msg.slice(0, 180)}…` : msg;
    }
    if (!error.response) {
      return 'Tidak bisa terhubung ke server';
    }
  }
  if (error instanceof Error && error.message) return error.message;
  return fallback;
}

function codeFromError(error: unknown): string | undefined {
  if (error instanceof AxiosError) {
    const payload = error.response?.data as
      | (ApiEnvelope<unknown> & { error?: string; code?: string })
      | undefined;
    return payload?.error ?? payload?.code;
  }
  return undefined;
}

async function postOrThrow<T>(
  path: string,
  body?: Record<string, unknown>,
  fallback = 'Gagal memproses ganti nomor',
): Promise<T> {
  try {
    const { data } = await apiClient.post<ApiEnvelope<T>>(path, body ?? {});
    if (!data.success || data.data === undefined) {
      throw new ChangePhoneApiError(data.message || fallback, data.error);
    }
    return data.data;
  } catch (error) {
    if (error instanceof ChangePhoneApiError) throw error;
    throw new ChangePhoneApiError(
      messageFromError(error, fallback),
      codeFromError(error),
    );
  }
}

export async function fetchChangePhoneStatus(): Promise<ChangePhoneStatus | null> {
  try {
    const { data } = await apiClient.get<ApiEnvelope<ChangePhoneStatus | null>>(
      '/api/member/change-phone/status',
    );
    if (!data.success) {
      throw new ChangePhoneApiError(data.message || 'Gagal mengambil status');
    }
    return data.data ?? null;
  } catch (error) {
    if (error instanceof ChangePhoneApiError) throw error;
    throw new ChangePhoneApiError(
      messageFromError(error, 'Gagal mengambil status'),
      codeFromError(error),
    );
  }
}

export async function sendChangePhoneOtpOld(): Promise<ChangePhoneOtpResult> {
  return postOrThrow('/api/member/change-phone/send-otp-old', undefined, 'Gagal mengirim OTP');
}

export async function verifyChangePhoneOtpOld(
  otp: string,
): Promise<{ challenge: string }> {
  return postOrThrow(
    '/api/member/change-phone/verify-otp-old',
    { otp },
    'OTP tidak valid',
  );
}

export async function sendChangePhoneOtpNew(input: {
  newPhone: string;
  challenge: string;
}): Promise<ChangePhoneOtpResult> {
  return postOrThrow(
    '/api/member/change-phone/send-otp-new',
    input,
    'Gagal mengirim OTP ke nomor baru',
  );
}

export async function requestAdminAssistedChangePhone(input: {
  newPhone: string;
  reason: string;
}): Promise<ChangePhoneStatus> {
  return postOrThrow(
    '/api/member/change-phone/request-admin',
    input,
    'Gagal mengirim permintaan ke admin',
  );
}

export async function confirmChangePhone(
  otp: string,
): Promise<ChangePhoneConfirmResult> {
  return postOrThrow(
    '/api/member/change-phone/confirm',
    { otp },
    'Gagal mengonfirmasi ganti nomor',
  );
}

export async function cancelChangePhone(): Promise<ChangePhoneStatus> {
  return postOrThrow(
    '/api/member/change-phone/cancel',
    undefined,
    'Gagal membatalkan permintaan',
  );
}
