import { Elysia, t } from 'elysia';
import {
  requireActiveUser,
  requireNotSuspended,
} from '../../../middleware/auth.middleware';
import { redeemService } from '../services/redeem.service';
import { RedeemError, RedeemErrorCode } from '../types/redeem.types';

function statusForRedeemError(code: RedeemErrorCode): number {
  switch (code) {
    case 'REWARD_NOT_FOUND':
    case 'STOCK_NOT_FOUND':
    case 'TOKEN_NOT_FOUND':
    case 'HISTORY_NOT_FOUND':
      return 404;
    case 'MEMBER_SUSPENDED':
      return 403;
    case 'PENDING_PHONE_CHANGE':
      return 403;
    case 'STOCK_UNAVAILABLE':
    case 'INSUFFICIENT_POINTS':
    case 'TOKEN_ALREADY_USED':
    case 'TOKEN_ALREADY_RELEASED':
    case 'TOKEN_ALREADY_ACTIVE':
    case 'TOKEN_EXPIRED':
      return 409;
    case 'REWARD_NOT_ACTIVE':
    default:
      return 400;
  }
}

export const redeemRoutes = new Elysia({ prefix: '/api/redeem' })
  .use(requireActiveUser)
  .get(
    '/active',
    async ({ auth, set }) => {
      if (!auth?.memberId) {
        set.status = 401;
        return {
          success: false,
          message: 'Unauthorized - Silakan login terlebih dahulu',
        };
      }

      const data = await redeemService.getActiveRedeemToken(auth.memberId);

      return {
        success: true,
        message: data
          ? 'Token redeem aktif berhasil diambil'
          : 'Tidak ada token redeem aktif',
        data,
      };
    },
    {
      detail: {
        summary: 'Get active redeem token',
        tags: ['Redeem'],
      },
    },
  )
  .get(
    '/history',
    async ({ auth, query, set }) => {
      if (!auth?.memberId) {
        set.status = 401;
        return {
          success: false,
          message: 'Unauthorized - Silakan login terlebih dahulu',
        };
      }

      const limit = query.limit ? Number(query.limit) : 10;
      const cursor = query.cursor as string | undefined;

      if (Number.isNaN(limit) || limit < 1 || limit > 50) {
        set.status = 400;
        return {
          success: false,
          message: 'Limit harus antara 1-50',
        };
      }

      if (cursor) {
        try {
          const decoded = JSON.parse(
            Buffer.from(cursor, 'base64').toString(),
          );
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

      try {
        const result = await redeemService.getRedeemHistory(auth.memberId, {
          cursor,
          limit,
        });

        return {
          success: true,
          message: 'Riwayat redeem berhasil diambil',
          data: result.data,
          pagination: result.pagination,
        };
      } catch (error) {
        if (error instanceof RedeemError) {
          set.status = statusForRedeemError(error.code);
          return {
            success: false,
            message: error.message,
            error: error.code,
          };
        }

        set.status = 500;
        return {
          success: false,
          message: 'Gagal mengambil riwayat redeem',
        };
      }
    },
    {
      detail: {
        summary: 'Get redeem history (invoices)',
        tags: ['Redeem'],
      },
    },
  )
  .get(
    '/history/:id',
    async ({ auth, params, set }) => {
      if (!auth?.memberId) {
        set.status = 401;
        return {
          success: false,
          message: 'Unauthorized - Silakan login terlebih dahulu',
        };
      }

      const data = await redeemService.getRedeemHistoryById(
        auth.memberId,
        params.id,
      );

      if (!data) {
        set.status = 404;
        return {
          success: false,
          message: 'Riwayat redeem tidak ditemukan',
          error: 'HISTORY_NOT_FOUND',
        };
      }

      return {
        success: true,
        message: 'Detail riwayat redeem berhasil diambil',
        data,
      };
    },
    {
      detail: {
        summary: 'Get redeem history detail by id',
        tags: ['Redeem'],
      },
    },
  )
  .get(
    '/token/:redeemId/status',
    async ({ auth, params, set }) => {
      if (!auth?.memberId) {
        set.status = 401;
        return {
          success: false,
          message: 'Unauthorized - Silakan login terlebih dahulu',
        };
      }

      try {
        const data = await redeemService.getRedeemTokenStatus(
          auth.memberId,
          params.redeemId,
        );

        return {
          success: true,
          message: 'Status token redeem berhasil diambil',
          data,
        };
      } catch (error) {
        if (error instanceof RedeemError) {
          set.status = statusForRedeemError(error.code);
          return {
            success: false,
            message: error.message,
            error: error.code,
          };
        }

        set.status = 500;
        return {
          success: false,
          message: 'Gagal mengambil status token redeem',
        };
      }
    },
    {
      detail: {
        summary: 'Get redeem token status (active/completed/released/expired)',
        tags: ['Redeem'],
      },
    },
  )
  .post(
    '/cancel',
    async ({ auth, body, set }) => {
      if (!auth?.memberId) {
        set.status = 401;
        return {
          success: false,
          message: 'Unauthorized - Silakan login terlebih dahulu',
        };
      }

      try {
        await redeemService.cancelRedeemToken(auth.memberId, body.redeemId);

        return {
          success: true,
          message: 'Klaim reward berhasil dibatalkan',
          data: null,
        };
      } catch (error) {
        if (error instanceof RedeemError) {
          set.status = statusForRedeemError(error.code);
          return {
            success: false,
            message: error.message,
            error: error.code,
          };
        }

        set.status = 500;
        return {
          success: false,
          message: 'Gagal membatalkan klaim reward',
        };
      }
    },
    {
      body: t.Object({
        redeemId: t.String(),
      }),
      detail: {
        summary: 'Cancel active redeem reservation (refund points + held stock)',
        tags: ['Redeem'],
      },
    },
  )
  .use(requireNotSuspended)
  .post(
    '/token',
    async ({ auth, body, set }) => {
      if (!auth?.memberId) {
        set.status = 401;
        return {
          success: false,
          message: 'Unauthorized - Silakan login terlebih dahulu',
        };
      }

      try {
        const data = await redeemService.createRedeemToken(auth.memberId, {
          rewardId: body.rewardId,
          branchId: body.branchId,
        });

        set.status = 201;
        return {
          success: true,
          message: 'Token redeem berhasil dibuat',
          data,
        };
      } catch (error) {
        if (error instanceof RedeemError) {
          set.status = statusForRedeemError(error.code);
          return {
            success: false,
            message: error.message,
            error: error.code,
          };
        }

        set.status = 500;
        return {
          success: false,
          message: 'Gagal membuat token redeem',
        };
      }
    },
    {
      body: t.Object({
        rewardId: t.String(),
        branchId: t.Number(),
      }),
      detail: {
        summary: 'Create redeem reservation token',
        tags: ['Redeem'],
      },
    },
  );
