import {
  RegisterRequest,
  LoginRequest,
  ChangePasswordRequest,
  UpdateUserProfileRequest,
  AuthResponse,
  UserData
} from '../types/auth.types';

export interface IAuthService {
  register(data: RegisterRequest): Promise<AuthResponse>;
  login(data: LoginRequest): Promise<AuthResponse>;
  changePassword(userId: string, data: ChangePasswordRequest): Promise<{ message: string }>;
  validateUser(identifier: string, password: string): Promise<AuthResponse>;
  // Fungsi service publik yang dipanggil modul Member untuk memutasi data profil
  // pada tabel User (fullName, profilePhotoId) secara legal lintas modul.
  updateUserProfile(userId: string, data: UpdateUserProfileRequest): Promise<UserData>;
}
