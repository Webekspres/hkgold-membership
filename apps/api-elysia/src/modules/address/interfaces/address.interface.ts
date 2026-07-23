import {
  CreateAddressRequest,
  UpdateAddressRequest,
  AddressDetailData,
  AddressOptionData,
  AddressCascadeLevel,
  AddressCascadeOptionData
} from '../types/address.types';

// Kontrak service publik Address. Modul lain (mis. Member) memanggil fungsi ini
// secara legal alih-alih mengakses Prisma Client tabel addresses secara langsung.
export interface IAddressService {
  getById(id: string): Promise<AddressDetailData | null>;
  create(data: CreateAddressRequest): Promise<AddressDetailData>;
  update(id: string, data: UpdateAddressRequest): Promise<AddressDetailData>;
  searchOptions(query: string, limit?: number): Promise<AddressOptionData[]>;
  listCascadeOptions(
    level: AddressCascadeLevel,
    parentId?: number
  ): Promise<AddressCascadeOptionData[]>;
  assertRegionPair(villageId: number, postalCodeId: number): Promise<void>;
}
