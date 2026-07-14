import { Elysia } from 'elysia';
import { authService } from '../services/auth.service';
import { jwtService } from '../services/jwt.service';
import {
  RegisterRequest,
  LoginRequest,
  ChangePasswordRequest
} from '../types/auth.types';

export const authRoutes = new Elysia({ prefix: '/api/auth' })
  .post('/register', async ({ body, set }) => {
    try {
      const data = body as RegisterRequest;
      const result = await authService.register(data);

      return {
        success: true,
        message: 'Registrasi berhasil',
        data: result
      };
    } catch (error) {
      set.status = 400;
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Registrasi gagal'
      };
    }
  })
  .post('/login', async ({ body, set }) => {
    try {
      const data = body as LoginRequest;
      const result = await authService.login(data);

      return {
        success: true,
        message: 'Login berhasil',
        data: result
      };
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Login gagal';

      if (message.includes('dinonaktifkan')) {
        set.status = 403;
      } else if (message.includes('tidak ditemukan') || message.includes('salah')) {
        set.status = 401;
      } else {
        set.status = 400;
      }

      return {
        success: false,
        message
      };
    }
  })
  .post('/change-password', async ({ body, headers, set }) => {
    try {
      // Extract JWT token from Authorization header
      const authHeader = headers.authorization;
      if (!authHeader || !authHeader.startsWith('Bearer ')) {
        set.status = 401;
        return {
          success: false,
          message: 'Token tidak ditemukan'
        };
      }

      const token = authHeader.slice(7);
      const payload = await jwtService.verifyAccessToken(token);

      const data = body as ChangePasswordRequest;
      const result = await authService.changePassword(payload.userId, data);

      return {
        success: true,
        message: result.message
      };
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Gagal mengubah password';

      if (message.includes('Token') || message.includes('Invalid')) {
        set.status = 401;
      } else if (message.includes('salah')) {
        set.status = 401;
      } else {
        set.status = 400;
      }

      return {
        success: false,
        message
      };
    }
  })
  .post('/refresh', async ({ body, set }) => {
    try {
      const { refreshToken } = body as { refreshToken: string };

      if (!refreshToken) {
        set.status = 400;
        return {
          success: false,
          message: 'Refresh token diperlukan'
        };
      }

      const payload = await jwtService.verifyRefreshToken(refreshToken);

      // Generate new token pair
      const tokens = await jwtService.generateTokenPair({
        userId: payload.userId,
        memberId: payload.memberId,
        role: payload.role,
        isActive: payload.isActive,
        isSuspended: payload.isSuspended
      });

      return {
        success: true,
        message: 'Token berhasil diperbarui',
        data: tokens
      };
    } catch (error) {
      set.status = 401;
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Refresh token tidak valid'
      };
    }
  });
