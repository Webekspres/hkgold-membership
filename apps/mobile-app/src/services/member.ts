import { persistProfileSnapshot } from '@/services/auth';
import { apiClient } from '@/lib/api-client';
import type { ApiEnvelope, AuthMember, AuthUser, MemberTier } from '@/types/auth';
import type { MemberProfile } from '@/types/member';

export async function getMyProfile(): Promise<MemberProfile> {
  const { data } = await apiClient.get<ApiEnvelope<MemberProfile>>('/api/member/me');

  if (!data.success || !data.data) {
    throw new Error(data.message || 'Gagal mengambil profil');
  }

  const profile = data.data;
  await persistProfileSnapshot(toAuthUser(profile), toAuthMember(profile));
  return profile;
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
    upper === 'SAPPHIRE'
  ) {
    return upper;
  }
  return 'SILVER';
}

export type MemberCardView = {
  fullName: string;
  memberNumber: string;
  currentTier: MemberTier;
  pointBalance: number;
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
    avatarUri: profile.user.profilePhoto?.fileUrl,
    avatarFallback: getNameInitials(fullName),
    firstName: fullName.trim().split(/\s+/)[0] ?? fullName,
    tierLabel: formatTierLabel(String(profile.currentTier)),
  };
}

export function mapSessionToCardView(
  user: AuthUser | null,
  member: AuthMember | null
): MemberCardView | null {
  if (!user || !member) return null;
  return {
    fullName: user.fullName,
    memberNumber: member.memberNumber,
    currentTier: asMemberTier(String(member.currentTier)),
    pointBalance: member.pointBalance,
    avatarUri: undefined,
    avatarFallback: getNameInitials(user.fullName),
    firstName: user.fullName.trim().split(/\s+/)[0] ?? user.fullName,
    tierLabel: formatTierLabel(String(member.currentTier)),
  };
}
