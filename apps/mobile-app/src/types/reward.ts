export type RewardCategory = {
  id: number;
  name: string;
  slug: string;
};

export type RewardBranchStockItem = {
  branchId: number;
  branchName: string;
  subdistrict: string;
  city: string;
  locationUrl: string | null;
  actualStock: number;
  heldStock: number;
};

export type RewardCatalogItem = {
  id: string;
  sku: string;
  name: string;
  categoryId: number;
  categoryName: string;
  categorySlug: string;
  pointsRequired: number;
  stockRemaining: number;
  image: number | string | null;
};

export type RewardDetail = RewardCatalogItem & {
  description: string;
  images: (string | number)[];
  branchStocks: RewardBranchStockItem[];
};

export type RewardCategoryGroup = RewardCategory & {
  rewards: RewardCatalogItem[];
};

export type RewardPagination = {
  nextCursor: string | null;
  hasMore: boolean;
  limit: number;
};

export type RewardCatalogPage = {
  data: RewardCatalogItem[];
  pagination: RewardPagination;
};
