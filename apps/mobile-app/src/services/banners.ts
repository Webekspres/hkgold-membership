import { apiClient } from '@/lib/api-client';
import type { ApiEnvelope } from '@/types/auth';
import type { PromotionBanner } from '@/types/banner';

export async function fetchActivePromotionBanners(): Promise<PromotionBanner[]> {
  const { data } = await apiClient.get<ApiEnvelope<PromotionBanner[]>>(
    '/api/promotion-banner',
  );

  if (!data.success || !data.data) {
    throw new Error(data.message || 'Gagal mengambil banner promosi');
  }

  return data.data.map((banner) => ({
    ...banner,
    createdAt:
      typeof banner.createdAt === 'string'
        ? banner.createdAt
        : String(banner.createdAt),
  }));
}
