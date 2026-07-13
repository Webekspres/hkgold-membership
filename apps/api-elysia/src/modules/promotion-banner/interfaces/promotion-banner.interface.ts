import type { PromotionBannerData } from '../types/promotion-banner.types';

export interface IPromotionBannerService {
  getActive(): Promise<PromotionBannerData[]>;
}
