import { MemberProfileData, UpdateMemberProfileRequest } from '../types/member.types';

export interface IMemberService {
  getProfileByUserId(userId: string): Promise<MemberProfileData | null>;
  updateProfileByUserId(
    userId: string,
    data: UpdateMemberProfileRequest
  ): Promise<MemberProfileData>;
  updateAvatarByUserId(userId: string, file: File): Promise<MemberProfileData>;
}
