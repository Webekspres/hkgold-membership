import { ApiResponse } from '../../../shared/types/response.types';
import { AddressDetailData, UpdateAddressRequest } from '../../address/types/address.types';

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
  currentTier: string;
  pointBalance: number;
  highestPoint: number;
  isSuspended: boolean;
  user: MemberUserData;
  address: AddressDetailData | null;
  createdAt: Date;
  updatedAt: Date;
}

// Payload edit profil dari member. Menggabungkan field milik User (fullName,
// profilePhotoId, email) dan field Member (phoneNumber, birthDate) dan field alamat.
// Semua opsional (partial update).
export interface UpdateMemberProfileRequest {
  fullName?: string;
  email?: string;
  phoneNumber?: string;
  birthDate?: string | null; // ISO 8601 string dari client
  profilePhotoId?: string | null;
  address?: UpdateAddressRequest;
}

export type MemberProfileResponse = ApiResponse<MemberProfileData>;
