import { Elysia, t } from 'elysia';
import { requireActiveUser } from '../../../middleware/auth.middleware';
import { pointLedgerService } from '../services/point-ledger.service';

export const pointLedgerRoutes = new Elysia({ prefix: '/api/point-ledger' })
  .use(requireActiveUser)
  .get(
    '/',
    async ({ auth, query, set }) => {
      if (!auth?.memberId) {
        set.status = 401;
        return {
          success: false,
          message: 'Unauthorized - Silakan login terlebih dahulu',
        };
      }

      const limit = query.limit ? Number(query.limit) : 20;
      const cursor = query.cursor as string | undefined;
      const dateFrom = query.dateFrom as string | undefined;
      const dateTo = query.dateTo as string | undefined;

      if (Number.isNaN(limit) || limit < 1 || limit > 50) {
        set.status = 400;
        return {
          success: false,
          message: 'Limit harus antara 1-50',
        };
      }

      // Validate cursor format
      if (cursor) {
        try {
          const decoded = JSON.parse(Buffer.from(cursor, 'base64').toString());
          if (!decoded.id) {
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

      // Validate date format
      if (dateFrom) {
        const d = new Date(dateFrom);
        if (Number.isNaN(d.getTime())) {
          set.status = 400;
          return {
            success: false,
            message: 'Format dateFrom tidak valid (gunakan YYYY-MM-DD)',
          };
        }
      }
      if (dateTo) {
        const d = new Date(dateTo);
        if (Number.isNaN(d.getTime())) {
          set.status = 400;
          return {
            success: false,
            message: 'Format dateTo tidak valid (gunakan YYYY-MM-DD)',
          };
        }
      }

      const result = await pointLedgerService.getPointLedger(auth.memberId, {
        cursor,
        limit,
        dateFrom,
        dateTo,
      });

      return {
        success: true,
        message: 'Riwayat mutasi poin berhasil diambil',
        data: result.data,
        pagination: result.pagination,
      };
    },
    {
      query: t.Object({
        cursor: t.Optional(t.String()),
        limit: t.Optional(t.Number()),
        dateFrom: t.Optional(t.String()),
        dateTo: t.Optional(t.String()),
      }),
      detail: {
        summary: 'Get point mutation history (ledger)',
        tags: ['Point Ledger'],
      },
    },
  );
