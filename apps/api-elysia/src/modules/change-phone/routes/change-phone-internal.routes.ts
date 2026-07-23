import { Elysia, t } from 'elysia';
import { requireInternalSecret } from '../../../middleware/internal-auth.middleware';
import { changePhoneService } from '../services/change-phone.service';
import {
  ChangePhoneError,
  type ChangePhoneErrorCode,
} from '../types/change-phone.types';

function statusForError(code: ChangePhoneErrorCode): number {
  switch (code) {
    case 'NOT_FOUND':
      return 404;
    case 'FONNTE_FAILED':
      return 502;
    default:
      return 400;
  }
}

function handleError(error: unknown, set: { status?: number | string }) {
  if (error instanceof ChangePhoneError) {
    set.status = statusForError(error.code);
    return {
      success: false,
      message: error.message,
      error: error.code,
    };
  }
  console.error('[change-phone-internal]', error);
  set.status = 500;
  return {
    success: false,
    message: 'Gagal memproses persetujuan ganti nomor',
  };
}

export const changePhoneInternalRoutes = new Elysia({
  prefix: '/internal/change-phone',
})
  .use(requireInternalSecret)
  .post(
    '/approve',
    async ({ body, set }) => {
      try {
        const data = await changePhoneService.approveByAdmin(
          body.approvalId,
          body.staffId,
          body.actionNotes,
        );
        return {
          success: true,
          message: 'Permintaan ganti nomor disetujui',
          data,
        };
      } catch (error) {
        return handleError(error, set);
      }
    },
    {
      body: t.Object({
        approvalId: t.String({ minLength: 36, maxLength: 36 }),
        staffId: t.Number(),
        actionNotes: t.Optional(t.String({ maxLength: 2000 })),
      }),
    },
  )
  .post(
    '/reject',
    async ({ body, set }) => {
      try {
        const data = await changePhoneService.rejectByAdmin(
          body.approvalId,
          body.staffId,
          body.actionNotes,
        );
        return {
          success: true,
          message: 'Permintaan ganti nomor ditolak',
          data,
        };
      } catch (error) {
        return handleError(error, set);
      }
    },
    {
      body: t.Object({
        approvalId: t.String({ minLength: 36, maxLength: 36 }),
        staffId: t.Number(),
        actionNotes: t.String({ minLength: 1, maxLength: 2000 }),
      }),
    },
  );
