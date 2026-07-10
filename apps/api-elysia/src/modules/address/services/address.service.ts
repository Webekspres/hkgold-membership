import { prisma } from '../../../db';
import { IAddressService } from '../interfaces/address.interface';
import {
  CreateAddressRequest,
  UpdateAddressRequest,
  AddressDetailData
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
      nama: string;
      city: {
        nama: string;
        province: { nama: string };
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

  async create(data: CreateAddressRequest): Promise<AddressDetailData> {
    const { villageId, postalCodeId, street } = data;

    // Validasi di trust boundary
    if (!villageId || !postalCodeId) {
      throw new Error('villageId dan postalCodeId wajib diisi');
    }
    if (!street || street.trim().length === 0) {
      throw new Error('Alamat jalan (street) wajib diisi');
    }

    // Pastikan FK valid agar tidak melempar error DB mentah ke client
    const village = await prisma.village.findUnique({ where: { id: villageId } });
    if (!village) {
      throw new Error('Village tidak ditemukan');
    }
    const postalCode = await prisma.postalCode.findUnique({ where: { id: postalCodeId } });
    if (!postalCode) {
      throw new Error('Kode pos tidak ditemukan');
    }

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

    // Tidak ada field yang dikirim
    if (villageId === undefined && postalCodeId === undefined && street === undefined) {
      throw new Error('Tidak ada data yang diubah');
    }

    // Validasi FK hanya jika field-nya dikirim
    if (villageId !== undefined) {
      const village = await prisma.village.findUnique({ where: { id: villageId } });
      if (!village) {
        throw new Error('Village tidak ditemukan');
      }
    }
    if (postalCodeId !== undefined) {
      const postalCode = await prisma.postalCode.findUnique({ where: { id: postalCodeId } });
      if (!postalCode) {
        throw new Error('Kode pos tidak ditemukan');
      }
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
}

export const addressService = new AddressService();
