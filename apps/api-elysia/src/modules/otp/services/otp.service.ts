import { prisma } from '../../../db';
import { getRedis } from '../../../lib/redis';
import {
  FonnteError,
  fonnteService,
  normalizePhoneForFonnte,
} from './fonnte.service';
import {
  DEFAULT_OTP_MESSAGE_TEMPLATE,
  GenerateOtpRequest,
  GenerateOtpResult,
  OtpError,
  VerifyOtpRequest,
} from '../types/otp.types';

const OTP_TYPE = 'REDEEM_VALIDATION' as const;

function otpExpiryMinutes(): number {
  return Number(process.env.REDEEM_OTP_EXPIRY_MINUTES ?? 5);
}

function buildIdentifier(phone62: string, redeemTokenCode: string): string {
  return `otp:${OTP_TYPE}:${phone62}:${redeemTokenCode}`;
}

function validateTokenCode(code: string): string {
  const trimmed = code.trim().toUpperCase();
  if (!/^[A-Z0-9]{10}$/.test(trimmed)) {
    throw new OtpError(
      'INVALID_TOKEN_CODE',
      'Kode token redeem tidak valid',
    );
  }
  return trimmed;
}

function generateSixDigitOtp(): string {
  const n = crypto.getRandomValues(new Uint32Array(1))[0]! % 1_000_000;
  return String(n).padStart(6, '0');
}

function buildMessage(otp: string): string {
  const template =
    process.env.FONNTE_OTP_MESSAGE_TEMPLATE ?? DEFAULT_OTP_MESSAGE_TEMPLATE;
  return template.replaceAll('{otp}', otp);
}

async function markDbUsed(identifier: string): Promise<void> {
  await prisma.otpVerification.updateMany({
    where: {
      identifier,
      type: OTP_TYPE,
      isUsed: false,
    },
    data: { isUsed: true },
  });
}

export class OtpService {
  async generateOtp(req: GenerateOtpRequest): Promise<GenerateOtpResult> {
    let phone62: string;
    try {
      phone62 = normalizePhoneForFonnte(req.phone);
    } catch (error) {
      throw new OtpError(
        'INVALID_PHONE',
        error instanceof Error ? error.message : 'Nomor HP tidak valid',
      );
    }

    const redeemTokenCode = validateTokenCode(req.redeemTokenCode);
    const identifier = buildIdentifier(phone62, redeemTokenCode);
    const redis = getRedis();

    await prisma.otpVerification.updateMany({
      where: {
        identifier,
        type: OTP_TYPE,
        isUsed: false,
      },
      data: { isUsed: true },
    });
    await redis.del(identifier);

    const otpCode = generateSixDigitOtp();
    const expiryMinutes = otpExpiryMinutes();
    const expiredAt = new Date(Date.now() + expiryMinutes * 60_000);

    await redis.set(identifier, otpCode, 'EX', expiryMinutes * 60);

    await prisma.otpVerification.create({
      data: {
        identifier,
        otpCode,
        type: OTP_TYPE,
        isUsed: false,
        expiredAt,
      },
    });

    try {
      await fonnteService.sendWhatsappMessage(phone62, buildMessage(otpCode));
    } catch (error) {
      await redis.del(identifier);
      await markDbUsed(identifier);
      if (error instanceof FonnteError) {
        throw new OtpError('FONNTE_FAILED', error.message);
      }
      throw error;
    }

    return { expiresAt: expiredAt.toISOString() };
  }

  async verifyOtp(req: VerifyOtpRequest): Promise<{ verified: true }> {
    let phone62: string;
    try {
      phone62 = normalizePhoneForFonnte(req.phone);
    } catch (error) {
      throw new OtpError(
        'INVALID_PHONE',
        error instanceof Error ? error.message : 'Nomor HP tidak valid',
      );
    }

    const redeemTokenCode = validateTokenCode(req.redeemTokenCode);
    const otp = req.otp.trim();
    if (!/^\d{6}$/.test(otp)) {
      throw new OtpError('OTP_INVALID', 'Kode OTP tidak valid');
    }

    const identifier = buildIdentifier(phone62, redeemTokenCode);
    const redis = getRedis();

    const cached = await redis.get(identifier);
    if (cached !== null) {
      if (cached === otp) {
        await markDbUsed(identifier);
        await redis.del(identifier);
        return { verified: true };
      }
      throw new OtpError('OTP_INVALID', 'Kode OTP tidak valid');
    }

    const row = await prisma.otpVerification.findFirst({
      where: {
        identifier,
        type: OTP_TYPE,
        otpCode: otp,
        isUsed: false,
      },
      orderBy: { createdAt: 'desc' },
    });

    if (!row) {
      throw new OtpError('OTP_INVALID', 'Kode OTP tidak valid');
    }

    if (row.expiredAt <= new Date()) {
      throw new OtpError('OTP_EXPIRED', 'Kode OTP sudah kedaluwarsa');
    }

    await prisma.otpVerification.update({
      where: { id: row.id },
      data: { isUsed: true },
    });
    await redis.del(identifier);

    return { verified: true };
  }

  /** Test helper: build Redis/DB identifier key. */
  buildIdentifierForTest(phone: string, redeemTokenCode: string): string {
    return buildIdentifier(
      normalizePhoneForFonnte(phone),
      validateTokenCode(redeemTokenCode),
    );
  }
}

export const otpService = new OtpService();
