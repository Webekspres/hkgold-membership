import { ApiResponse } from '../../../shared/types/response.types';
import { AddressDetailData, CreateAddressRequest } from '../../address/types/address.types';

export type MemberGender = 'MALE' | 'FEMALE';

// Bagian user (dari tabel users) di dalam payload member
export interface MemberUserData {
  id: string;
  email: string;
  fullName: string;
  role: string;
  isActive: boolean;
  profilePhoto: {
    id: string;
    fileUrl: string;
  } | null;
}

// Objek final agregasi member menuju client React Native
export interface MemberProfileData {
  id: string;
  memberNumber: string;
  phoneNumber: string;
  birthDate: Date | null;
  gender: MemberGender | null;
  currentTier: string;
  pointBalance: number;
  highestPoint: number;
  isSuspended: boolean;
  user: MemberUserData;
  address: AddressDetailData | null;
  createdAt: Date;
  updatedAt: Date;
}

/** Payload edit profil. Email/HP/memberNumber/profilePhotoId tidak diterima. */
export interface UpdateMemberProfileRequest {
  fullName?: string;
  birthDate?: string | null; // YYYY-MM-DD
  gender?: MemberGender | null;
  address?: CreateAddressRequest;
}

export type MemberProfileResponse = ApiResponse<MemberProfileData>;
