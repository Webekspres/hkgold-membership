import { ApiResponse } from '../../../shared/types/response.types';

// Request payloads dari client React Native
export interface CreateAddressRequest {
  villageId: number;
  postalCodeId: number;
  street: string;
}

// Semua field opsional saat update (partial edit)
export interface UpdateAddressRequest {
  villageId?: number;
  postalCodeId?: number;
  street?: string;
}

// Objek wilayah ternormalisasi untuk payload detail
export interface AddressRegionData {
  provinceId: number;
  cityId: number;
  subDistrictId: number;
  villageId: number;
  villageName: string;
  subDistrictName: string;
  cityName: string;
  provinceName: string;
}

// Final response object menuju client
export interface AddressDetailData {
  id: string;
  street: string;
  postalCodeId: number;
  kodepos: string;
  region: AddressRegionData;
  createdAt: Date;
  updatedAt: Date;
}

/** Hasil pencarian wilayah + kode pos untuk editor alamat. */
export interface AddressOptionData {
  villageId: number;
  postalCodeId: number;
  kodepos: string;
  villageName: string;
  subDistrictName: string;
  cityName: string;
  provinceName: string;
}

export type AddressCascadeLevel =
  | 'province'
  | 'city'
  | 'subDistrict'
  | 'village'
  | 'postalCode';

export interface AddressCascadeOptionData {
  id: number;
  label: string;
}

export type AddressDetailResponse = ApiResponse<AddressDetailData>;
export type AddressOptionsResponse = ApiResponse<AddressOptionData[]>;
export type AddressCascadeOptionsResponse = ApiResponse<AddressCascadeOptionData[]>;
