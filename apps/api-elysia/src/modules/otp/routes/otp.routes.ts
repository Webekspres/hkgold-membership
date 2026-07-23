import { Elysia, t } from 'elysia';
import { requireInternalSecret } from '../../../middleware/internal-auth.middleware';
import { otpService } from '../services/otp.service';
import { OtpError, OtpErrorCode } from '../types/otp.types';

function statusForOtpError(code: OtpErrorCode): number {
  switch (code) {
    case 'OTP_EXPIRED':
      return 410;
    case 'FONNTE_FAILED':
      return 502;
    case 'OTP_INVALID':
    case 'INVALID_PHONE':
    case 'INVALID_TOKEN_CODE':
    default:
      return 400;
  }
}

export const otpRoutes = new Elysia({ prefix: '/internal/otp' })
  .use(requireInternalSecret)
  .post(
    '/send',
    async ({ body, set }) => {
      try {
        const data = await otpService.generateOtp({
          phone: body.phone,
          redeemTokenCode: body.redeemTokenCode,
        });

        return {
          success: true,
          message: 'OTP berhasil dikirim',
          data,
        };
      } catch (error) {
        if (error instanceof OtpError) {
          set.status = statusForOtpError(error.code);
          return {
            success: false,
            message: error.message,
            error: error.code,
          };
        }

        set.status = 500;
        return {
          success: false,
          message: 'Gagal mengirim OTP',
        };
      }
    },
    {
      body: t.Object({
        phone: t.String(),
        redeemTokenCode: t.String(),
      }),
      detail: {
        summary: 'Send redeem OTP via WhatsApp (internal)',
        tags: ['Internal OTP'],
      },
    },
  )
  .post(
    '/verify',
    async ({ body, set }) => {
      try {
        const data = await otpService.verifyOtp({
          phone: body.phone,
          redeemTokenCode: body.redeemTokenCode,
          otp: body.otp,
        });

        return {
          success: true,
          message: 'OTP valid',
          data,
        };
      } catch (error) {
        if (error instanceof OtpError) {
          set.status = statusForOtpError(error.code);
          return {
            success: false,
            message: error.message,
            error: error.code,
          };
        }

        set.status = 500;
        return {
          success: false,
          message: 'Gagal verifikasi OTP',
        };
      }
    },
    {
      body: t.Object({
        phone: t.String(),
        redeemTokenCode: t.String(),
        otp: t.String(),
      }),
      detail: {
        summary: 'Verify redeem OTP (internal)',
        tags: ['Internal OTP'],
      },
    },
  );
