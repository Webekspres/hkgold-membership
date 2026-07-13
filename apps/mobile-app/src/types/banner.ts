export type PromotionBanner = {
  id: string;
  name: string;
  imageUrl: string;
  /** Null = belum ada CTA; siap di-wire nanti. */
  linkUrl: string | null;
  createdAt: string;
};
