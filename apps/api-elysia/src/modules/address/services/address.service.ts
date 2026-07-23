import { prisma } from '../../../db';
import { IAddressService } from '../interfaces/address.interface';
import {
  CreateAddressRequest,
  UpdateAddressRequest,
  AddressDetailData,
  AddressOptionData,
  AddressCascadeLevel,
  AddressCascadeOptionData
} from '../types/address.types';

// Query village + rantai wilayah (subDistrict → city → province) sekali jalan
const addressInclude = {
  village: {
    include: {
      subDistrict: {
        include: {
          city: {
            include: {
              province: true
            }
          }
        }
      }
    }
  },
  postalCode: true
} as const;

type AddressWithRelations = {
  id: string;
  street: string;
  postalCodeId: number;
  createdAt: Date;
  updatedAt: Date;
  postalCode: { kodepos: string };
  village: {
    id: number;
    nama: string;
    subDistrict: {
      id: number;
      nama: string;
      city: {
        id: number;
        nama: string;
        province: { id: number; nama: string };
      };
    };
  };
};

const toDetail = (addr: AddressWithRelations): AddressDetailData => ({
  id: addr.id,
  street: addr.street,
  postalCodeId: addr.postalCodeId,
  kodepos: addr.postalCode.kodepos,
  region: {
    provinceId: addr.village.subDistrict.city.province.id,
    cityId: addr.village.subDistrict.city.id,
    subDistrictId: addr.village.subDistrict.id,
    villageId: addr.village.id,
    villageName: addr.village.nama,
    subDistrictName: addr.village.subDistrict.nama,
    cityName: addr.village.subDistrict.city.nama,
    provinceName: addr.village.subDistrict.city.province.nama
  },
  createdAt: addr.createdAt,
  updatedAt: addr.updatedAt
});

export class AddressService implements IAddressService {
  async getById(id: string): Promise<AddressDetailData | null> {
    if (!id) return null;

    const addr = await prisma.address.findUnique({
      where: { id },
      include: addressInclude
    });

    if (!addr) return null;

    return toDetail(addr as unknown as AddressWithRelations);
  }

  async assertRegionPair(villageId: number, postalCodeId: number): Promise<void> {
    const village = await prisma.village.findUnique({ where: { id: villageId } });
    if (!village) {
      throw new Error('Village tidak ditemukan');
    }

    const postalCode = await prisma.postalCode.findUnique({ where: { id: postalCodeId } });
    if (!postalCode) {
      throw new Error('Kode pos tidak ditemukan');
    }

    if (village.subDistrictId !== postalCode.subDistrictId) {
      throw new Error('Village dan kode pos tidak berada di wilayah yang sama');
    }
  }

  async create(data: CreateAddressRequest): Promise<AddressDetailData> {
    const { villageId, postalCodeId, street } = data;

    if (!villageId || !postalCodeId) {
      throw new Error('villageId dan postalCodeId wajib diisi');
    }
    if (!street || street.trim().length === 0) {
      throw new Error('Alamat jalan (street) wajib diisi');
    }

    await this.assertRegionPair(villageId, postalCodeId);

    const created = await prisma.address.create({
      data: {
        villageId,
        postalCodeId,
        street: street.trim()
      },
      include: addressInclude
    });

    return toDetail(created as unknown as AddressWithRelations);
  }

  async update(id: string, data: UpdateAddressRequest): Promise<AddressDetailData> {
    if (!id) {
      throw new Error('ID address wajib diisi');
    }

    const existing = await prisma.address.findUnique({ where: { id } });
    if (!existing) {
      throw new Error('Address tidak ditemukan');
    }

    const { villageId, postalCodeId, street } = data;

    if (villageId === undefined && postalCodeId === undefined && street === undefined) {
      throw new Error('Tidak ada data yang diubah');
    }

    const nextVillageId = villageId ?? existing.villageId;
    const nextPostalCodeId = postalCodeId ?? existing.postalCodeId;

    if (villageId !== undefined || postalCodeId !== undefined) {
      await this.assertRegionPair(nextVillageId, nextPostalCodeId);
    }

    if (street !== undefined && street.trim().length === 0) {
      throw new Error('Alamat jalan (street) tidak boleh kosong');
    }

    const updated = await prisma.address.update({
      where: { id },
      data: {
        ...(villageId !== undefined ? { villageId } : {}),
        ...(postalCodeId !== undefined ? { postalCodeId } : {}),
        ...(street !== undefined ? { street: street.trim() } : {})
      },
      include: addressInclude
    });

    return toDetail(updated as unknown as AddressWithRelations);
  }

  async searchOptions(query: string, limit = 20): Promise<AddressOptionData[]> {
    const q = query.trim();
    if (q.length < 2) return [];

    const take = Math.min(Math.max(limit, 1), 50);

    const villages = await prisma.village.findMany({
      where: {
        OR: [
          { nama: { contains: q } },
          {
            subDistrict: {
              OR: [
                { nama: { contains: q } },
                { city: { nama: { contains: q } } },
                { postalCodes: { some: { kodepos: { contains: q } } } }
              ]
            }
          }
        ]
      },
      take,
      include: {
        subDistrict: {
          include: {
            city: { include: { province: true } },
            postalCodes: { take: 5, orderBy: { kodepos: 'asc' } }
          }
        }
      },
      orderBy: { nama: 'asc' }
    });

    const options: AddressOptionData[] = [];

    for (const village of villages) {
      for (const postal of village.subDistrict.postalCodes) {
        options.push({
          villageId: village.id,
          postalCodeId: postal.id,
          kodepos: postal.kodepos,
          villageName: village.nama,
          subDistrictName: village.subDistrict.nama,
          cityName: village.subDistrict.city.nama,
          provinceName: village.subDistrict.city.province.nama
        });
        if (options.length >= take) return options;
      }
    }

    return options;
  }

  async listCascadeOptions(
    level: AddressCascadeLevel,
    parentId?: number
  ): Promise<AddressCascadeOptionData[]> {
    if (level !== 'province' && (!parentId || parentId < 1)) {
      throw new Error('parentId wajib diisi');
    }

    switch (level) {
      case 'province':
        return prisma.province.findMany({
          select: { id: true, nama: true },
          orderBy: { nama: 'asc' }
        }).then((rows) => rows.map((row) => ({ id: row.id, label: row.nama })));
      case 'city':
        return prisma.city.findMany({
          where: { provinceId: parentId },
          select: { id: true, nama: true },
          orderBy: { nama: 'asc' }
        }).then((rows) => rows.map((row) => ({ id: row.id, label: row.nama })));
      case 'subDistrict':
        return prisma.subDistrict.findMany({
          where: { cityId: parentId },
          select: { id: true, nama: true },
          orderBy: { nama: 'asc' }
        }).then((rows) => rows.map((row) => ({ id: row.id, label: row.nama })));
      case 'village':
        return prisma.village.findMany({
          where: { subDistrictId: parentId },
          select: { id: true, nama: true },
          orderBy: { nama: 'asc' }
        }).then((rows) => rows.map((row) => ({ id: row.id, label: row.nama })));
      case 'postalCode':
        return prisma.postalCode.findMany({
          where: { subDistrictId: parentId },
          select: { id: true, kodepos: true },
          orderBy: { kodepos: 'asc' }
        }).then((rows) => rows.map((row) => ({ id: row.id, label: row.kodepos })));
    }
  }
}

export const addressService = new AddressService();
