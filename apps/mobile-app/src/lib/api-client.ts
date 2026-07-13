import axios, { type AxiosError } from 'axios';
import { router } from 'expo-router';
import * as SecureStore from 'expo-secure-store';

import { getApiBaseUrl } from '@/config/api';

const ACCESS_TOKEN_KEY = 'hkgold_access_token';

function resolveBaseUrl(): string {
  try {
    return getApiBaseUrl();
  } catch {
    // Biarkan request gagal dengan pesan jelas di messageFromError
    return '';
  }
}

export const apiClient = axios.create({
  baseURL: resolveBaseUrl(),
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  timeout: 15_000,
});

apiClient.interceptors.request.use(async (config) => {
  if (!config.baseURL) {
    config.baseURL = resolveBaseUrl();
  }

  try {
    const token = await SecureStore.getItemAsync(ACCESS_TOKEN_KEY);
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
  } catch {
    // Storage kosong / belum siap — lanjut tanpa token
  }
  return config;
});

apiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    if (error.response?.status === 401) {
      try {
        await Promise.all([
          SecureStore.deleteItemAsync(ACCESS_TOKEN_KEY),
          SecureStore.deleteItemAsync('hkgold_refresh_token'),
          SecureStore.deleteItemAsync('hkgold_user'),
          SecureStore.deleteItemAsync('hkgold_member'),
        ]);
      } catch {
        // ignore clear errors
      }
      router.replace('/login');
    }
    return Promise.reject(error);
  }
);
