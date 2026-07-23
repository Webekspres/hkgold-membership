import { prisma } from '../../../db';
import { IAuthService } from '../interfaces/auth.interface';
import {
  RegisterRequest,
  LoginRequest,
  ChangePasswordRequest,
  ForgotPasswordSendOtpRequest,
  ForgotPasswordResetRequest,
  ForgotPasswordSendOtpResult,
  UpdateUserProfileRequest,
  AuthResponse,
  UserData,
} from '../types/auth.types';
import { jwtService } from './jwt.service';
import { AuthError } from '../errors/auth.error';
import { passwordResetOtpService } from '../../otp/services/password-reset-otp.service';
import { OtpError } from '../../otp/types/otp.types';
import { normalizePhoneForFonnte } from '../../otp/services/fonnte.service';

// Phone number validation & normalization
const normalizePhoneNumber = (phone: string): string => {
  // Remove all non-digits
  let cleaned = phone.replace(/\D/g, '');

  // Convert 08xxx to 62xxx
  if (cleaned.startsWith('08')) {
    cleaned = '62' + cleaned.slice(1);
  }

  // Ensure starts with 62
  if (!cleaned.startsWith('62')) {
    throw new Error('Nomor HP harus format Indonesia (+62 atau 08)');
  }

  // Validate length (62 + 9-12 digits = 11-14 total)
  if (cleaned.length < 11 || cleaned.length > 14) {
    throw new Error('Nomor HP tidak valid');
  }

  return '+' + cleaned;
};

// Member number: YYMM-NNNN (4-digit sequence resets each calendar month)
const generateMemberNumber = async (): Promise<string> => {
  const now = new Date();
  const prefix = `${String(now.getFullYear()).slice(-2)}${String(now.getMonth() + 1).padStart(2, '0')}`;

  const lastMember = await prisma.member.findFirst({
    where: { memberNumber: { startsWith: `${prefix}-` } },
    orderBy: { memberNumber: 'desc' },
    select: { memberNumber: true }
  });

  const lastSeq = lastMember
    ? parseInt(lastMember.memberNumber.split('-')[1] ?? '0', 10)
    : 0;

  if (Number.isNaN(lastSeq) || lastSeq >= 9999) {
    throw new Error('Kapasitas nomor member bulan ini penuh');
  }

  return `${prefix}-${String(lastSeq + 1).padStart(4, '0')}`;
};

// Email validation
const validateEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

/** YYMM-NNNN — ditolak di forgot-password (hanya email/HP). */
const isMemberNumber = (value: string): boolean => /^\d{4}-\d{4}$/.test(value.trim());

type ResolvedResetTarget = {
  userId: string;
  memberId: string;
  phoneNumber: string;
};

async function assertNoPendingPhoneChange(memberId: string): Promise<void> {
  const pending = await prisma.changePhoneApproval.findFirst({
    where: { memberId, status: 'PENDING' },
    select: { id: true },
  });
  if (pending) {
    throw new AuthError(
      'PENDING_PHONE_CHANGE',
      'Tidak bisa ubah password saat ada permintaan ganti nomor yang menunggu.',
    );
  }
}

async function resolveForgotPasswordTarget(
  identifier: string | undefined,
  userId?: string,
): Promise<ResolvedResetTarget> {
  if (userId) {
    const member = await prisma.member.findUnique({
      where: { userId },
      select: { id: true, phoneNumber: true, userId: true },
    });
    if (!member) {
      throw new AuthError('NOT_FOUND', 'Akun tidak ditemukan');
    }
    return {
      userId: member.userId,
      memberId: member.id,
      phoneNumber: member.phoneNumber,
    };
  }

  const trimmed = (identifier ?? '').trim();
  if (!trimmed) {
    throw new AuthError('VALIDATION', 'Email atau nomor HP wajib diisi');
  }

  if (isMemberNumber(trimmed)) {
    throw new AuthError(
      'VALIDATION',
      'Gunakan email atau nomor HP, bukan nomor member',
    );
  }

  if (trimmed.includes('@')) {
    if (!validateEmail(trimmed)) {
      throw new AuthError('VALIDATION', 'Format email tidak valid');
    }
    const user = await prisma.user.findUnique({
      where: { email: trimmed },
      select: {
        id: true,
        member: { select: { id: true, phoneNumber: true } },
      },
    });
    if (!user || !user.member) {
      throw new AuthError('NOT_FOUND', 'Akun tidak ditemukan');
    }
    return {
      userId: user.id,
      memberId: user.member.id,
      phoneNumber: user.member.phoneNumber,
    };
  }

  if (!/^[0-9+]+$/.test(trimmed)) {
    throw new AuthError('VALIDATION', 'Gunakan email atau nomor HP');
  }

  let searchPhone: string;
  try {
    searchPhone = normalizePhoneNumber(trimmed);
  } catch {
    throw new AuthError('VALIDATION', 'Nomor HP tidak valid');
  }

  // DB bisa simpan +62… (API register) atau 62… (Filament/seeder).
  const phoneVariants = searchPhone.startsWith('+')
    ? [searchPhone, searchPhone.slice(1)]
    : [searchPhone, `+${searchPhone}`];

  const member = await prisma.member.findFirst({
    where: { phoneNumber: { in: phoneVariants } },
    select: { id: true, userId: true, phoneNumber: true },
  });
  if (!member) {
    throw new AuthError('NOT_FOUND', 'Akun tidak ditemukan');
  }
  return {
    userId: member.userId,
    memberId: member.id,
    phoneNumber: member.phoneNumber,
  };
}

function assertWaPhone(phoneNumber: string): string {
  const raw = (phoneNumber ?? '').trim();
  if (!raw) {
    throw new AuthError(
      'WA_NOT_SET',
      'Kamu belum atur nomor WA. Hubungi admin',
    );
  }
  try {
    return normalizePhoneForFonnte(raw);
  } catch {
    throw new AuthError(
      'WA_NOT_SET',
      'Kamu belum atur nomor WA. Hubungi admin',
    );
  }
}

// Auth select: field yang dibutuhkan login/JWT saja (bukan full profile).
const authUserSelect = {
  id: true,
  email: true,
  fullName: true,
  password: true,
  role: true,
  isActive: true,
} as const;

const authMemberSelect = {
  id: true,
  memberNumber: true,
  phoneNumber: true,
  currentTier: true,
  pointBalance: true,
  isSuspended: true,
} as const;

export class AuthService implements IAuthService {
  async register(data: RegisterRequest): Promise<AuthResponse> {
    const { email, password, fullName, phoneNumber } = data;

    // Validate inputs
    if (!email || !password || !fullName || !phoneNumber) {
      throw new Error('Semua field wajib diisi');
    }

    if (!validateEmail(email)) {
      throw new Error('Format email tidak valid');
    }

    if (password.length < 8) {
      throw new Error('Password minimal 8 karakter');
    }

    // Normalize phone number
    const normalizedPhone = normalizePhoneNumber(phoneNumber);

    // Check duplicate email
    const existingEmail = await prisma.user.findUnique({
      where: { email },
      select: { id: true },
    });
    if (existingEmail) {
      throw new Error('Email sudah terdaftar');
    }

    // Check duplicate phone
    const existingPhone = await prisma.member.findUnique({
      where: { phoneNumber: normalizedPhone },
      select: { id: true },
    });
    if (existingPhone) {
      throw new Error('Nomor HP sudah terdaftar');
    }

    // Hash password
    const hashedPassword = await Bun.password.hash(password);

    // Generate member number
    const memberNumber = await generateMemberNumber();

    // Create User + Member in transaction
    const result = await prisma.$transaction(async (tx) => {
      const user = await tx.user.create({
        data: {
          email,
          password: hashedPassword,
          fullName,
          role: 'MEMBER',
          isActive: true
        },
        select: authUserSelect,
      });

      const member = await tx.member.create({
        data: {
          userId: user.id,
          memberNumber,
          phoneNumber: normalizedPhone,
          currentTier: 'SILVER',
          pointBalance: 0,
          highestPoint: 0
        },
        select: authMemberSelect,
      });

      return { user, member };
    });

    // Generate tokens
    const tokens = await jwtService.generateTokenPair({
      userId: result.user.id,
      memberId: result.member.id,
      role: result.user.role,
      isActive: result.user.isActive,
      isSuspended: result.member.isSuspended
    });

    return {
      accessToken: tokens.accessToken,
      refreshToken: tokens.refreshToken,
      user: {
        id: result.user.id,
        email: result.user.email,
        fullName: result.user.fullName,
        role: result.user.role,
        isActive: result.user.isActive
      },
      member: {
        id: result.member.id,
        memberNumber: result.member.memberNumber,
        phoneNumber: result.member.phoneNumber,
        currentTier: result.member.currentTier,
        pointBalance: result.member.pointBalance,
        isSuspended: result.member.isSuspended
      }
    };
  }

  async login(data: LoginRequest): Promise<AuthResponse> {
    const { identifier, password } = data;

    if (!identifier || !password) {
      throw new Error('Identifier dan password wajib diisi');
    }

    const trimmed = identifier.trim();
    let authUser: {
      id: string;
      email: string;
      fullName: string;
      password: string;
      role: string;
      isActive: boolean;
    };
    let authMember: {
      id: string;
      memberNumber: string;
      phoneNumber: string;
      currentTier: string;
      pointBalance: number;
      isSuspended: boolean;
    };

    const userSelect = authUserSelect;
    const memberSelect = authMemberSelect;

    if (trimmed.includes('@')) {
      // Login via email
      const user = await prisma.user.findUnique({
        where: { email: trimmed },
        select: {
          ...userSelect,
          member: { select: memberSelect },
        },
      });

      if (!user || !user.member) {
        throw new Error('User tidak ditemukan');
      }

      authUser = user;
      authMember = user.member;
    } else {
      // Login via phone number atau member number
      let searchIdentifier = trimmed;
      let phoneVariants: string[] = [];
      if (trimmed.match(/^[0-9+]/)) {
        try {
          searchIdentifier = normalizePhoneNumber(trimmed);
          phoneVariants = searchIdentifier.startsWith('+')
            ? [searchIdentifier, searchIdentifier.slice(1)]
            : [searchIdentifier, `+${searchIdentifier}`];
        } catch {
          // Not a valid phone, might be member number
        }
      }

      const member = await prisma.member.findFirst({
        where: {
          OR: [
            ...(phoneVariants.length > 0
              ? [{ phoneNumber: { in: phoneVariants } }]
              : [{ phoneNumber: searchIdentifier }]),
            { memberNumber: searchIdentifier },
          ],
        },
        select: {
          ...memberSelect,
          user: { select: userSelect },
        },
      });

      if (!member || !member.user) {
        throw new Error('User tidak ditemukan');
      }

      authUser = member.user;
      authMember = member;
    }

    const isValid = await Bun.password.verify(password, authUser.password);
    if (!isValid) {
      throw new Error('Password salah');
    }

    if (!authUser.isActive) {
      throw new Error('Akun Anda telah dinonaktifkan');
    }

    // Note: isSuspended does NOT block login (per AGENTS.md)
    const tokens = await jwtService.generateTokenPair({
      userId: authUser.id,
      memberId: authMember.id,
      role: authUser.role,
      isActive: authUser.isActive,
      isSuspended: authMember.isSuspended
    });

    return {
      accessToken: tokens.accessToken,
      refreshToken: tokens.refreshToken,
      user: {
        id: authUser.id,
        email: authUser.email,
        fullName: authUser.fullName,
        role: authUser.role,
        isActive: authUser.isActive
      },
      member: {
        id: authMember.id,
        memberNumber: authMember.memberNumber,
        phoneNumber: authMember.phoneNumber,
        currentTier: authMember.currentTier,
        pointBalance: authMember.pointBalance,
        isSuspended: authMember.isSuspended
      }
    };
  }

  async changePassword(userId: string, data: ChangePasswordRequest): Promise<{ message: string }> {
    const { oldPassword, newPassword } = data;

    if (!oldPassword || !newPassword) {
      throw new Error('Old password dan new password wajib diisi');
    }

    if (newPassword.length < 8) {
      throw new Error('Password baru minimal 8 karakter');
    }

    // Get user
    const user = await prisma.user.findUnique({
      where: { id: userId },
      include: { member: { select: { id: true } } },
    });

    if (!user) {
      throw new Error('User tidak ditemukan');
    }

    if (user.member) {
      await assertNoPendingPhoneChange(user.member.id);
    }

    // Verify old password
    const isValid = await Bun.password.verify(oldPassword, user.password);
    if (!isValid) {
      throw new Error('Password lama salah');
    }

    // Check if new password same as old
    const isSame = await Bun.password.verify(newPassword, user.password);
    if (isSame) {
      throw new Error('Password baru tidak boleh sama dengan password lama');
    }

    // Hash new password
    const hashedPassword = await Bun.password.hash(newPassword);

    // Update password
    await prisma.user.update({
      where: { id: userId },
      data: { password: hashedPassword }
    });

    return { message: 'Password berhasil diubah' };
  }

  async sendForgotPasswordOtp(
    data: ForgotPasswordSendOtpRequest,
    userId?: string,
  ): Promise<ForgotPasswordSendOtpResult> {
    const target = await resolveForgotPasswordTarget(data.identifier, userId);
    await assertNoPendingPhoneChange(target.memberId);
    assertWaPhone(target.phoneNumber);

    try {
      return await passwordResetOtpService.sendOtp(target.phoneNumber);
    } catch (error) {
      if (error instanceof OtpError) {
        throw new AuthError(error.code, error.message);
      }
      throw error;
    }
  }

  async resetPasswordWithOtp(
    data: ForgotPasswordResetRequest,
    userId?: string,
  ): Promise<{ message: string }> {
    const { otp, newPassword } = data;

    if (!otp || !newPassword) {
      throw new AuthError('VALIDATION', 'OTP dan password baru wajib diisi');
    }

    if (newPassword.length < 8) {
      throw new AuthError('VALIDATION', 'Password baru minimal 8 karakter');
    }

    const target = await resolveForgotPasswordTarget(data.identifier, userId);
    await assertNoPendingPhoneChange(target.memberId);
    assertWaPhone(target.phoneNumber);

    try {
      await passwordResetOtpService.verifyOtp(target.phoneNumber, otp);
    } catch (error) {
      if (error instanceof OtpError) {
        throw new AuthError(error.code, error.message);
      }
      throw error;
    }

    const hashedPassword = await Bun.password.hash(newPassword);
    await prisma.user.update({
      where: { id: target.userId },
      data: { password: hashedPassword },
    });

    return { message: 'Password berhasil diubah' };
  }

  async updateUserProfile(userId: string, data: UpdateUserProfileRequest): Promise<UserData> {
    const { fullName, profilePhotoId } = data;

    // Minimal ada satu field yang diubah
    if (fullName === undefined && profilePhotoId === undefined) {
      throw new Error('Tidak ada data yang diubah');
    }

    if (fullName !== undefined && fullName.trim().length === 0) {
      throw new Error('Nama lengkap tidak boleh kosong');
    }

    const user = await prisma.user.findUnique({ where: { id: userId } });
    if (!user) {
      throw new Error('User tidak ditemukan');
    }

    // Validasi FK media jika foto profil di-set (bukan null)
    if (profilePhotoId !== undefined && profilePhotoId !== null) {
      const media = await prisma.media.findUnique({ where: { id: profilePhotoId } });
      if (!media) {
        throw new Error('Media foto profil tidak ditemukan');
      }
    }

    const updated = await prisma.user.update({
      where: { id: userId },
      data: {
        ...(fullName !== undefined ? { fullName: fullName.trim() } : {}),
        ...(profilePhotoId !== undefined ? { profilePhotoId } : {})
      }
    });

    return {
      id: updated.id,
      email: updated.email,
      fullName: updated.fullName,
      role: updated.role,
      isActive: updated.isActive
    };
  }

  async validateUser(identifier: string, password: string): Promise<AuthResponse> {
    return this.login({ identifier, password });
  }
}

export const authService = new AuthService();
