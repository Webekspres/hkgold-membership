import { Elysia } from 'elysia';
import { tierService } from '../services/tier.service';
import { requireActiveUser } from '../../../middleware/auth.middleware';

export const tierRoutes = new Elysia({ prefix: '/api/tier' })
  /**
   * GET /api/tier/levels
   * Auth required - all tier endpoints need auth
   * Returns all tier levels with conversion rules
   */
  .use(requireActiveUser)
  .get('/levels', async ({ memberId }) => {
    try {
      const levels = await tierService.getTierLevels();

      return {
        success: true,
        message: 'Daftar level tier berhasil diambil',
        data: {
          levels
        }
      };
    } catch (error) {
      console.error('Error fetching tier levels:', error);
      return {
        success: false,
        message: 'Gagal mengambil daftar level tier'
      };
    }
  }, {
    detail: {
      summary: 'Get all tier levels with conversion rules',
      tags: ['Tier']
    }
  })

  /**
   * GET /api/tier/member
   * Auth required - get current member's tier
   * Returns member's current tier with conversion rules
   */
  .use(requireActiveUser)
  .get('/member', async ({ memberId }) => {
    try {
      const tierData = await tierService.getMemberTier(memberId);

      if (!tierData) {
        return {
          success: false,
          message: 'Tier member tidak ditemukan'
        };
      }

      return {
        success: true,
        message: 'Tier member berhasil diambil',
        data: tierData
      };
    } catch (error) {
      console.error('Error fetching member tier:', error);
      return {
        success: false,
        message: 'Gagal mengambil tier member'
      };
    }
  }, {
    detail: {
      summary: 'Get current member tier with conversion rules',
      tags: ['Tier']
    }
  });
