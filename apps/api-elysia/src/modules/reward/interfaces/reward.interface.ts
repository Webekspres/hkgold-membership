import { PaginationResponse } from '../types/reward.types';
import {
  RewardCategoryData,
  RewardCatalogItemData,
  RewardDetailData,
  RewardCategoryGroupData,
  GetRewardsParams
} from '../types/reward.types';

export interface IRewardService {
  getCategories(): Promise<RewardCategoryData[]>;

  getRewards(params: GetRewardsParams): Promise<PaginationResponse<RewardCatalogItemData>>;

  getBySku(sku: string): Promise<RewardDetailData | null>;

  getCatalog(params?: { categoryIds?: number[] }): Promise<RewardCategoryGroupData[]>;

  /** Home teaser: top 3 categories by max reward.updatedAt × 2 newest rewards each */
  getHomePreview(): Promise<RewardCategoryGroupData[]>;
}
