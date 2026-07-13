import { prisma } from '../../../db';
import type { IPromotionBannerService } from '../interfaces/promotion-banner.interface';
import type { PromotionBannerData } from '../types/promotion-banner.types';

export class PromotionBannerService implements IPromotionBannerService {
  async getActive(): Promise<PromotionBannerData[]> {
    const banners = await prisma.promotionBanner.findMany({
      where: { isActive: true },
      orderBy: { createdAt: 'desc' },
      include: { media: true },
    });

    return banners.map((banner) => ({
      id: banner.id,
      name: banner.name,
      imageUrl: banner.media.fileUrl,
      // ponytail: link CMS belum ada — selalu null sampai kolom/link di-wire
      linkUrl: null,
      createdAt: banner.createdAt,
    }));
  }
}

export const promotionBannerService = new PromotionBannerService();
