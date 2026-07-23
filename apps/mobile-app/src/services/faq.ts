import { apiClient } from '@/lib/api-client';
import type { ApiEnvelope } from '@/types/auth';
import type { FaqItem } from '@/types/faq';

export async function fetchFaqList(): Promise<FaqItem[]> {
  const { data } = await apiClient.get<ApiEnvelope<FaqItem[]>>('/api/faq');

  if (!data.success || !data.data) {
    throw new Error(data.message || 'Gagal mengambil FAQ');
  }

  return data.data;
}
