import { Elysia } from 'elysia';
import { branchService } from '../services/branch.service';

function parseCoord(value: unknown): number | null {
  if (value == null || value === '') return null;
  const n = typeof value === 'number' ? value : Number(value);
  return Number.isFinite(n) ? n : null;
}

export const branchRoutes = new Elysia({ prefix: '/api/branch' })
  .get('/cities', async () => {
    const cities = await branchService.getCities();
    return {
      success: true,
      message: 'Daftar kota cabang berhasil diambil',
      data: cities,
    };
  })
  .get('/nearest', async ({ query, set }) => {
    const lat = parseCoord(query.lat);
    const lng = parseCoord(query.lng);

    if (lat == null || lng == null) {
      set.status = 400;
      return {
        success: false,
        message: 'Parameter lat dan lng wajib diisi',
      };
    }

    if (lat < -90 || lat > 90 || lng < -180 || lng > 180) {
      set.status = 400;
      return {
        success: false,
        message: 'Koordinat di luar jangkauan valid',
      };
    }

    const nearest = await branchService.getNearest(lat, lng);

    if (!nearest) {
      set.status = 404;
      return {
        success: false,
        message: 'Tidak ada cabang dengan koordinat',
      };
    }

    return {
      success: true,
      message: 'Cabang terdekat berhasil diambil',
      data: nearest,
    };
  })
  .get('/:id', async ({ params, set }) => {
    const id = Number(params.id);

    if (isNaN(id) || id <= 0) {
      set.status = 400;
      return {
        success: false,
        message: 'ID tidak valid',
      };
    }

    const branch = await branchService.getById(id);

    if (!branch) {
      set.status = 404;
      return {
        success: false,
        message: 'Branch tidak ditemukan',
      };
    }

    return {
      success: true,
      message: 'Branch berhasil diambil',
      data: branch,
    };
  })
  .get('/', async ({ query, set }) => {
    const limit = query.limit ? Number(query.limit) : 15;
    const cursor = query.cursor as string | undefined;
    const q = typeof query.q === 'string' ? query.q : undefined;
    const city = typeof query.city === 'string' ? query.city : undefined;

    if (limit < 1 || limit > 50) {
      set.status = 400;
      return {
        success: false,
        message: 'Limit harus antara 1-50',
      };
    }

    if (cursor) {
      try {
        const decoded = JSON.parse(Buffer.from(cursor, 'base64').toString());
        if (!decoded.id || !decoded.name) {
          set.status = 400;
          return {
            success: false,
            message: 'Cursor tidak valid',
          };
        }
      } catch {
        set.status = 400;
        return {
          success: false,
          message: 'Cursor tidak valid',
        };
      }
    }

    const result = await branchService.getAll({ limit, cursor, q, city });

    return {
      success: true,
      message: 'Daftar branch berhasil diambil',
      data: result,
    };
  });
