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
}
