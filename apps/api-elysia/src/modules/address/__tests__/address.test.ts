import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { addressService } from '../services/address.service';
import { prisma } from '../../../db';

describe('Address Module - Service', () => {
  const suffix = Date.now().toString().slice(-6);

  // Rantai wilayah dibangun manual karena tabel lokasi kosong di lingkungan test
  let nationId: number;
  let provinceId: number;
  let cityId: number;
  let subDistrictId: number;
  let villageId: number;
  let village2Id: number;
  let postalCodeId: number;
  let postalCode2Id: number;

  const createdAddressIds: string[] = [];

  beforeAll(async () => {
    const nation = await prisma.nation.create({
      data: {
        nationCode: 62,
        iso2: `T${suffix.slice(0, 1)}`,
        iso3: `T${suffix.slice(0, 2)}`,
        nama: `Test Nation ${suffix}`,
        mataUang: 'Rupiah',
        kodeMataUang: 'IDR',
        simbolMataUang: 'Rp',
        satuanBerat: 'gram',
        satuanPanjang: 'cm'
      }
    });
    nationId = nation.id;

    const province = await prisma.province.create({
      data: { nationId, nama: `Test Province ${suffix}` }
    });
    provinceId = province.id;

    const city = await prisma.city.create({
      data: { provinceId, nama: `Test City ${suffix}` }
    });
    cityId = city.id;

    const subDistrict = await prisma.subDistrict.create({
      data: { cityId, nama: `Test SubDistrict ${suffix}` }
    });
    subDistrictId = subDistrict.id;

    const village = await prisma.village.create({
      data: { subDistrictId, nama: `Test Village ${suffix}` }
    });
    villageId = village.id;

    const village2 = await prisma.village.create({
      data: { subDistrictId, nama: `Test Village 2 ${suffix}` }
    });
    village2Id = village2.id;

    const postalCode = await prisma.postalCode.create({
      data: { cityId, subDistrictId, kodepos: `4${suffix}` }
    });
    postalCodeId = postalCode.id;

    const postalCode2 = await prisma.postalCode.create({
      data: { cityId, subDistrictId, kodepos: `5${suffix}` }
    });
    postalCode2Id = postalCode2.id;
  });

  afterAll(async () => {
    try {
      for (const id of createdAddressIds) {
        await prisma.address.deleteMany({ where: { id } });
      }
      await prisma.postalCode.deleteMany({ where: { id: { in: [postalCodeId, postalCode2Id] } } });
      await prisma.village.deleteMany({ where: { id: { in: [villageId, village2Id] } } });
      await prisma.subDistrict.deleteMany({ where: { id: subDistrictId } });
      await prisma.city.deleteMany({ where: { id: cityId } });
      await prisma.province.deleteMany({ where: { id: provinceId } });
      await prisma.nation.deleteMany({ where: { id: nationId } });
    } catch (error) {
      // Ignore cleanup errors
    }
  });

  // ---------------------------------------------------------------------------
  // CREATE
  // ---------------------------------------------------------------------------
  describe('create()', () => {
    test('Happy path: valid data returns address dengan region ternormalisasi', async () => {
      const result = await addressService.create({
        villageId,
        postalCodeId,
        street: 'Jl. Test No. 1'
      });
      createdAddressIds.push(result.id);

      expect(result.id).toBeDefined();
      expect(result.street).toBe('Jl. Test No. 1');
      expect(result.postalCodeId).toBe(postalCodeId);
      expect(result.kodepos).toBe(`4${suffix}`);
      expect(result.region.villageId).toBe(villageId);
      expect(result.region.villageName).toBe(`Test Village ${suffix}`);
      expect(result.region.subDistrictName).toBe(`Test SubDistrict ${suffix}`);
      expect(result.region.cityName).toBe(`Test City ${suffix}`);
      expect(result.region.provinceName).toBe(`Test Province ${suffix}`);
    });

    test('Street di-trim otomatis', async () => {
      const result = await addressService.create({
        villageId,
        postalCodeId,
        street: '   Jl. Trim No. 2   '
      });
      createdAddressIds.push(result.id);

      expect(result.street).toBe('Jl. Trim No. 2');
    });

    test('Edge: villageId hilang melempar error', async () => {
      await expect(
        addressService.create({ villageId: 0, postalCodeId, street: 'Jl. X' })
      ).rejects.toThrow('villageId dan postalCodeId wajib diisi');
    });

    test('Edge: postalCodeId hilang melempar error', async () => {
      await expect(
        addressService.create({ villageId, postalCodeId: 0, street: 'Jl. X' })
      ).rejects.toThrow('villageId dan postalCodeId wajib diisi');
    });

    test('Edge: street kosong melempar error', async () => {
      await expect(
        addressService.create({ villageId, postalCodeId, street: '   ' })
      ).rejects.toThrow('Alamat jalan (street) wajib diisi');
    });

    test('Edge: villageId tidak ada di DB melempar error', async () => {
      await expect(
        addressService.create({ villageId: 999999999, postalCodeId, street: 'Jl. X' })
      ).rejects.toThrow('Village tidak ditemukan');
    });

    test('Edge: postalCodeId tidak ada di DB melempar error', async () => {
      await expect(
        addressService.create({ villageId, postalCodeId: 999999999, street: 'Jl. X' })
      ).rejects.toThrow('Kode pos tidak ditemukan');
    });
  });

  // ---------------------------------------------------------------------------
  // GET BY ID
  // ---------------------------------------------------------------------------
  describe('getById()', () => {
    test('Happy path: existing ID mengembalikan detail', async () => {
      const created = await addressService.create({
        villageId,
        postalCodeId,
        street: 'Jl. Get No. 3'
      });
      createdAddressIds.push(created.id);

      const result = await addressService.getById(created.id);
      expect(result).toBeDefined();
      expect(result?.id).toBe(created.id);
      expect(result?.street).toBe('Jl. Get No. 3');
    });

    test('Edge: non-existent ID mengembalikan null', async () => {
      const result = await addressService.getById('00000000-0000-0000-0000-000000000000');
      expect(result).toBeNull();
    });

    test('Edge: empty ID mengembalikan null', async () => {
      const result = await addressService.getById('');
      expect(result).toBeNull();
    });
  });

  // ---------------------------------------------------------------------------
  // UPDATE
  // ---------------------------------------------------------------------------
  describe('update()', () => {
    test('Happy path: update street saja', async () => {
      const created = await addressService.create({
        villageId,
        postalCodeId,
        street: 'Jl. Awal'
      });
      createdAddressIds.push(created.id);

      const result = await addressService.update(created.id, { street: 'Jl. Baru' });
      expect(result.street).toBe('Jl. Baru');
      expect(result.region.villageId).toBe(villageId); // tidak berubah
    });

    test('Happy path: update village + postalCode', async () => {
      const created = await addressService.create({
        villageId,
        postalCodeId,
        street: 'Jl. Pindah'
      });
      createdAddressIds.push(created.id);

      const result = await addressService.update(created.id, {
        villageId: village2Id,
        postalCodeId: postalCode2Id
      });
      expect(result.region.villageId).toBe(village2Id);
      expect(result.region.villageName).toBe(`Test Village 2 ${suffix}`);
      expect(result.postalCodeId).toBe(postalCode2Id);
      expect(result.kodepos).toBe(`5${suffix}`);
      expect(result.street).toBe('Jl. Pindah'); // tidak berubah
    });

    test('Edge: ID tidak ada melempar error', async () => {
      await expect(
        addressService.update('00000000-0000-0000-0000-000000000000', { street: 'X' })
      ).rejects.toThrow('Address tidak ditemukan');
    });

    test('Edge: empty ID melempar error', async () => {
      await expect(
        addressService.update('', { street: 'X' })
      ).rejects.toThrow('ID address wajib diisi');
    });

    test('Edge: tidak ada field yang dikirim melempar error', async () => {
      const created = await addressService.create({
        villageId,
        postalCodeId,
        street: 'Jl. Kosong'
      });
      createdAddressIds.push(created.id);

      await expect(
        addressService.update(created.id, {})
      ).rejects.toThrow('Tidak ada data yang diubah');
    });

    test('Edge: villageId baru tidak valid melempar error', async () => {
      const created = await addressService.create({
        villageId,
        postalCodeId,
        street: 'Jl. FK'
      });
      createdAddressIds.push(created.id);

      await expect(
        addressService.update(created.id, { villageId: 999999999 })
      ).rejects.toThrow('Village tidak ditemukan');
    });

    test('Edge: postalCodeId baru tidak valid melempar error', async () => {
      const created = await addressService.create({
        villageId,
        postalCodeId,
        street: 'Jl. FK2'
      });
      createdAddressIds.push(created.id);

      await expect(
        addressService.update(created.id, { postalCodeId: 999999999 })
      ).rejects.toThrow('Kode pos tidak ditemukan');
    });

    test('Edge: street kosong saat update melempar error', async () => {
      const created = await addressService.create({
        villageId,
        postalCodeId,
        street: 'Jl. Valid'
      });
      createdAddressIds.push(created.id);

      await expect(
        addressService.update(created.id, { street: '   ' })
      ).rejects.toThrow('Alamat jalan (street) tidak boleh kosong');
    });
  });
});
