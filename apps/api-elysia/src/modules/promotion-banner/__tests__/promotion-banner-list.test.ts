import { describe, test, expect } from 'bun:test';
import { promotionBannerService } from '../services/promotion-banner.service';

describe('PromotionBanner Module - Get Active List', () => {
  test('Returns array of active banners with expected shape', async () => {
    const result = await promotionBannerService.getActive();

    expect(Array.isArray(result)).toBe(true);
    result.forEach((banner) => {
      expect(typeof banner.id).toBe('string');
      expect(typeof banner.name).toBe('string');
      expect(typeof banner.imageUrl).toBe('string');
      expect(banner.linkUrl).toBeNull();
      expect(banner.createdAt).toBeInstanceOf(Date);
    });

    // Newest first when more than one
    for (let i = 1; i < result.length; i++) {
      expect(result[i - 1].createdAt.getTime()).toBeGreaterThanOrEqual(
        result[i].createdAt.getTime(),
      );
    }
  });
});
