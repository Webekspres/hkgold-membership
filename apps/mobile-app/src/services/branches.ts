import { apiClient } from '@/lib/api-client';
import type { ApiEnvelope } from '@/types/auth';
import type { BranchCityOption, BranchItem, BranchPage } from '@/types/branch';

type BranchListApiItem = {
  id: number;
  branchCode: string;
  name: string;
  address: string;
  phone: string | null;
  locationUrl: string | null;
  city?: string;
  subdistrict?: string;
  images?: { fileUrl: string }[];
};

type BranchListApiPage = {
  data: BranchListApiItem[];
  pagination: {
    nextCursor: string | null;
    hasMore: boolean;
    limit: number;
  };
};

function mapBranchItem(item: BranchListApiItem): BranchItem {
  return {
    id: item.id,
    branchCode: item.branchCode,
    name: item.name,
    subdistrict: item.subdistrict ?? '',
    city: item.city ?? '',
    phone: item.phone,
    locationUrl: item.locationUrl,
    imageUrl: item.images?.[0]?.fileUrl ?? null,
  };
}

export async function fetchBranchesPage(params: {
  limit: number;
  cursor?: string;
  q?: string;
  city?: string;
}): Promise<BranchPage> {
  const { data } = await apiClient.get<ApiEnvelope<BranchListApiPage>>('/api/branch', {
    params: {
      limit: params.limit,
      ...(params.cursor ? { cursor: params.cursor } : {}),
      ...(params.q ? { q: params.q } : {}),
      ...(params.city && params.city !== 'all' ? { city: params.city } : {}),
    },
  });

  if (!data.success || !data.data) {
    throw new Error(data.message || 'Gagal mengambil daftar cabang');
  }

  return {
    items: data.data.data.map(mapBranchItem),
    nextCursor: data.data.pagination.nextCursor,
    hasMore: data.data.pagination.hasMore,
  };
}

export async function fetchBranchCities(): Promise<BranchCityOption[]> {
  const { data } = await apiClient.get<ApiEnvelope<BranchCityOption[]>>('/api/branch/cities');

  if (!data.success || !data.data) {
    throw new Error(data.message || 'Gagal mengambil daftar kota');
  }

  return data.data;
}

type NearestBranchApiItem = BranchListApiItem & {
  distanceKm: number;
};

export async function fetchNearestBranch(lat: number, lng: number): Promise<BranchItem> {
  const { data } = await apiClient.get<ApiEnvelope<NearestBranchApiItem>>('/api/branch/nearest', {
    params: { lat, lng },
  });

  if (!data.success || !data.data) {
    throw new Error(data.message || 'Gagal mengambil cabang terdekat');
  }

  return {
    ...mapBranchItem(data.data),
    distanceKm: data.data.distanceKm,
  };
}
