import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { memberService } from '../services/member.service';
import { authService } from '../../auth/services/auth.service';
import { addressService } from '../../address/services/address.service';
import { prisma } from '../../../db';

describe('Member Module - Profile (GET/PATCH me)', () => {
  const suffix = Date.now().toString().slice(-6);

  const testUser = {
    email: `member-profile-${suffix}@example.com`,
    password: 'password123',
    fullName: 'Member Profile Test',
    phoneNumber: `0812${suffix}55`
  };

  let userId: string;
  let memberId: string;

  // Lokasi untuk address
  let nationId: number;
  let provinceId: number;
  let cityId: number;
  let subDistrictId: number;
  let villageId: number;
  let postalCodeId: number;

  // Media untuk foto profil
  let mediaId: string;

  const createdAddressIds: string[] = [];

  beforeAll(async () => {
    // User + member via authService (real flow)
    const reg = await authService.register(testUser);
    userId = reg.user.id;
    memberId = reg.member.id;

    // Rantai lokasi
    const nation = await prisma.nation.create({
      data: {
        nationCode: 62,
        iso2: `M${suffix.slice(0, 1)}`,
        iso3: `M${suffix.slice(0, 2)}`,
        nama: `MemberNation ${suffix}`,
        mataUang: 'Rupiah',
        kodeMataUang: 'IDR',
        simbolMataUang: 'Rp',
        satuanBerat: 'gram',
        satuanPanjang: 'cm'
      }
    });
    nationId = nation.id;
    const province = await prisma.province.create({ data: { nationId, nama: `MProv ${suffix}` } });
    provinceId = province.id;
    const city = await prisma.city.create({ data: { provinceId, nama: `MCity ${suffix}` } });
    cityId = city.id;
    const subDistrict = await prisma.subDistrict.create({ data: { cityId, nama: `MSub ${suffix}` } });
    subDistrictId = subDistrict.id;
    const village = await prisma.village.create({ data: { subDistrictId, nama: `MVillage ${suffix}` } });
    villageId = village.id;
    const postalCode = await prisma.postalCode.create({ data: { cityId, subDistrictId, kodepos: `6${suffix}` } });
    postalCodeId = postalCode.id;

    // Media
    const media = await prisma.media.create({
      data: {
        fileName: `photo-${suffix}.jpg`,
        fileType: 'image/jpeg',
        fileUrl: `https://cdn.test/photo-${suffix}.jpg`,
        fileSize: 1024
      }
    });
    mediaId = media.id;
  });

  afterAll(async () => {
    try {
      // Lepaskan foto profil sebelum hapus media
      await prisma.user.updateMany({ where: { id: userId }, data: { profilePhotoId: null } });
      // Lepaskan address dari member sebelum hapus address
      await prisma.member.updateMany({ where: { id: memberId }, data: { addressId: null } });

      for (const id of createdAddressIds) {
        await prisma.address.deleteMany({ where: { id } });
      }
      await prisma.member.deleteMany({ where: { id: memberId } });
      await prisma.user.deleteMany({ where: { id: userId } });
      await prisma.media.deleteMany({ where: { id: mediaId } });
      await prisma.postalCode.deleteMany({ where: { id: postalCodeId } });
      await prisma.village.deleteMany({ where: { id: villageId } });
      await prisma.subDistrict.deleteMany({ where: { id: subDistrictId } });
      await prisma.city.deleteMany({ where: { id: cityId } });
      await prisma.province.deleteMany({ where: { id: provinceId } });
      await prisma.nation.deleteMany({ where: { id: nationId } });
    } catch (error) {
      // Ignore cleanup errors
    }
  });

  // ---------------------------------------------------------------------------
  // GET PROFILE
  // ---------------------------------------------------------------------------
  describe('getProfileByUserId()', () => {
    test('Happy path: mengembalikan member + user, address null saat belum diisi', async () => {
      const result = await memberService.getProfileByUserId(userId);

      expect(result).toBeDefined();
      expect(result?.id).toBe(memberId);
      expect(result?.memberNumber).toBeDefined();
      expect(result?.currentTier).toBe('SILVER');
      expect(result?.pointBalance).toBe(0);
      expect(result?.isSuspended).toBe(false);
      // Relasi user
      expect(result?.user.id).toBe(userId);
      expect(result?.user.email).toBe(testUser.email);
      expect(result?.user.fullName).toBe(testUser.fullName);
      expect(result?.user.role).toBe('MEMBER');
      expect(result?.user.profilePhoto).toBeNull();
      // Address belum ada
      expect(result?.address).toBeNull();
    });

    test('Edge: userId tidak ada mengembalikan null', async () => {
      const result = await memberService.getProfileByUserId('non-existent-user-id');
      expect(result).toBeNull();
    });

    test('Edge: empty userId mengembalikan null', async () => {
      const result = await memberService.getProfileByUserId('');
      expect(result).toBeNull();
    });
  });

  // ---------------------------------------------------------------------------
  // UPDATE PROFILE - fullName & foto profil (cross-module ke authService)
  // ---------------------------------------------------------------------------
  describe('updateProfileByUserId() - user fields', () => {
    test('Happy path: update fullName', async () => {
      const result = await memberService.updateProfileByUserId(userId, {
        fullName: 'Nama Baru Member'
      });
      expect(result.user.fullName).toBe('Nama Baru Member');
    });

    test('Happy path: set foto profil (profilePhotoId)', async () => {
      const result = await memberService.updateProfileByUserId(userId, {
        profilePhotoId: mediaId
      });
      expect(result.user.profilePhoto).toBeDefined();
      expect(result.user.profilePhoto?.id).toBe(mediaId);
      expect(result.user.profilePhoto?.fileUrl).toBe(`https://cdn.test/photo-${suffix}.jpg`);
    });

    test('Happy path: hapus foto profil (profilePhotoId null)', async () => {
      const result = await memberService.updateProfileByUserId(userId, {
        profilePhotoId: null
      });
      expect(result.user.profilePhoto).toBeNull();
    });

    test('Edge: fullName kosong melempar error', async () => {
      await expect(
        memberService.updateProfileByUserId(userId, { fullName: '   ' })
      ).rejects.toThrow('Nama lengkap tidak boleh kosong');
    });

    test('Edge: profilePhotoId tidak valid melempar error', async () => {
      await expect(
        memberService.updateProfileByUserId(userId, { profilePhotoId: 'non-existent-media' })
      ).rejects.toThrow('Media foto profil tidak ditemukan');
    });

    test('Edge: tidak ada data yang dikirim melempar error', async () => {
      await expect(
        memberService.updateProfileByUserId(userId, {})
      ).rejects.toThrow('Tidak ada data yang diubah');
    });

    test('Edge: userId tidak ada melempar error', async () => {
      await expect(
        memberService.updateProfileByUserId('non-existent-user-id', { fullName: 'X' })
      ).rejects.toThrow('Member tidak ditemukan');
    });
  });

  // ---------------------------------------------------------------------------
  // UPDATE PROFILE - address (cross-module ke addressService)
  // ---------------------------------------------------------------------------
  describe('updateProfileByUserId() - address', () => {
    test('Edge: update address saat member belum punya address melempar error', async () => {
      await expect(
        memberService.updateProfileByUserId(userId, { address: { street: 'Jl. Baru' } })
      ).rejects.toThrow('Member belum memiliki alamat untuk diperbarui');
    });

    test('Happy path: update address yang sudah ada', async () => {
      // Buat address lalu link ke member (simulasi member sudah punya alamat)
      const addr = await addressService.create({
        villageId,
        postalCodeId,
        street: 'Jl. Awal Member'
      });
      createdAddressIds.push(addr.id);
      await prisma.member.update({ where: { id: memberId }, data: { addressId: addr.id } });

      // GET harus sekarang menampilkan address
      const before = await memberService.getProfileByUserId(userId);
      expect(before?.address).toBeDefined();
      expect(before?.address?.street).toBe('Jl. Awal Member');

      // Update street via member service
      const result = await memberService.updateProfileByUserId(userId, {
        address: { street: 'Jl. Update Member' }
      });
      expect(result.address?.street).toBe('Jl. Update Member');
      expect(result.address?.region.villageId).toBe(villageId);
    });

    test('Happy path: update fullName + address sekaligus', async () => {
      const result = await memberService.updateProfileByUserId(userId, {
        fullName: 'Combo Update',
        address: { street: 'Jl. Combo' }
      });
      expect(result.user.fullName).toBe('Combo Update');
      expect(result.address?.street).toBe('Jl. Combo');
    });
  });
});
