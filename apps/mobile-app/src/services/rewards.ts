import { apiClient } from '@/lib/api-client';
import {
  getRewardDetailBySku,
  MOCK_REWARD_CATALOG,
  MOCK_REWARD_CATEGORIES,
  MOCK_REWARD_LIST,
} from '@/mocks/mock-rewards';
import type { ApiEnvelope } from '@/types/auth';
import type {
  RewardCatalogPage,
  RewardCategory,
  RewardDetail,
} from '@/types/reward';

export function getRewardList() {
  return MOCK_REWARD_LIST;
}

export function getRewardCategories() {
  return MOCK_REWARD_CATEGORIES;
}

/** Home catalog — tetap mock (out of scope). */
export function getRewardCatalog() {
  return MOCK_REWARD_CATALOG;
}

/** @deprecated Prefer fetchRewardBySku */
export function getRewardBySku(sku: string) {
  return getRewardDetailBySku(sku);
}

export type FetchRewardCatalogParams = {
  cursor?: string;
  limit?: number;
  search?: string;
  categoryIds?: number[];
  pointsMin?: number;
  pointsMax?: number;
  sortBy?: 'sku' | 'name' | 'points';
  sortOrder?: 'asc' | 'desc';
};

function buildRewardListQuery(params: FetchRewardCatalogParams): string {
  const sp = new URLSearchParams();
  if (params.limit != null) sp.set('limit', String(params.limit));
  if (params.cursor) sp.set('cursor', params.cursor);
  if (params.search) sp.set('search', params.search);
  if (params.pointsMin != null) sp.set('pointsMin', String(params.pointsMin));
  if (params.pointsMax != null) sp.set('pointsMax', String(params.pointsMax));
  if (params.sortBy) sp.set('sortBy', params.sortBy);
  if (params.sortOrder) sp.set('sortOrder', params.sortOrder);
  params.categoryIds?.forEach((id) => sp.append('categoryIds', String(id)));
  return sp.toString();
}

export async function fetchRewardCatalogPage(
  params: FetchRewardCatalogParams,
): Promise<RewardCatalogPage> {
  const qs = buildRewardListQuery(params);
  const { data } = await apiClient.get<ApiEnvelope<RewardCatalogPage>>(
    qs ? `/api/reward?${qs}` : '/api/reward',
  );

  if (!data.success || !data.data) {
    throw new Error(data.message || 'Gagal mengambil daftar reward');
  }

  return data.data;
}

export async function fetchRewardCategories(): Promise<RewardCategory[]> {
  const { data } = await apiClient.get<ApiEnvelope<RewardCategory[]>>('/api/reward/categories');

  if (!data.success || !data.data) {
    throw new Error(data.message || 'Gagal mengambil kategori reward');
  }

  return data.data;
}

export async function fetchRewardBySku(sku: string): Promise<RewardDetail> {
  const { data } = await apiClient.get<ApiEnvelope<RewardDetail>>(
    `/api/reward/${encodeURIComponent(sku)}`,
  );

  if (!data.success || !data.data) {
    throw new Error(data.message || 'Reward tidak ditemukan');
  }

  return {
    ...data.data,
    images: data.data.images ?? [],
    branchStocks: data.data.branchStocks ?? [],
  };
}

export function getAvailableBranchStock(stock: {
  actualStock: number;
  heldStock: number;
}) {
  return Math.max(stock.actualStock - stock.heldStock, 0);
}
