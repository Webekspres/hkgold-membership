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

export type AddressDetailResponse = ApiResponse<AddressDetailData>;
