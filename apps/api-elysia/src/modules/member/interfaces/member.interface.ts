import { MemberProfileData, UpdateMemberProfileRequest } from '../types/member.types';

export interface IMemberService {
  // GET /me — ambil profil member sendiri berdasarkan userId dari token
  getProfileByUserId(userId: string): Promise<MemberProfileData | null>;
  // PATCH /me — edit fullName, foto profil, dan/atau alamat
  updateProfileByUserId(
    userId: string,
    data: UpdateMemberProfileRequest
  ): Promise<MemberProfileData>;
}
