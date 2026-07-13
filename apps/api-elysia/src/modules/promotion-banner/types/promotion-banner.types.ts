export type PromotionBannerData = {
  id: string;
  name: string;
  imageUrl: string;
  /** Siap untuk CTA nanti; CMS belum punya field → selalu null. */
  linkUrl: string | null;
  createdAt: Date;
};
