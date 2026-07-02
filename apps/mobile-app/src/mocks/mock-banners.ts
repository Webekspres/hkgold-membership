export type PromotionBanner = {
  id: string;
  image: number;
};

export const MOCK_PROMOTION_BANNERS: PromotionBanner[] = [
  { id: 'promo-1', image: require('@/assets/mockImage/mock-image-banner-promotion.webp') },
  { id: 'promo-2', image: require('@/assets/mockImage/mock-image-banner-promotion.webp') },
  { id: 'promo-3', image: require('@/assets/mockImage/mock-image-banner-promotion.webp') },
];
