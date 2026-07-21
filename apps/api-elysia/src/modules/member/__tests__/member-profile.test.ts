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

  let nationId: number;
  let provinceId: number;
  let cityId: number;
  let subDistrictId: number;
  let villageId: number;
  let postalCodeId: number;
  let otherSubDistrictId: number;
  let otherVillageId: number;
  let otherPostalCodeId: number;

  const createdAddressIds: string[] = [];

  beforeAll(async () => {
    const reg = await authService.register(testUser);
    userId = reg.user.id;
    memberId = reg.member.id;

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

    const otherSub = await prisma.subDistrict.create({ data: { cityId, nama: `MSub2 ${suffix}` } });
    otherSubDistrictId = otherSub.id;
    const otherVillage = await prisma.village.create({
      data: { subDistrictId: otherSubDistrictId, nama: `MVillage2 ${suffix}` }
    });
    otherVillageId = otherVillage.id;
    const otherPostal = await prisma.postalCode.create({
      data: { cityId, subDistrictId: otherSubDistrictId, kodepos: `7${suffix}` }
    });
    otherPostalCodeId = otherPostal.id;
  });

  afterAll(async () => {
    try {
      await prisma.user.updateMany({ where: { id: userId }, data: { profilePhotoId: null } });
      await prisma.member.updateMany({ where: { id: memberId }, data: { addressId: null } });

      for (const id of createdAddressIds) {
        await prisma.address.deleteMany({ where: { id } });
      }
      await prisma.member.deleteMany({ where: { id: memberId } });
      await prisma.user.deleteMany({ where: { id: userId } });
      await prisma.postalCode.deleteMany({ where: { id: { in: [postalCodeId, otherPostalCodeId] } } });
      await prisma.village.deleteMany({ where: { id: { in: [villageId, otherVillageId] } } });
      await prisma.subDistrict.deleteMany({
        where: { id: { in: [subDistrictId, otherSubDistrictId] } }
      });
      await prisma.city.deleteMany({ where: { id: cityId } });
      await prisma.province.deleteMany({ where: { id: provinceId } });
      await prisma.nation.deleteMany({ where: { id: nationId } });
    } catch {
      // Ignore cleanup errors
    }
  });

  describe('getProfileByUserId()', () => {
    test('Happy path: mengembalikan member + user + gender null', async () => {
      const result = await memberService.getProfileByUserId(userId);

      expect(result).toBeDefined();
      expect(result?.id).toBe(memberId);
      expect(result?.memberNumber).toBeDefined();
      expect(result?.gender).toBeNull();
      expect(result?.user.fullName).toBe(testUser.fullName);
      expect(result?.address).toBeNull();
    });

    test('Edge: userId tidak ada mengembalikan null', async () => {
      const result = await memberService.getProfileByUserId('non-existent-user-id');
      expect(result).toBeNull();
    });
  });

  describe('updateProfileByUserId() - user/member fields', () => {
    test('Happy path: update fullName + gender + birthDate', async () => {
      const result = await memberService.updateProfileByUserId(userId, {
        fullName: 'Nama Baru Member',
        gender: 'MALE',
        birthDate: '1995-08-15'
      });
      expect(result.user.fullName).toBe('Nama Baru Member');
      expect(result.gender).toBe('MALE');
      expect(result.birthDate).toBeDefined();
    });

    test('Happy path: clear gender dan birthDate', async () => {
      const result = await memberService.updateProfileByUserId(userId, {
        gender: null,
        birthDate: null
      });
      expect(result.gender).toBeNull();
      expect(result.birthDate).toBeNull();
    });

    test('Edge: fullName kosong melempar error', async () => {
      await expect(
        memberService.updateProfileByUserId(userId, { fullName: '   ' })
      ).rejects.toThrow('Nama lengkap tidak boleh kosong');
    });

    test('Edge: birthDate masa depan melempar error', async () => {
      await expect(
        memberService.updateProfileByUserId(userId, { birthDate: '2099-01-01' })
      ).rejects.toThrow('Tanggal lahir tidak boleh di masa depan');
    });

    test('Edge: birthDate format salah melempar error', async () => {
      await expect(
        memberService.updateProfileByUserId(userId, { birthDate: '15-08-1995' })
      ).rejects.toThrow('Format birthDate tidak valid');
    });

    test('Edge: tidak ada data yang dikirim melempar error', async () => {
      await expect(memberService.updateProfileByUserId(userId, {})).rejects.toThrow(
        'Tidak ada data yang diubah'
      );
    });

    test('Edge: email/HP tidak bisa diubah lewat payload (diabaikan oleh tipe)', async () => {
      const before = await memberService.getProfileByUserId(userId);
      const result = await memberService.updateProfileByUserId(userId, {
        fullName: before!.user.fullName
      });
      expect(result.user.email).toBe(testUser.email);
      expect(result.phoneNumber).toBe(before!.phoneNumber);
    });
  });

  describe('updateProfileByUserId() - address upsert', () => {
    test('Happy path: create address saat member belum punya', async () => {
      const result = await memberService.updateProfileByUserId(userId, {
        address: {
          villageId,
          postalCodeId,
          street: 'Jl. Baru Member'
        }
      });
      expect(result.address?.street).toBe('Jl. Baru Member');
      expect(result.address?.region.villageId).toBe(villageId);
      if (result.address?.id) createdAddressIds.push(result.address.id);
    });

    test('Happy path: update address yang sudah ada', async () => {
      const result = await memberService.updateProfileByUserId(userId, {
        address: {
          villageId,
          postalCodeId,
          street: 'Jl. Update Member'
        }
      });
      expect(result.address?.street).toBe('Jl. Update Member');
    });

    test('Edge: village/postal mismatch melempar error', async () => {
      await expect(
        memberService.updateProfileByUserId(userId, {
          address: {
            villageId,
            postalCodeId: otherPostalCodeId,
            street: 'Jl. Salah Wilayah'
          }
        })
      ).rejects.toThrow('Village dan kode pos tidak berada di wilayah yang sama');
    });

    test('Happy path: update fullName + address sekaligus', async () => {
      const result = await memberService.updateProfileByUserId(userId, {
        fullName: 'Combo Update',
        address: {
          villageId: otherVillageId,
          postalCodeId: otherPostalCodeId,
          street: 'Jl. Combo'
        }
      });
      expect(result.user.fullName).toBe('Combo Update');
      expect(result.address?.street).toBe('Jl. Combo');
      expect(result.address?.region.villageId).toBe(otherVillageId);
    });
  });

  describe('address searchOptions()', () => {
    test('Happy path: mencari desa berdasarkan nama', async () => {
      const options = await addressService.searchOptions(`MVillage ${suffix}`, 10);
      expect(options.length).toBeGreaterThan(0);
      expect(options[0]?.villageName).toContain('MVillage');
      expect(options[0]?.postalCodeId).toBeDefined();
    });

    test('Happy path: opsi bertingkat mengikuti parent seperti backoffice', async () => {
      const provinces = await addressService.listCascadeOptions('province');
      const cities = await addressService.listCascadeOptions('city', provinceId);
      const subDistricts = await addressService.listCascadeOptions('subDistrict', cityId);
      const villages = await addressService.listCascadeOptions('village', subDistrictId);
      const postalCodes = await addressService.listCascadeOptions('postalCode', subDistrictId);

      expect(provinces.some((option) => option.id === provinceId)).toBe(true);
      expect(cities.some((option) => option.id === cityId)).toBe(true);
      expect(subDistricts.some((option) => option.id === subDistrictId)).toBe(true);
      expect(villages.some((option) => option.id === villageId)).toBe(true);
      expect(postalCodes.some((option) => option.id === postalCodeId)).toBe(true);
    });

    test('Edge: child level mewajibkan parentId', async () => {
      await expect(addressService.listCascadeOptions('city')).rejects.toThrow(
        'parentId wajib diisi'
      );
    });

    test('Edge: query pendek mengembalikan kosong', async () => {
      const options = await addressService.searchOptions('a');
      expect(options).toEqual([]);
    });
  });
});
