import { AxiosError } from 'axios';
import * as SecureStore from 'expo-secure-store';

import { getApiBaseUrl } from '@/config/api';
import { apiClient } from '@/lib/api-client';
import type {
  ApiEnvelope,
  AuthMember,
  AuthResponse,
  AuthUser,
} from '@/types/auth';

/** SecureStore: alphanumeric + `.` `-` `_` only (no `@` / `/`). */
const KEYS = {
  accessToken: 'hkgold_access_token',
  refreshToken: 'hkgold_refresh_token',
  user: 'hkgold_user',
  member: 'hkgold_member',
} as const;

async function persistSession(data: AuthResponse) {
  await SecureStore.setItemAsync(KEYS.accessToken, data.accessToken);
  await SecureStore.setItemAsync(KEYS.refreshToken, data.refreshToken);
  await SecureStore.setItemAsync(KEYS.user, JSON.stringify(data.user));
  await SecureStore.setItemAsync(KEYS.member, JSON.stringify(data.member));
}

/** Update user+member di SecureStore setelah refresh profil (tanpa sentuh token). */
export async function persistProfileSnapshot(
  user: AuthUser,
  member: AuthMember
): Promise<void> {
  await SecureStore.setItemAsync(KEYS.user, JSON.stringify(user));
  await SecureStore.setItemAsync(KEYS.member, JSON.stringify(member));
}

function messageFromError(error: unknown, fallback: string): string {
  if (error instanceof AxiosError) {
    const payload = error.response?.data as ApiEnvelope<unknown> | undefined;
    if (payload?.message) {
      // Jangan dump stack Prisma penuh ke toast
      const msg = payload.message;
      if (msg.includes('birth_date') || msg.includes('does not exist')) {
        return 'Server/database belum sinkron. Hubungi admin backend.';
      }
      return msg.length > 180 ? `${msg.slice(0, 180)}…` : msg;
    }

    if (!error.response) {
      let base = '(URL kosong)';
      try {
        base = getApiBaseUrl();
      } catch {
        // env belum di-inject
      }
      if (error.code === 'ECONNABORTED') {
        return `Timeout ke API (${base}). Cek jaringan & server.`;
      }
      return `Tidak bisa terhubung ke API (${base}). Pastikan API jalan, HP satu Wi‑Fi, dan start Expo via npm start.`;
    }

    if (error.message) return error.message;
  }
  if (error instanceof Error) return error.message;
  return fallback;
}

export async function getAccessToken(): Promise<string | null> {
  try {
    return await SecureStore.getItemAsync(KEYS.accessToken);
  } catch {
    return null;
  }
}

export async function getStoredUser(): Promise<AuthUser | null> {
  try {
    const raw = await SecureStore.getItemAsync(KEYS.user);
    return raw ? (JSON.parse(raw) as AuthUser) : null;
  } catch {
    return null;
  }
}

export async function getStoredMember(): Promise<AuthMember | null> {
  try {
    const raw = await SecureStore.getItemAsync(KEYS.member);
    return raw ? (JSON.parse(raw) as AuthMember) : null;
  } catch {
    return null;
  }
}

export async function login(
  identifier: string,
  password: string
): Promise<AuthResponse> {
  try {
    const { data } = await apiClient.post<ApiEnvelope<AuthResponse>>(
      '/api/auth/login',
      { identifier, password }
    );

    if (!data.success || !data.data) {
      throw new Error(data.message || 'Login gagal');
    }

    await persistSession(data.data);
    return data.data;
  } catch (error) {
    throw new Error(messageFromError(error, 'Login gagal'));
  }
}

export async function register(input: {
  email: string;
  fullName: string;
  phoneNumber: string;
  password: string;
}): Promise<AuthResponse> {
  try {
    const { data } = await apiClient.post<ApiEnvelope<AuthResponse>>(
      '/api/auth/register',
      input
    );

    if (!data.success || !data.data) {
      throw new Error(data.message || 'Registrasi gagal');
    }

    await persistSession(data.data);
    return data.data;
  } catch (error) {
    throw new Error(messageFromError(error, 'Registrasi gagal'));
  }
}

export async function changePassword(
  oldPassword: string,
  newPassword: string
): Promise<void> {
  try {
    const { data } = await apiClient.post<ApiEnvelope<void>>(
      '/api/auth/change-password',
      { oldPassword, newPassword }
    );

    if (!data.success) {
      throw new Error(data.message || 'Gagal mengubah password');
    }

    // Success - no need to update session (per user requirement: tetap login)
  } catch (error) {
    throw new Error(messageFromError(error, 'Gagal mengubah password'));
  }
}

export async function logout(): Promise<void> {
  try {
    const { unregisterPushToken } = await import('@/services/device-push');
    await unregisterPushToken();
  } catch {
    // best-effort
  }

  try {
    await Promise.all([
      SecureStore.deleteItemAsync(KEYS.accessToken),
      SecureStore.deleteItemAsync(KEYS.refreshToken),
      SecureStore.deleteItemAsync(KEYS.user),
      SecureStore.deleteItemAsync(KEYS.member),
    ]);
  } catch {
    // Storage sudah kosong — abaikan
  }
}
