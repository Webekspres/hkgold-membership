import { Elysia } from 'elysia';
import { promotionBannerService } from '../services/promotion-banner.service';

export const promotionBannerRoutes = new Elysia({ prefix: '/api/promotion-banner' }).get(
  '/',
  async () => {
    const data = await promotionBannerService.getActive();
    return {
      success: true,
      message: 'Daftar banner promosi berhasil diambil',
      data,
    };
  },
);
