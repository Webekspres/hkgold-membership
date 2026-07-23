import { prisma } from '../../../db';
import { IMemberService } from '../interfaces/member.interface';
import {
  MemberGender,
  MemberProfileData,
  UpdateMemberProfileRequest
} from '../types/member.types';
import { authService } from '../../auth/services/auth.service';
import { addressService } from '../../address/services/address.service';
import { mediaService } from '../../media/services/media.service';

const GENDERS: MemberGender[] = ['MALE', 'FEMALE'];

function parseBirthDate(value: string | null): Date | null {
  if (value === null) return null;

  if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    throw new Error('Format birthDate tidak valid (gunakan YYYY-MM-DD)');
  }

  const parsed = new Date(`${value}T00:00:00.000Z`);
  if (Number.isNaN(parsed.getTime())) {
    throw new Error('Format birthDate tidak valid (gunakan YYYY-MM-DD)');
  }

  const today = new Date();
  const todayUtc = new Date(
    Date.UTC(today.getUTCFullYear(), today.getUTCMonth(), today.getUTCDate())
  );
  if (parsed.getTime() > todayUtc.getTime()) {
    throw new Error('Tanggal lahir tidak boleh di masa depan');
  }

  return parsed;
}

function parseGender(value: MemberGender | null): MemberGender | null {
  if (value === null) return null;
  if (!GENDERS.includes(value)) {
    throw new Error('Gender tidak valid (MALE atau FEMALE)');
  }
  return value;
}

export class MemberService implements IMemberService {
  async getProfileByUserId(userId: string): Promise<MemberProfileData | null> {
    if (!userId) return null;

    const member = await prisma.member.findUnique({
      where: { userId },
      select: {
        id: true,
        addressId: true,
        memberNumber: true,
        phoneNumber: true,
        birthDate: true,
        gender: true,
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
                fileUrl: true
              }
            }
          }
        }
      }
    });

    if (!member || !member.user) return null;

    const address = member.addressId
      ? await addressService.getById(member.addressId)
      : null;

    return {
      id: member.id,
      memberNumber: member.memberNumber,
      phoneNumber: member.phoneNumber,
      birthDate: member.birthDate,
      gender: (member.gender as MemberGender | null) ?? null,
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
    const { fullName, birthDate, gender, address } = data;

    const hasUserUpdate = fullName !== undefined;
    const hasMemberUpdate = birthDate !== undefined || gender !== undefined;
    const hasAddressUpdate = address !== undefined;

    if (!hasUserUpdate && !hasMemberUpdate && !hasAddressUpdate) {
      throw new Error('Tidak ada data yang diubah');
    }

    const member = await prisma.member.findUnique({
      where: { userId },
      select: { id: true, addressId: true }
    });
    if (!member) {
      throw new Error('Member tidak ditemukan');
    }

    let trimmedName: string | undefined;
    if (fullName !== undefined) {
      trimmedName = fullName.trim();
      if (trimmedName.length === 0) {
        throw new Error('Nama lengkap tidak boleh kosong');
      }
      if (trimmedName.length > 150) {
        throw new Error('Nama lengkap maksimal 150 karakter');
      }
    }

    const parsedBirthDate =
      birthDate !== undefined ? parseBirthDate(birthDate) : undefined;
    const parsedGender = gender !== undefined ? parseGender(gender) : undefined;

    if (address !== undefined) {
      const street = address.street?.trim() ?? '';
      if (!address.villageId || !address.postalCodeId) {
        throw new Error('villageId dan postalCodeId wajib diisi');
      }
      if (!street) {
        throw new Error('Alamat jalan (street) wajib diisi');
      }
      await addressService.assertRegionPair(address.villageId, address.postalCodeId);
    }

    await prisma.$transaction(async (tx) => {
      if (trimmedName !== undefined) {
        await tx.user.update({
          where: { id: userId },
          data: { fullName: trimmedName }
        });
      }

      if (parsedBirthDate !== undefined || parsedGender !== undefined) {
        await tx.member.update({
          where: { id: member.id },
          data: {
            ...(parsedBirthDate !== undefined ? { birthDate: parsedBirthDate } : {}),
            ...(parsedGender !== undefined ? { gender: parsedGender } : {})
          }
        });
      }

      if (address !== undefined) {
        const street = address.street.trim();
        if (member.addressId) {
          await tx.address.update({
            where: { id: member.addressId },
            data: {
              villageId: address.villageId,
              postalCodeId: address.postalCodeId,
              street
            }
          });
        } else {
          const created = await tx.address.create({
            data: {
              villageId: address.villageId,
              postalCodeId: address.postalCodeId,
              street
            }
          });
          await tx.member.update({
            where: { id: member.id },
            data: { addressId: created.id }
          });
        }
      }
    });

    const updated = await this.getProfileByUserId(userId);
    if (!updated) {
      throw new Error('Gagal mengambil profil setelah update');
    }
    return updated;
  }

  async updateAvatarByUserId(userId: string, file: File): Promise<MemberProfileData> {
    const member = await prisma.member.findUnique({
      where: { userId },
      select: {
        id: true,
        user: { select: { profilePhotoId: true } }
      }
    });
    if (!member) {
      throw new Error('Member tidak ditemukan');
    }

    const previousPhotoId = member.user?.profilePhotoId ?? null;
    let uploadedMediaId: string | null = null;

    try {
      const media = await mediaService.upload({
        file,
        folder: 'member/photo',
        image: { maxSize: 512, quality: 80 }
      });
      uploadedMediaId = media.id;

      await authService.updateUserProfile(userId, { profilePhotoId: media.id });

      if (previousPhotoId && previousPhotoId !== media.id) {
        try {
          await mediaService.delete(previousPhotoId);
        } catch {
          // ponytail: orphan media cleanup best-effort; cron/S3 GC later
        }
      }
    } catch (error) {
      if (uploadedMediaId) {
        try {
          await mediaService.delete(uploadedMediaId);
        } catch {
          // ignore rollback failure
        }
      }
      throw error;
    }

    const updated = await this.getProfileByUserId(userId);
    if (!updated) {
      throw new Error('Gagal mengambil profil setelah upload avatar');
    }
    return updated;
  }
}

export const memberService = new MemberService();
