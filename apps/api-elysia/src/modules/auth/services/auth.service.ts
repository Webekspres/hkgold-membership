import { prisma } from '../../../db';
import { IAuthService } from '../interfaces/auth.interface';
import {
  RegisterRequest,
  LoginRequest,
  ChangePasswordRequest,
  AuthResponse,
  UserData,
  MemberData
} from '../types/auth.types';
import { jwtService } from './jwt.service';

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

// Member number generation: HK + letter (A-Z) + 7 digits (0000001-9999999)
const generateMemberNumber = async (): Promise<string> => {
  const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

  // Get last member number from database
  const lastMember = await prisma.member.findFirst({
    orderBy: { memberNumber: 'desc' },
    select: { memberNumber: true }
  });

  if (!lastMember) {
    // First member ever
    return 'HKA0000001';
  }

  const lastNumber = lastMember.memberNumber;
  const lastLetter = lastNumber.charAt(2); // HKA -> A
  const lastDigits = parseInt(lastNumber.slice(3)); // 0000001 -> 1

  // Increment
  if (lastDigits < 9999999) {
    const newDigits = (lastDigits + 1).toString().padStart(7, '0');
    return `HK${lastLetter}${newDigits}`;
  }

  // Rollover to next letter
  const letterIndex = letters.indexOf(lastLetter);
  if (letterIndex >= 25) {
    throw new Error('Member number capacity exceeded');
  }

  const newLetter = letters[letterIndex + 1];
  return `HK${newLetter}0000001`;
};

// Email validation
const validateEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

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
      where: { email }
    });
    if (existingEmail) {
      throw new Error('Email sudah terdaftar');
    }

    // Check duplicate phone
    const existingPhone = await prisma.member.findUnique({
      where: { phoneNumber: normalizedPhone }
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
        }
      });

      const member = await tx.member.create({
        data: {
          userId: user.id,
          memberNumber,
          phoneNumber: normalizedPhone,
          currentTier: 'SILVER',
          pointBalance: 0,
          highestPoint: 0
        }
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

    // Normalize identifier if it's a phone number
    let searchIdentifier = identifier;
    if (identifier.match(/^[0-9+]/)) {
      try {
        searchIdentifier = normalizePhoneNumber(identifier);
      } catch {
        // Not a valid phone, might be member number
      }
    }

    // Find user by phone number OR member number
    const member = await prisma.member.findFirst({
      where: {
        OR: [
          { phoneNumber: searchIdentifier },
          { memberNumber: searchIdentifier }
        ]
      },
      include: {
        user: true
      }
    });

    if (!member || !member.user) {
      throw new Error('User tidak ditemukan');
    }

    // Verify password
    const isValid = await Bun.password.verify(password, member.user.password);
    if (!isValid) {
      throw new Error('Password salah');
    }

    // Check if user is active
    if (!member.user.isActive) {
      throw new Error('Akun Anda telah dinonaktifkan');
    }

    // Note: isSuspended does NOT block login (per AGENTS.md)
    // Generate tokens
    const tokens = await jwtService.generateTokenPair({
      userId: member.user.id,
      memberId: member.id,
      role: member.user.role,
      isActive: member.user.isActive,
      isSuspended: member.isSuspended
    });

    return {
      accessToken: tokens.accessToken,
      refreshToken: tokens.refreshToken,
      user: {
        id: member.user.id,
        email: member.user.email,
        fullName: member.user.fullName,
        role: member.user.role,
        isActive: member.user.isActive
      },
      member: {
        id: member.id,
        memberNumber: member.memberNumber,
        phoneNumber: member.phoneNumber,
        currentTier: member.currentTier,
        pointBalance: member.pointBalance,
        isSuspended: member.isSuspended
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
      where: { id: userId }
    });

    if (!user) {
      throw new Error('User tidak ditemukan');
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

  async validateUser(identifier: string, password: string): Promise<AuthResponse> {
    return this.login({ identifier, password });
  }
}

export const authService = new AuthService();
