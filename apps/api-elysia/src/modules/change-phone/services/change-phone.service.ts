import { prisma } from '../../../db';
import { getRedis } from '../../../lib/redis';
import {
  FonnteError,
  fonnteService,
  normalizePhoneForFonnte,
} from '../../otp/services/fonnte.service';
import {
  DEFAULT_OTP_MESSAGE_TEMPLATE,
  OtpError,
  type OtpTypeValue,
} from '../../otp/types/otp.types';
import {
  ChangePhoneError,
  type ChangePhoneIntent,
  type ChangePhoneSourceValue,
  type ChangePhoneStatusDto,
} from '../types/change-phone.types';

const CHALLENGE_TTL_SEC = 600;
const INTENT_TTL_SEC = 600;

function otpExpiryMinutes(): number {
  return Number(process.env.CHANGE_PHONE_OTP_EXPIRY_MINUTES ?? 5);
}

function resendCooldownSeconds(): number {
  return Number(process.env.CHANGE_PHONE_OTP_RESEND_SECONDS ?? 60);
}

function challengeKey(memberId: string): string {
  return `change-phone:challenge:${memberId}`;
}

function intentKey(memberId: string): string {
  return `change-phone:intent:${memberId}`;
}

function otpIdentifier(type: OtpTypeValue, phone62: string): string {
  return `otp:${type}:${phone62}`;
}

function otpCooldownKey(type: OtpTypeValue, phone62: string): string {
  return `otp:${type}:cooldown:${phone62}`;
}

function generateSixDigitOtp(): string {
  const n = crypto.getRandomValues(new Uint32Array(1))[0]! % 1_000_000;
  return String(n).padStart(6, '0');
}

function generateChallengeToken(): string {
  return Buffer.from(crypto.getRandomValues(new Uint8Array(24))).toString('base64url');
}

function buildOtpMessage(otp: string): string {
  const template =
    process.env.FONNTE_OTP_MESSAGE_TEMPLATE ?? DEFAULT_OTP_MESSAGE_TEMPLATE;
  return template.replaceAll('{otp}', otp);
}

function maskPhone(phone62: string): string {
  if (phone62.length <= 6) return phone62;
  return `${phone62.slice(0, 4)}****${phone62.slice(-3)}`;
}

function toStatusDto(row: {
  id: string;
  status: string;
  source: string;
  oldPhoneNumber: string;
  newPhoneNumber: string;
  reason: string | null;
  actionNotes: string | null;
  createdAt: Date;
  processedAt: Date | null;
}): ChangePhoneStatusDto {
  return {
    id: row.id,
    status: row.status as ChangePhoneStatusDto['status'],
    source: row.source as ChangePhoneStatusDto['source'],
    oldPhoneNumber: row.oldPhoneNumber,
    newPhoneNumber: row.newPhoneNumber,
    reason: row.reason,
    actionNotes: row.actionNotes,
    createdAt: row.createdAt.toISOString(),
    processedAt: row.processedAt?.toISOString() ?? null,
  };
}

async function markDbOtpUsed(identifier: string, type: OtpTypeValue): Promise<void> {
  await prisma.otpVerification.updateMany({
    where: { identifier, type, isUsed: false },
    data: { isUsed: true },
  });
}

async function sendTypedOtp(
  type: OtpTypeValue,
  phone: string,
): Promise<{ expiresAt: string; resendAvailableAt: string; maskedPhone: string }> {
  let phone62: string;
  try {
    phone62 = normalizePhoneForFonnte(phone);
  } catch (error) {
    throw new ChangePhoneError(
      'INVALID_PHONE',
      error instanceof Error ? error.message : 'Nomor HP tidak valid',
    );
  }

  const identifier = otpIdentifier(type, phone62);
  const cooldownKey = otpCooldownKey(type, phone62);
  const redis = getRedis();

  const cooldownTtl = await redis.ttl(cooldownKey);
  if (cooldownTtl > 0) {
    throw new ChangePhoneError(
      'RESEND_COOLDOWN',
      `Tunggu ${cooldownTtl} detik sebelum kirim ulang OTP`,
    );
  }

  await prisma.otpVerification.updateMany({
    where: { identifier, type, isUsed: false },
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
      type,
      isUsed: false,
      expiredAt,
    },
  });

  try {
    await fonnteService.sendWhatsappMessage(phone62, buildOtpMessage(otpCode));
  } catch (error) {
    await redis.del(identifier);
    await redis.del(cooldownKey);
    await markDbOtpUsed(identifier, type);
    if (error instanceof FonnteError) {
      throw new ChangePhoneError('FONNTE_FAILED', error.message);
    }
    throw error;
  }

  return {
    expiresAt: expiredAt.toISOString(),
    resendAvailableAt: resendAvailableAt.toISOString(),
    maskedPhone: maskPhone(phone62),
  };
}

async function verifyTypedOtp(
  type: OtpTypeValue,
  phone: string,
  otpRaw: string,
): Promise<void> {
  let phone62: string;
  try {
    phone62 = normalizePhoneForFonnte(phone);
  } catch (error) {
    throw new ChangePhoneError(
      'INVALID_PHONE',
      error instanceof Error ? error.message : 'Nomor HP tidak valid',
    );
  }

  const otp = otpRaw.trim();
  if (!/^\d{6}$/.test(otp)) {
    throw new ChangePhoneError('OTP_INVALID', 'Kode OTP tidak valid');
  }

  const identifier = otpIdentifier(type, phone62);
  const redis = getRedis();
  const cached = await redis.get(identifier);

  if (cached !== null) {
    if (cached === otp) {
      await markDbOtpUsed(identifier, type);
      await redis.del(identifier);
      return;
    }
    throw new ChangePhoneError('OTP_INVALID', 'Kode OTP tidak valid');
  }

  const row = await prisma.otpVerification.findFirst({
    where: { identifier, type, otpCode: otp, isUsed: false },
    orderBy: { createdAt: 'desc' },
  });

  if (!row) {
    throw new ChangePhoneError('OTP_INVALID', 'Kode OTP tidak valid');
  }
  if (row.expiredAt <= new Date()) {
    throw new ChangePhoneError('OTP_EXPIRED', 'Kode OTP sudah kedaluwarsa');
  }

  await prisma.otpVerification.update({
    where: { id: row.id },
    data: { isUsed: true },
  });
  await redis.del(identifier);
}

async function sendWaFailSoft(phone: string, message: string): Promise<void> {
  try {
    await fonnteService.sendWhatsappMessage(phone, message);
  } catch (error) {
    console.error('[change-phone] WA fail-soft:', error);
  }
}

export class ChangePhoneService {
  async hasPending(memberId: string): Promise<boolean> {
    const count = await prisma.changePhoneApproval.count({
      where: { memberId, status: 'PENDING' },
    });
    return count > 0;
  }

  async assertNoPending(memberId: string): Promise<void> {
    if (await this.hasPending(memberId)) {
      throw new ChangePhoneError(
        'PENDING_EXISTS',
        'Masih ada permintaan ganti nomor yang menunggu. Batalkan dulu atau tunggu admin.',
      );
    }
  }

  async getStatus(memberId: string): Promise<ChangePhoneStatusDto | null> {
    const pending = await prisma.changePhoneApproval.findFirst({
      where: { memberId, status: 'PENDING' },
      orderBy: { createdAt: 'desc' },
    });
    if (pending) return toStatusDto(pending);

    const latest = await prisma.changePhoneApproval.findFirst({
      where: {
        memberId,
        status: { in: ['REJECTED', 'APPROVED', 'CANCELLED'] },
      },
      orderBy: { createdAt: 'desc' },
    });
    return latest ? toStatusDto(latest) : null;
  }

  private async getMemberPhone(memberId: string): Promise<{
    id: string;
    phoneNumber: string;
  }> {
    const member = await prisma.member.findFirst({
      where: { id: memberId, deletedAt: null },
      select: { id: true, phoneNumber: true },
    });
    if (!member) {
      throw new ChangePhoneError('NOT_FOUND', 'Member tidak ditemukan');
    }
    return member;
  }

  private async assertNewPhoneAvailable(
    memberId: string,
    newPhone62: string,
  ): Promise<void> {
    const taken = await prisma.member.findFirst({
      where: {
        phoneNumber: newPhone62,
        id: { not: memberId },
        deletedAt: null,
      },
      select: { id: true },
    });
    if (taken) {
      throw new ChangePhoneError(
        'PHONE_IN_USE',
        'Nomor HP baru sudah digunakan member lain',
      );
    }

    const pendingOther = await prisma.changePhoneApproval.findFirst({
      where: {
        newPhoneNumber: newPhone62,
        status: 'PENDING',
        OR: [{ memberId: { not: memberId } }, { memberId: null }],
      },
      select: { id: true },
    });
    if (pendingOther) {
      throw new ChangePhoneError(
        'PHONE_IN_USE',
        'Nomor HP baru sedang diajukan member lain',
      );
    }
  }

  async sendOtpOld(memberId: string) {
    await this.assertNoPending(memberId);
    const member = await this.getMemberPhone(memberId);
    return sendTypedOtp('CHANGE_PHONE_OLD', member.phoneNumber);
  }

  async verifyOtpOld(memberId: string, otp: string): Promise<{ challenge: string }> {
    await this.assertNoPending(memberId);
    const member = await this.getMemberPhone(memberId);
    await verifyTypedOtp('CHANGE_PHONE_OLD', member.phoneNumber, otp);

    const challenge = generateChallengeToken();
    await getRedis().set(challengeKey(memberId), challenge, 'EX', CHALLENGE_TTL_SEC);
    return { challenge };
  }

  async sendOtpNew(
    memberId: string,
    input: {
      newPhone: string;
      challenge: string;
    },
  ) {
    await this.assertNoPending(memberId);
    const member = await this.getMemberPhone(memberId);

    let newPhone62: string;
    try {
      newPhone62 = normalizePhoneForFonnte(input.newPhone);
    } catch (error) {
      throw new ChangePhoneError(
        'INVALID_PHONE',
        error instanceof Error ? error.message : 'Nomor HP tidak valid',
      );
    }

    const old62 = normalizePhoneForFonnte(member.phoneNumber);
    if (newPhone62 === old62) {
      throw new ChangePhoneError(
        'PHONE_SAME',
        'Nomor baru harus berbeda dari nomor saat ini',
      );
    }

    await this.assertNewPhoneAvailable(memberId, newPhone62);

    const expected = await getRedis().get(challengeKey(memberId));
    if (!expected || expected !== input.challenge) {
      throw new ChangePhoneError(
        'INVALID_CHALLENGE',
        'Verifikasi nomor lama belum valid atau sudah kedaluwarsa',
      );
    }

    const intent: ChangePhoneIntent = {
      newPhone: newPhone62,
      source: 'SELF_SERVICE',
      reason: null,
    };
    await getRedis().set(
      intentKey(memberId),
      JSON.stringify(intent),
      'EX',
      INTENT_TTL_SEC,
    );

    return sendTypedOtp('CHANGE_PHONE_NEW', newPhone62);
  }

  /** Path admin-assisted: nomor lama tidak bisa diakses — tanpa OTP, langsung PENDING. */
  async requestAdminAssisted(
    memberId: string,
    input: { newPhone: string; reason: string },
  ): Promise<ChangePhoneStatusDto> {
    await this.assertNoPending(memberId);
    const member = await this.getMemberPhone(memberId);

    const reason = input.reason.trim();
    if (!reason) {
      throw new ChangePhoneError(
        'VALIDATION',
        'Alasan wajib diisi jika nomor lama tidak bisa diakses',
      );
    }

    let newPhone62: string;
    try {
      newPhone62 = normalizePhoneForFonnte(input.newPhone);
    } catch (error) {
      throw new ChangePhoneError(
        'INVALID_PHONE',
        error instanceof Error ? error.message : 'Nomor HP tidak valid',
      );
    }

    const old62 = normalizePhoneForFonnte(member.phoneNumber);
    if (newPhone62 === old62) {
      throw new ChangePhoneError(
        'PHONE_SAME',
        'Nomor baru harus berbeda dari nomor saat ini',
      );
    }

    await this.assertNewPhoneAvailable(memberId, newPhone62);

    const row = await prisma.changePhoneApproval.create({
      data: {
        memberId,
        requestedById: null,
        approvedById: null,
        oldPhoneNumber: old62,
        newPhoneNumber: newPhone62,
        source: 'ADMIN_ASSISTED',
        status: 'PENDING',
        reason,
      },
    });

    return toStatusDto(row);
  }

  async confirm(memberId: string, otp: string): Promise<{
    outcome: 'APPROVED';
    status: ChangePhoneStatusDto;
    forceLogout: true;
  }> {
    await this.assertNoPending(memberId);
    const member = await this.getMemberPhone(memberId);

    const rawIntent = await getRedis().get(intentKey(memberId));
    if (!rawIntent) {
      throw new ChangePhoneError(
        'INTENT_MISSING',
        'Sesi ganti nomor kedaluwarsa. Ulangi dari awal',
      );
    }

    let intent: ChangePhoneIntent;
    try {
      intent = JSON.parse(rawIntent) as ChangePhoneIntent;
    } catch {
      throw new ChangePhoneError('INTENT_MISSING', 'Sesi ganti nomor tidak valid');
    }

    if (intent.source !== 'SELF_SERVICE') {
      throw new ChangePhoneError('INTENT_MISSING', 'Sesi ganti nomor tidak valid');
    }

    await verifyTypedOtp('CHANGE_PHONE_NEW', intent.newPhone, otp);
    await this.assertNewPhoneAvailable(memberId, intent.newPhone);

    const oldPhone = normalizePhoneForFonnte(member.phoneNumber);
    const now = new Date();
    const row = await prisma.$transaction(async (tx) => {
      await tx.member.update({
        where: { id: memberId },
        data: {
          phoneNumber: intent.newPhone,
          phoneChangedAt: now,
        },
      });
      return tx.changePhoneApproval.create({
        data: {
          memberId,
          requestedById: null,
          approvedById: null,
          oldPhoneNumber: oldPhone,
          newPhoneNumber: intent.newPhone,
          source: 'SELF_SERVICE',
          status: 'APPROVED',
          reason: intent.reason,
          processedAt: now,
        },
      });
    });

    await getRedis().del(intentKey(memberId));
    await getRedis().del(challengeKey(memberId));

    await sendWaFailSoft(
      oldPhone,
      `HK GOLD VIP: Nomor HP akun Anda berhasil diganti dari ${maskPhone(oldPhone)} ke ${maskPhone(intent.newPhone)}. Jika bukan Anda, hubungi admin segera.`,
    );
    await sendWaFailSoft(
      intent.newPhone,
      `HK GOLD VIP: Nomor HP ini sekarang terhubung ke akun member Anda. Silakan login ulang di aplikasi.`,
    );

    return {
      outcome: 'APPROVED',
      status: toStatusDto(row),
      forceLogout: true,
    };
  }

  async cancel(memberId: string): Promise<ChangePhoneStatusDto> {
    const pending = await prisma.changePhoneApproval.findFirst({
      where: { memberId, status: 'PENDING' },
      orderBy: { createdAt: 'desc' },
    });
    if (!pending) {
      throw new ChangePhoneError('NO_PENDING', 'Tidak ada permintaan yang bisa dibatalkan');
    }

    const updated = await prisma.changePhoneApproval.update({
      where: { id: pending.id },
      data: {
        status: 'CANCELLED',
        processedAt: new Date(),
      },
    });
    return toStatusDto(updated);
  }

  async approveByAdmin(
    approvalId: string,
    staffId: number,
    actionNotes?: string,
  ): Promise<ChangePhoneStatusDto> {
    const row = await prisma.changePhoneApproval.findUnique({
      where: { id: approvalId },
    });
    if (!row || !row.memberId) {
      throw new ChangePhoneError('NOT_FOUND', 'Permintaan tidak ditemukan');
    }
    if (row.status !== 'PENDING') {
      throw new ChangePhoneError(
        'ALREADY_PROCESSED',
        'Permintaan sudah diproses',
      );
    }

    await this.assertNewPhoneAvailable(row.memberId, row.newPhoneNumber);

    const now = new Date();
    const updated = await prisma.$transaction(async (tx) => {
      await tx.member.update({
        where: { id: row.memberId! },
        data: {
          phoneNumber: row.newPhoneNumber,
          phoneChangedAt: now,
        },
      });
      return tx.changePhoneApproval.update({
        where: { id: approvalId },
        data: {
          status: 'APPROVED',
          approvedById: staffId,
          actionNotes: actionNotes?.trim() || null,
          processedAt: now,
        },
      });
    });

    await sendWaFailSoft(
      row.oldPhoneNumber,
      `HK GOLD VIP: Permintaan ganti nomor Anda disetujui. Nomor lama ${maskPhone(row.oldPhoneNumber)} diganti ke ${maskPhone(row.newPhoneNumber)}.`,
    );
    await sendWaFailSoft(
      row.newPhoneNumber,
      `HK GOLD VIP: Nomor HP ini sekarang terhubung ke akun member Anda. Silakan login ulang di aplikasi.`,
    );

    return toStatusDto(updated);
  }

  async rejectByAdmin(
    approvalId: string,
    staffId: number,
    actionNotes: string,
  ): Promise<ChangePhoneStatusDto> {
    const notes = actionNotes.trim();
    if (!notes) {
      throw new ChangePhoneError('VALIDATION', 'Catatan penolakan wajib diisi');
    }

    const row = await prisma.changePhoneApproval.findUnique({
      where: { id: approvalId },
    });
    if (!row) {
      throw new ChangePhoneError('NOT_FOUND', 'Permintaan tidak ditemukan');
    }
    if (row.status !== 'PENDING') {
      throw new ChangePhoneError(
        'ALREADY_PROCESSED',
        'Permintaan sudah diproses',
      );
    }

    const updated = await prisma.changePhoneApproval.update({
      where: { id: approvalId },
      data: {
        status: 'REJECTED',
        approvedById: staffId,
        actionNotes: notes,
        processedAt: new Date(),
      },
    });

    await sendWaFailSoft(
      row.oldPhoneNumber,
      `HK GOLD VIP: Permintaan ganti nomor HP Anda ditolak. Catatan: ${notes}`,
    );

    return toStatusDto(updated);
  }
}

export const changePhoneService = new ChangePhoneService();

/** Map OtpError from shared helpers if needed by callers */
export function mapOtpError(error: unknown): never {
  if (error instanceof OtpError) {
    throw new ChangePhoneError(
      error.code as ChangePhoneError['code'],
      error.message,
    );
  }
  throw error;
}
