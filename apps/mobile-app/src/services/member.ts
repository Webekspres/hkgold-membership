import { persistProfileSnapshot } from '@/services/auth';
import { apiClient } from '@/lib/api-client';
import {
  buildUpdateMyProfilePayload,
  formatAddressLine,
  formatAddressOptionLabel,
  formatGenderLabel,
  parseDateOnly,
  toDateOnlyString,
} from '@/services/member-profile-utils';
import type { ApiEnvelope, AuthMember, AuthUser, MemberTier } from '@/types/auth';
import type {
  AddressCascadeLevel,
  AddressCascadeOption,
  AddressOption,
  MemberProfile,
  UpdateMyProfileInput,
} from '@/types/member';

export {
  buildUpdateMyProfilePayload,
  formatAddressLine,
  formatAddressOptionLabel,
  formatGenderLabel,
  parseDateOnly,
  toDateOnlyString,
};

export async function getMyProfile(): Promise<MemberProfile> {
  const { data } = await apiClient.get<ApiEnvelope<MemberProfile>>('/api/member/me');

  if (!data.success || !data.data) {
    throw new Error(data.message || 'Gagal mengambil profil');
  }

  const profile = data.data;
  await persistProfileSnapshot(toAuthUser(profile), toAuthMember(profile));
  return profile;
}

export async function updateMyProfile(
  input: UpdateMyProfileInput,
): Promise<MemberProfile> {
  const body = buildUpdateMyProfilePayload(input);
  const { data } = await apiClient.patch<ApiEnvelope<MemberProfile>>(
    '/api/member/me',
    body,
  );

  if (!data.success || !data.data) {
    throw new Error(data.message || 'Gagal memperbarui profil');
  }

  const profile = data.data;
  await persistProfileSnapshot(toAuthUser(profile), toAuthMember(profile));
  return profile;
}

export async function uploadMyAvatar(asset: {
  uri: string;
  mimeType?: string | null;
  fileName?: string | null;
}): Promise<MemberProfile> {
  const form = new FormData();
  const mimeType = asset.mimeType || 'image/jpeg';
  const fileName = asset.fileName || `avatar.${mimeType.split('/')[1] || 'jpg'}`;

  form.append('file', {
    uri: asset.uri,
    type: mimeType,
    name: fileName,
  } as unknown as Blob);

  const { data } = await apiClient.put<ApiEnvelope<MemberProfile>>(
    '/api/member/me/avatar',
    form,
    {
      headers: { 'Content-Type': 'multipart/form-data' },
      timeout: 60_000,
    },
  );

  if (!data.success || !data.data) {
    throw new Error(data.message || 'Gagal mengunggah foto profil');
  }

  const profile = data.data;
  await persistProfileSnapshot(toAuthUser(profile), toAuthMember(profile));
  return profile;
}

export async function searchAddressOptions(
  query: string,
  limit = 20,
): Promise<AddressOption[]> {
  const q = query.trim();
  if (q.length < 2) return [];

  const { data } = await apiClient.get<ApiEnvelope<AddressOption[]>>(
    '/api/address/options',
    { params: { q, limit } },
  );

  if (!data.success || !data.data) {
    throw new Error(data.message || 'Gagal mencari alamat');
  }

  return data.data;
}

export async function getAddressCascadeOptions(
  level: AddressCascadeLevel,
  parentId?: number,
): Promise<AddressCascadeOption[]> {
  const { data } = await apiClient.get<ApiEnvelope<AddressCascadeOption[]>>(
    '/api/address/cascade-options',
    { params: { level, parentId } },
  );

  if (!data.success || !data.data) {
    throw new Error(data.message || 'Gagal mengambil opsi wilayah');
  }

  return data.data;
}

export function toAuthUser(profile: MemberProfile): AuthUser {
  return {
    id: profile.user.id,
    email: profile.user.email,
    fullName: profile.user.fullName,
    role: profile.user.role,
    isActive: profile.user.isActive,
  };
}

export function toAuthMember(profile: MemberProfile): AuthMember {
  return {
    id: profile.id,
    memberNumber: profile.memberNumber,
    phoneNumber: profile.phoneNumber,
    currentTier: profile.currentTier,
    pointBalance: profile.pointBalance,
    isSuspended: profile.isSuspended,
  };
}

/** Inisial 1–2 huruf dari nama lengkap. */
export function getNameInitials(fullName: string): string {
  const parts = fullName.trim().split(/\s+/).filter(Boolean);
  if (parts.length === 0) return 'HK';
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
  return `${parts[0][0] ?? ''}${parts[1][0] ?? ''}`.toUpperCase();
}

export function formatTierLabel(tier: string): string {
  if (!tier) return '';
  return tier.charAt(0) + tier.slice(1).toLowerCase();
}

export function asMemberTier(tier: string): MemberTier {
  const upper = tier.toUpperCase();
  if (
    upper === 'SILVER' ||
    upper === 'GOLD' ||
    upper === 'PLATINUM' ||
    upper === 'ELITE' ||
    upper === 'SAPPHIRE'
  ) {
    return upper === 'SAPPHIRE' ? 'ELITE' : upper;
  }
  return 'SILVER';
}

export type MemberCardView = {
  fullName: string;
  memberNumber: string;
  currentTier: MemberTier;
  pointBalance: number;
  birthDate: string | null;
  avatarUri?: string;
  avatarFallback: string;
  firstName: string;
  tierLabel: string;
};

export function mapProfileToCardView(profile: MemberProfile): MemberCardView {
  const fullName = profile.user.fullName;
  return {
    fullName,
    memberNumber: profile.memberNumber,
    currentTier: asMemberTier(String(profile.currentTier)),
    pointBalance: profile.pointBalance,
    birthDate: profile.birthDate ?? null,
    avatarUri: profile.user.profilePhoto?.fileUrl,
    avatarFallback: getNameInitials(fullName),
    firstName: fullName.trim().split(/\s+/)[0] ?? fullName,
    tierLabel: formatTierLabel(String(profile.currentTier)),
  };
}

export function mapSessionToCardView(
  user: AuthUser | null,
  member: AuthMember | null,
): MemberCardView | null {
  if (!user || !member) return null;
  return {
    fullName: user.fullName,
    memberNumber: member.memberNumber,
    currentTier: asMemberTier(String(member.currentTier)),
    pointBalance: member.pointBalance,
    birthDate: null,
    avatarUri: undefined,
    avatarFallback: getNameInitials(user.fullName),
    firstName: user.fullName.trim().split(/\s+/)[0] ?? user.fullName,
    tierLabel: formatTierLabel(String(member.currentTier)),
  };
}
