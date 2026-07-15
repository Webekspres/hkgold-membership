import { Elysia, t } from 'elysia';
import { requireActiveUser } from '../../../middleware/auth.middleware';
import { devicePushService } from '../services/device-push.service';

export const deviceRoutes = new Elysia({ prefix: '/api/device' })
  .use(requireActiveUser)
  .post(
    '/push-token',
    async ({ auth, body, set }) => {
      if (!auth?.userId) {
        set.status = 401;
        return {
          success: false,
          message: 'Unauthorized - Silakan login terlebih dahulu',
        };
      }

      try {
        const row = await devicePushService.upsertToken({
          userId: auth.userId,
          token: body.token,
          deviceUuid: body.deviceUuid ?? null,
        });

        return {
          success: true,
          message: 'Token push terdaftar',
          data: {
            id: row.id,
            platform: row.platform,
            revokedAt: row.revokedAt,
          },
        };
      } catch (error) {
        if (error instanceof Error && error.message === 'TOKEN_REQUIRED') {
          set.status = 400;
          return { success: false, message: 'Token wajib diisi' };
        }
        throw error;
      }
    },
    {
      body: t.Object({
        token: t.String({ minLength: 1 }),
        deviceUuid: t.Optional(t.String()),
      }),
      detail: {
        summary: 'Register mobile FCM push token',
        tags: ['Device'],
      },
    },
  )
  .delete(
    '/push-token',
    async ({ auth, body, set }) => {
      if (!auth?.userId) {
        set.status = 401;
        return {
          success: false,
          message: 'Unauthorized - Silakan login terlebih dahulu',
        };
      }

      try {
        const row = await devicePushService.revokeToken({
          userId: auth.userId,
          token: body.token,
        });

        return {
          success: true,
          message: row ? 'Token push dicabut' : 'Token tidak ditemukan',
          data: row
            ? { id: row.id, revokedAt: row.revokedAt }
            : null,
        };
      } catch (error) {
        if (error instanceof Error && error.message === 'TOKEN_REQUIRED') {
          set.status = 400;
          return { success: false, message: 'Token wajib diisi' };
        }
        throw error;
      }
    },
    {
      body: t.Object({
        token: t.String({ minLength: 1 }),
      }),
      detail: {
        summary: 'Revoke mobile FCM push token',
        tags: ['Device'],
      },
    },
  );
