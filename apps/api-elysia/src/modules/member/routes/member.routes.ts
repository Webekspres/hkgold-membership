import { Elysia, t } from 'elysia';
import { memberService } from '../services/member.service';
import { requireActiveUser } from '../../../middleware/auth.middleware';
import { UpdateMemberProfileRequest } from '../types/member.types';

const statusFromMessage = (message: string): number => {
  if (message.includes('tidak ditemukan')) return 404;
  return 400;
};

export const memberRoutes = new Elysia({ prefix: '/api/member' })
  .use(requireActiveUser)
  .get('/me', async ({ auth, set }) => {
    if (!auth?.userId) {
      set.status = 401;
      return {
        success: false,
        message: 'Unauthorized - Silakan login terlebih dahulu'
      };
    }

    const profile = await memberService.getProfileByUserId(auth.userId);

    if (!profile) {
      set.status = 404;
      return {
        success: false,
        message: 'Member tidak ditemukan'
      };
    }

    return {
      success: true,
      message: 'Profil member berhasil diambil',
      data: profile
    };
  })
  .patch('/me', async ({ auth, body, set }) => {
    try {
      if (!auth?.userId) {
        set.status = 401;
        return {
          success: false,
          message: 'Unauthorized - Silakan login terlebih dahulu'
        };
      }
      const userId = auth.userId;
      const data = body as UpdateMemberProfileRequest;
      const result = await memberService.updateProfileByUserId(userId, data);

      return {
        success: true,
        message: 'Profil member berhasil diperbarui',
        data: result
      };
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Gagal memperbarui profil';
      set.status = statusFromMessage(message);
      return {
        success: false,
        message
      };
    }
  }, {
    body: t.Object({
      fullName: t.Optional(t.String({ minLength: 1, maxLength: 150 })),
      birthDate: t.Optional(t.Union([t.String(), t.Null()])),
      gender: t.Optional(
        t.Union([t.Literal('MALE'), t.Literal('FEMALE'), t.Null()])
      ),
      address: t.Optional(
        t.Object({
          villageId: t.Number(),
          postalCodeId: t.Number(),
          street: t.String({ minLength: 1 })
        })
      )
    }),
    detail: {
      summary: 'Update profil member',
      tags: ['Member']
    }
  })
  .put(
    '/me/avatar',
    async ({ auth, body, set }) => {
      try {
        if (!auth?.userId) {
          set.status = 401;
          return {
            success: false,
            message: 'Unauthorized - Silakan login terlebih dahulu'
          };
        }

        if (!body.file) {
          set.status = 400;
          return {
            success: false,
            message: 'File wajib diisi'
          };
        }

        const result = await memberService.updateAvatarByUserId(auth.userId, body.file);

        return {
          success: true,
          message: 'Foto profil berhasil diperbarui',
          data: result
        };
      } catch (error) {
        const message = error instanceof Error ? error.message : 'Gagal mengunggah foto profil';
        set.status = statusFromMessage(message);
        return {
          success: false,
          message
        };
      }
    },
    {
      body: t.Object({
        file: t.File()
      }),
      detail: {
        summary: 'Upload/ganti foto profil member',
        tags: ['Member']
      }
    }
  );
