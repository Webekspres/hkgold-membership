import type { MemberTier } from '@/types/auth';

export type MemberGender = 'MALE' | 'FEMALE';

export type MemberProfilePhoto = {
  id: string;
  fileUrl: string;
};

export type MemberProfileUser = {
  id: string;
  email: string;
  fullName: string;
  role: string;
  isActive: boolean;
  profilePhoto: MemberProfilePhoto | null;
};

export type MemberAddressRegion = {
  provinceId: number;
  cityId: number;
  subDistrictId: number;
  villageId: number;
  villageName: string;
  subDistrictName: string;
  cityName: string;
  provinceName: string;
};

export type MemberAddress = {
  id: string;
  street: string;
  postalCodeId: number;
  kodepos: string;
  region: MemberAddressRegion;
  createdAt: string;
  updatedAt: string;
};

export type AddressOption = {
  villageId: number;
  postalCodeId: number;
  kodepos: string;
  villageName: string;
  subDistrictName: string;
  cityName: string;
  provinceName: string;
};

export type AddressCascadeLevel =
  | 'province'
  | 'city'
  | 'subDistrict'
  | 'village'
  | 'postalCode';

export type AddressCascadeOption = {
  id: number;
  label: string;
};

/** Full `GET /api/member/me` payload. */
export type MemberProfile = {
  id: string;
  memberNumber: string;
  phoneNumber: string;
  birthDate: string | null;
  gender: MemberGender | null;
  currentTier: MemberTier | string;
  pointBalance: number;
  highestPoint: number;
  isSuspended: boolean;
  user: MemberProfileUser;
  address: MemberAddress | null;
  createdAt?: string;
  updatedAt?: string;
};

export type UpdateMyProfileInput = {
  fullName?: string;
  birthDate?: string | null;
  gender?: MemberGender | null;
  address?: {
    villageId: number;
    postalCodeId: number;
    street: string;
  };
};
