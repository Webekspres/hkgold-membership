import { prisma } from '../../../db';
import { IMemberService } from '../interfaces/member.interface';
import { MemberProfileData, UpdateMemberProfileRequest } from '../types/member.types';
import { authService } from '../../auth/services/auth.service';
import { addressService } from '../../address/services/address.service';

export class MemberService implements IMemberService {
  async getProfileByUserId(userId: string): Promise<MemberProfileData | null> {
    if (!userId) return null;

    // Baca data member + user + foto profil dalam satu query.
    // Catatan: address DIBACA lewat addressService (bukan join langsung) agar
    // transformasi region wilayah konsisten dengan modul Address.
    // ponytail: select eksplisit — kolom birth_date belum ada di MySQL lokal.
    // Ceiling: birthDate selalu null sampai migrasi; upgrade: ALTER + select birthDate.
    const member = await prisma.member.findUnique({
      where: { userId },
      select: {
        id: true,
        addressId: true,
        memberNumber: true,
        phoneNumber: true,
        currentTier: true,
        pointBalance: true,
        highestPoint: true,
        isSuspended: true,
        createdAt: true,
        updatedAt: true,
        user: {
          select: {
            id: true,
            email: true,
            fullName: true,
            role: true,
            isActive: true,
            profilePhoto: {
              select: {
                id: true,
                fileUrl: true,
              },
            },
          },
        },
      },
    });

    if (!member || !member.user) return null;

    // Ambil address lewat service publik modul Address (jika ada)
    const address = member.addressId
      ? await addressService.getById(member.addressId)
      : null;

    return {
      id: member.id,
      memberNumber: member.memberNumber,
      phoneNumber: member.phoneNumber,
      birthDate: null,
      currentTier: member.currentTier,
      pointBalance: member.pointBalance,
      highestPoint: member.highestPoint,
      isSuspended: member.isSuspended,
      user: {
        id: member.user.id,
        email: member.user.email,
        fullName: member.user.fullName,
        role: member.user.role,
        isActive: member.user.isActive,
        profilePhoto: member.user.profilePhoto
          ? {
              id: member.user.profilePhoto.id,
              fileUrl: member.user.profilePhoto.fileUrl
            }
          : null
      },
      address,
      createdAt: member.createdAt,
      updatedAt: member.updatedAt
    };
  }

  async updateProfileByUserId(
    userId: string,
    data: UpdateMemberProfileRequest
  ): Promise<MemberProfileData> {
    const { fullName, email, phoneNumber, birthDate, profilePhotoId, address } = data;

    const hasUserUpdate = fullName !== undefined || email !== undefined || profilePhotoId !== undefined;
    const hasMemberUpdate = phoneNumber !== undefined || birthDate !== undefined;
    const hasAddressUpdate = address !== undefined;

    if (!hasUserUpdate && !hasMemberUpdate && !hasAddressUpdate) {
      throw new Error('Tidak ada data yang diubah');
    }

    // Pastikan member ada sebelum memutasi apa pun
    const member = await prisma.member.findUnique({
      where: { userId },
      select: { id: true, addressId: true, phoneNumber: true }
    });
    if (!member) {
      throw new Error('Member tidak ditemukan');
    }

    // 1. Validasi email unique jika diubah
    if (email !== undefined) {
      const existingEmail = await prisma.user.findFirst({
        where: {
          email,
          id: { not: userId }
        }
      });
      if (existingEmail) {
        throw new Error('Email sudah digunakan oleh user lain');
      }
    }

    // 2. Validasi & normalisasi phone jika diubah
    let normalizedPhone: string | undefined;
    if (phoneNumber !== undefined) {
      // Reuse normalizePhoneNumber dari auth.service (import di atas atau inline)
      normalizedPhone = this.normalizePhoneNumber(phoneNumber);

      const existingPhone = await prisma.member.findFirst({
        where: {
          phoneNumber: normalizedPhone,
          id: { not: member.id }
        }
      });
      if (existingPhone) {
        throw new Error('Nomor HP sudah digunakan oleh member lain');
      }
    }

    // 3. Parse birthDate jika ada
    let parsedBirthDate: Date | null | undefined;
    if (birthDate !== undefined) {
      if (birthDate === null) {
        parsedBirthDate = null;
      } else {
        parsedBirthDate = new Date(birthDate);
        if (isNaN(parsedBirthDate.getTime())) {
          throw new Error('Format birthDate tidak valid (gunakan ISO 8601)');
        }
      }
    }

    // 4. Update field User (fullName, email, foto profil) via service auth
    if (hasUserUpdate) {
      await authService.updateUserProfile(userId, { fullName, profilePhotoId });

      // Email update langsung (authService.updateUserProfile belum support email)
      if (email !== undefined) {
        await prisma.user.update({
          where: { id: userId },
          data: { email }
        });
      }
    }

    // 5. Update field Member (phoneNumber, birthDate)
    if (hasMemberUpdate) {
      await prisma.member.update({
        where: { id: member.id },
        data: {
          ...(normalizedPhone !== undefined ? { phoneNumber: normalizedPhone } : {}),
          ...(parsedBirthDate !== undefined ? { birthDate: parsedBirthDate } : {})
        }
      });
    }

    // 6. Update alamat via service Address
    if (hasAddressUpdate) {
      if (!member.addressId) {
        throw new Error('Member belum memiliki alamat untuk diperbarui');
      }
      await addressService.update(member.addressId, address);
    }

    // Kembalikan profil terbaru hasil agregasi
    const updated = await this.getProfileByUserId(userId);
    if (!updated) {
      throw new Error('Gagal mengambil profil setelah update');
    }
    return updated;
  }

  // Helper: normalize phone number (reuse dari auth.service pattern)
  private normalizePhoneNumber(phone: string): string {
    let cleaned = phone.replace(/\D/g, '');

    if (cleaned.startsWith('08')) {
      cleaned = '62' + cleaned.slice(1);
    }

    if (!cleaned.startsWith('62')) {
      throw new Error('Nomor HP harus format Indonesia (+62 atau 08)');
    }

    if (cleaned.length < 11 || cleaned.length > 14) {
      throw new Error('Nomor HP tidak valid');
    }

    return '+' + cleaned;
  }
}

export const memberService = new MemberService();
