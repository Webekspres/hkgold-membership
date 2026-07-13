// Response DTOs (match mobile app types)
export interface RewardCategoryData {
  id: number;
  name: string;
  slug: string;
}

export interface RewardCatalogItemData {
  id: string;
  sku: string;
  name: string;
  categoryId: number;
  categoryName: string;
  categorySlug: string;
  pointsRequired: number;
  stockRemaining: number;
  image: string | null;
}

export interface RewardBranchStockData {
  branchId: number;
  branchName: string;
  subdistrict: string;
  city: string;
  locationUrl: string | null;
  actualStock: number;
  heldStock: number;
}

export interface RewardDetailData extends RewardCatalogItemData {
  description: string;
  images: string[];
  branchStocks: RewardBranchStockData[];
}

export interface RewardCategoryGroupData extends RewardCategoryData {
  rewards: RewardCatalogItemData[];
}

// Query params
export interface GetRewardsParams {
  limit?: number;
  cursor?: string;
  categoryIds?: number[];
  pointsMin?: number;
  pointsMax?: number;
  branchId?: number;
  search?: string;
}

// Pagination types
export interface PaginationResponse<T> {
  data: T[];
  pagination: {
    nextCursor: string | null;
    hasMore: boolean;
    limit: number;
  };
}

export function encodeCursor(data: { sku: string; id: string }): string {
  return Buffer.from(JSON.stringify(data)).toString('base64');
}

export function decodeCursor(cursor: string): { sku: string; id: string } | null {
  try {
    const decoded = Buffer.from(cursor, 'base64').toString('utf8');
    return JSON.parse(decoded);
  } catch {
    return null;
  }
}
