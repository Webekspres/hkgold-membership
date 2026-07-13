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
      fullName: t.Optional(t.String()),
      email: t.Optional(t.String({ format: 'email' })),
      phoneNumber: t.Optional(t.String()),
      birthDate: t.Optional(t.Union([t.String(), t.Null()])),
      profilePhotoId: t.Optional(t.Union([t.String(), t.Null()])),
      address: t.Optional(t.Object({
        villageId: t.Optional(t.Number()),
        postalCodeId: t.Optional(t.Number()),
        street: t.Optional(t.String())
      }))
    }),
    detail: {
      summary: 'Update profil member',
      tags: ['Member']
    }
  });
