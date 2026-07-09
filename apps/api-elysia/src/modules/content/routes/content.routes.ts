import { Elysia } from 'elysia';
import { contentService } from '../services/content.service';

export const contentRoutes = new Elysia({ prefix: '/api/content' })
  .get('/:id', async ({ params, set }) => {
    const { id } = params;

    // Validate UUID format
    const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
    if (!uuidRegex.test(id)) {
      set.status = 400;
      return {
        success: false,
        message: 'ID format tidak valid (harus UUID)'
      };
    }

    const content = await contentService.getById(id);

    if (!content) {
      set.status = 404;
      return {
        success: false,
        message: 'Content tidak ditemukan'
      };
    }

    return {
      success: true,
      message: 'Content berhasil diambil',
      data: content
    };
  })
  .get('/', async ({ query, set }) => {
    const type = (query.type as 'NEWS' | 'EVENT') || 'NEWS';
    const includeArchived = query.includeArchived === 'true';
    const limit = query.limit ? Number(query.limit) : 15;
    const cursor = query.cursor as string | undefined;

    // Validate type
    if (type !== 'NEWS' && type !== 'EVENT') {
      set.status = 400;
      return {
        success: false,
        message: 'Type harus NEWS atau EVENT'
      };
    }

    // Validate limit
    if (limit < 1 || limit > 50) {
      set.status = 400;
      return {
        success: false,
        message: 'Limit harus antara 1-50'
      };
    }

    // Validate cursor jika ada
    if (cursor) {
      try {
        const decoded = JSON.parse(Buffer.from(cursor, 'base64').toString());
        if (!decoded.id || !decoded.createdAt) {
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

    const result = await contentService.getAll({ type, includeArchived, limit, cursor });

    return {
      success: true,
      message: 'Daftar content berhasil diambil',
      data: result
    };
  });
