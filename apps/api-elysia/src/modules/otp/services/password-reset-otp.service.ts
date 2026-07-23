import { prisma } from '../../../db';
import { getRedis } from '../../../lib/redis';
import {
  FonnteError,
  fonnteService,
  normalizePhoneForFonnte,
} from './fonnte.service';
import {
  DEFAULT_OTP_MESSAGE_TEMPLATE,
  OtpError,
} from '../types/otp.types';

const OTP_TYPE = 'PASSWORD_RESET' as const;

function otpExpiryMinutes(): number {
  return Number(process.env.PASSWORD_RESET_OTP_EXPIRY_MINUTES ?? 5);
}

function resendCooldownSeconds(): number {
  return Number(process.env.PASSWORD_RESET_OTP_RESEND_SECONDS ?? 60);
}

function buildIdentifier(phone62: string): string {
  return `otp:${OTP_TYPE}:${phone62}`;
}

function buildCooldownKey(phone62: string): string {
  return `otp:${OTP_TYPE}:cooldown:${phone62}`;
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

function maskPhone(phone62: string): string {
  if (phone62.length <= 6) return phone62;
  return `${phone62.slice(0, 4)}****${phone62.slice(-3)}`;
}

export interface PasswordResetOtpSendResult {
  expiresAt: string;
  resendAvailableAt: string;
  maskedPhone: string;
}

export class PasswordResetOtpService {
  async sendOtp(phone: string): Promise<PasswordResetOtpSendResult> {
    let phone62: string;
    try {
      phone62 = normalizePhoneForFonnte(phone);
    } catch (error) {
      throw new OtpError(
        'WA_NOT_SET',
        'Kamu belum atur nomor WA. Hubungi admin',
      );
    }

    const identifier = buildIdentifier(phone62);
    const cooldownKey = buildCooldownKey(phone62);
    const redis = getRedis();

    const cooldownTtl = await redis.ttl(cooldownKey);
    if (cooldownTtl > 0) {
      throw new OtpError(
        'RESEND_COOLDOWN',
        `Tunggu ${cooldownTtl} detik sebelum kirim ulang OTP`,
      );
    }

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
    const cooldownSeconds = resendCooldownSeconds();
    const expiredAt = new Date(Date.now() + expiryMinutes * 60_000);
    const resendAvailableAt = new Date(Date.now() + cooldownSeconds * 1000);

    await redis.set(identifier, otpCode, 'EX', expiryMinutes * 60);
    await redis.set(cooldownKey, '1', 'EX', cooldownSeconds);

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
      await redis.del(cooldownKey);
      await markDbUsed(identifier);
      if (error instanceof FonnteError) {
        throw new OtpError('FONNTE_FAILED', error.message);
      }
      throw error;
    }

    return {
      expiresAt: expiredAt.toISOString(),
      resendAvailableAt: resendAvailableAt.toISOString(),
      maskedPhone: maskPhone(phone62),
    };
  }

  async verifyOtp(phone: string, otpRaw: string): Promise<{ verified: true }> {
    let phone62: string;
    try {
      phone62 = normalizePhoneForFonnte(phone);
    } catch (error) {
      throw new OtpError(
        'INVALID_PHONE',
        error instanceof Error ? error.message : 'Nomor HP tidak valid',
      );
    }

    const otp = otpRaw.trim();
    if (!/^\d{6}$/.test(otp)) {
      throw new OtpError('OTP_INVALID', 'Kode OTP tidak valid');
    }

    const identifier = buildIdentifier(phone62);
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

  /** Test helper */
  buildIdentifierForTest(phone: string): string {
    return buildIdentifier(normalizePhoneForFonnte(phone));
  }

  buildCooldownKeyForTest(phone: string): string {
    return buildCooldownKey(normalizePhoneForFonnte(phone));
  }
}

export const passwordResetOtpService = new PasswordResetOtpService();
