import {
  RegisterRequest,
  LoginRequest,
  ChangePasswordRequest,
  AuthResponse
} from '../types/auth.types';

export interface IAuthService {
  register(data: RegisterRequest): Promise<AuthResponse>;
  login(data: LoginRequest): Promise<AuthResponse>;
  changePassword(userId: string, data: ChangePasswordRequest): Promise<{ message: string }>;
  validateUser(identifier: string, password: string): Promise<AuthResponse>;
}
