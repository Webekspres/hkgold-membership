import { Elysia, t } from 'elysia';
import { rewardService } from '../services/reward.service';
import { requireActiveUser } from '../../../middleware/auth.middleware';

export const rewardRoutes = new Elysia({ prefix: '/api/reward' })
  /**
   * GET /api/reward/categories
   * Public endpoint - no auth required
   * Returns all reward categories
   */
  .get('/categories', async () => {
    const categories = await rewardService.getCategories();

    return {
      success: true,
      message: 'Kategori reward berhasil diambil',
      data: categories
    };
  }, {
    detail: {
      summary: 'Get all reward categories',
      tags: ['Reward']
    }
  })

  /**
   * GET /api/reward/home
   * Public — home teaser: 3 freshest categories × 2 newest rewards
   */
  .get('/home', async ({ set }) => {
    try {
      const preview = await rewardService.getHomePreview();

      return {
        success: true,
        message: 'Preview reward homepage berhasil diambil',
        data: preview,
      };
    } catch {
      set.status = 500;
      return {
        success: false,
        message: 'Gagal mengambil preview reward homepage',
      };
    }
  }, {
    detail: {
      summary: 'Get home reward preview (3 categories × 2 rewards)',
      tags: ['Reward'],
    },
  })

  /**
   * GET /api/reward/catalog
   * Public endpoint - no auth required
   * Returns rewards grouped by categories
   */
  .get('/catalog', async ({ query, set }) => {
    try {
      // Parse categoryIds from query
      let categoryIds: number[] | undefined;
      if (query.categoryIds) {
        const ids = Array.isArray(query.categoryIds) ? query.categoryIds : [query.categoryIds];
        categoryIds = ids.map(id => Number(id)).filter(id => !isNaN(id) && id > 0);
      }

      const catalog = await rewardService.getCatalog({ categoryIds });

      return {
        success: true,
        message: 'Katalog reward berhasil diambil',
        data: catalog
      };
    } catch (error) {
      set.status = 500;
      return {
        success: false,
        message: 'Gagal mengambil katalog reward'
      };
    }
  }, {
    detail: {
      summary: 'Get reward catalog grouped by categories',
      tags: ['Reward']
    }
  })

  /**
   * GET /api/reward
   * Public endpoint with filters and pagination
   */
  .get('/', async ({ query, set }) => {
    try {
      // Parse and validate query params
      const limit = query.limit ? Number(query.limit) : 15;
      const cursor = query.cursor as string | undefined;

      if (limit < 1 || limit > 50) {
        set.status = 400;
        return {
          success: false,
          message: 'Limit harus antara 1-50'
        };
      }

      // Validate cursor if provided
      if (cursor) {
        try {
          const decoded = JSON.parse(Buffer.from(cursor, 'base64').toString());
          if (!decoded.id) {
            set.status = 400;
            return {
              success: false,
              message: 'Cursor tidak valid'
            };
          }
        } catch {
          set.status = 400;
          return {
            success: false,
            message: 'Cursor tidak valid'
          };
        }
      }

      // Parse categoryIds
      let categoryIds: number[] | undefined;
      if (query.categoryIds) {
        const ids = Array.isArray(query.categoryIds) ? query.categoryIds : [query.categoryIds];
        categoryIds = ids.map(id => Number(id)).filter(id => !isNaN(id) && id > 0);
      }

      // Parse point range
      const pointsMin = query.pointsMin ? Number(query.pointsMin) : undefined;
      const pointsMax = query.pointsMax ? Number(query.pointsMax) : undefined;

      if (pointsMin !== undefined && (isNaN(pointsMin) || pointsMin < 0)) {
        set.status = 400;
        return {
          success: false,
          message: 'pointsMin harus bilangan positif'
        };
      }

      if (pointsMax !== undefined && (isNaN(pointsMax) || pointsMax < 0)) {
        set.status = 400;
        return {
          success: false,
          message: 'pointsMax harus bilangan positif'
        };
      }

      if (pointsMin !== undefined && pointsMax !== undefined && pointsMin > pointsMax) {
        set.status = 400;
        return {
          success: false,
          message: 'pointsMin tidak boleh lebih besar dari pointsMax'
        };
      }

      // Parse branchId
      const branchId = query.branchId ? Number(query.branchId) : undefined;
      if (branchId !== undefined && (isNaN(branchId) || branchId <= 0)) {
        set.status = 400;
        return {
          success: false,
          message: 'branchId harus bilangan positif'
        };
      }

      // Parse search
      const search = query.search as string | undefined;

      const sortByRaw = query.sortBy as string | undefined;
      const sortOrderRaw = query.sortOrder as string | undefined;
      const sortBy =
        sortByRaw === 'name' || sortByRaw === 'points' || sortByRaw === 'sku'
          ? sortByRaw
          : 'sku';
      const sortOrder = sortOrderRaw === 'desc' ? 'desc' : 'asc';

      if (sortByRaw && !['sku', 'name', 'points'].includes(sortByRaw)) {
        set.status = 400;
        return {
          success: false,
          message: 'sortBy harus sku, name, atau points',
        };
      }

      if (sortOrderRaw && !['asc', 'desc'].includes(sortOrderRaw)) {
        set.status = 400;
        return {
          success: false,
          message: 'sortOrder harus asc atau desc',
        };
      }

      const result = await rewardService.getRewards({
        limit,
        cursor,
        categoryIds,
        pointsMin,
        pointsMax,
        branchId,
        search,
        sortBy,
        sortOrder,
      });

      return {
        success: true,
        message: 'Daftar reward berhasil diambil',
        data: result
      };
    } catch (error) {
      set.status = 500;
      return {
        success: false,
        message: 'Gagal mengambil daftar reward'
      };
    }
  }, {
    detail: {
      summary: 'Get reward list with filters and pagination',
      tags: ['Reward']
    }
  })

  /**
   * GET /api/reward/:sku
   * Auth required endpoint
   * Returns detailed reward information
   */
  .use(requireActiveUser)
  .get('/:sku', async ({ params, set }) => {
    const sku = params.sku;

    // Basic SKU format validation (alphanumeric + hyphen)
    if (!/^[A-Z0-9-]+$/i.test(sku)) {
      set.status = 400;
      return {
        success: false,
        message: 'Format SKU tidak valid'
      };
    }

    const reward = await rewardService.getBySku(sku);

    if (!reward) {
      set.status = 404;
      return {
        success: false,
        message: 'Reward tidak ditemukan'
      };
    }

    return {
      success: true,
      message: 'Detail reward berhasil diambil',
      data: reward
    };
  }, {
    detail: {
      summary: 'Get reward detail by SKU',
      tags: ['Reward']
    }
  });
