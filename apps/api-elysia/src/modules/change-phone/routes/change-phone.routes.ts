import { Elysia, t } from 'elysia';
import { requireActiveUser } from '../../../middleware/auth.middleware';
import { changePhoneService } from '../services/change-phone.service';
import {
  ChangePhoneError,
  type ChangePhoneErrorCode,
} from '../types/change-phone.types';

function statusForError(code: ChangePhoneErrorCode): number {
  switch (code) {
    case 'NOT_FOUND':
    case 'NO_PENDING':
      return 404;
    case 'PENDING_EXISTS':
    case 'PHONE_IN_USE':
    case 'PHONE_SAME':
    case 'INVALID_CHALLENGE':
    case 'INTENT_MISSING':
    case 'OTP_INVALID':
    case 'OTP_EXPIRED':
    case 'RESEND_COOLDOWN':
    case 'INVALID_PHONE':
    case 'WA_NOT_SET':
    case 'VALIDATION':
    case 'ALREADY_PROCESSED':
      return 400;
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
  console.error('[change-phone]', error);
  set.status = 500;
  return {
    success: false,
    message: 'Terjadi kesalahan pada ganti nomor HP',
  };
}

export const changePhoneRoutes = new Elysia({ prefix: '/api/member/change-phone' })
  .use(requireActiveUser)
  .get('/status', async ({ auth, set }) => {
    try {
      if (!auth?.memberId) {
        set.status = 401;
        return { success: false, message: 'Unauthorized' };
      }
      const data = await changePhoneService.getStatus(auth.memberId);
      return {
        success: true,
        message: data ? 'Status ganti nomor' : 'Tidak ada riwayat ganti nomor',
        data,
      };
    } catch (error) {
      return handleError(error, set);
    }
  })
  .post('/send-otp-old', async ({ auth, set }) => {
    try {
      if (!auth?.memberId) {
        set.status = 401;
        return { success: false, message: 'Unauthorized' };
      }
      const data = await changePhoneService.sendOtpOld(auth.memberId);
      return {
        success: true,
        message: 'OTP dikirim ke nomor lama',
        data,
      };
    } catch (error) {
      return handleError(error, set);
    }
  })
  .post(
    '/verify-otp-old',
    async ({ auth, body, set }) => {
      try {
        if (!auth?.memberId) {
          set.status = 401;
          return { success: false, message: 'Unauthorized' };
        }
        const data = await changePhoneService.verifyOtpOld(
          auth.memberId,
          body.otp,
        );
        return {
          success: true,
          message: 'OTP nomor lama valid',
          data,
        };
      } catch (error) {
        return handleError(error, set);
      }
    },
    {
      body: t.Object({
        otp: t.String({ minLength: 6, maxLength: 6 }),
      }),
    },
  )
  .post(
    '/send-otp-new',
    async ({ auth, body, set }) => {
      try {
        if (!auth?.memberId) {
          set.status = 401;
          return { success: false, message: 'Unauthorized' };
        }
        const data = await changePhoneService.sendOtpNew(auth.memberId, {
          newPhone: body.newPhone,
          challenge: body.challenge,
        });
        return {
          success: true,
          message: 'OTP dikirim ke nomor baru',
          data,
        };
      } catch (error) {
        return handleError(error, set);
      }
    },
    {
      body: t.Object({
        newPhone: t.String({ minLength: 10, maxLength: 20 }),
        challenge: t.String(),
      }),
    },
  )
  .post(
    '/request-admin',
    async ({ auth, body, set }) => {
      try {
        if (!auth?.memberId) {
          set.status = 401;
          return { success: false, message: 'Unauthorized' };
        }
        const data = await changePhoneService.requestAdminAssisted(
          auth.memberId,
          {
            newPhone: body.newPhone,
            reason: body.reason,
          },
        );
        return {
          success: true,
          message: 'Permintaan ganti nomor menunggu verifikasi admin',
          data,
        };
      } catch (error) {
        return handleError(error, set);
      }
    },
    {
      body: t.Object({
        newPhone: t.String({ minLength: 10, maxLength: 20 }),
        reason: t.String({ minLength: 1, maxLength: 2000 }),
      }),
    },
  )
  .post(
    '/confirm',
    async ({ auth, body, set }) => {
      try {
        if (!auth?.memberId) {
          set.status = 401;
          return { success: false, message: 'Unauthorized' };
        }
        const data = await changePhoneService.confirm(auth.memberId, body.otp);
        return {
          success: true,
          message: 'Nomor HP berhasil diganti. Silakan login ulang.',
          data,
        };
      } catch (error) {
        return handleError(error, set);
      }
    },
    {
      body: t.Object({
        otp: t.String({ minLength: 6, maxLength: 6 }),
      }),
    },
  )
  .post('/cancel', async ({ auth, set }) => {
    try {
      if (!auth?.memberId) {
        set.status = 401;
        return { success: false, message: 'Unauthorized' };
      }
      const data = await changePhoneService.cancel(auth.memberId);
      return {
        success: true,
        message: 'Permintaan ganti nomor dibatalkan',
        data,
      };
    } catch (error) {
      return handleError(error, set);
    }
  });
