import { Elysia } from 'elysia';
import { jwtService } from '../modules/auth/services/jwt.service';
import { prisma } from '../db';

export interface AuthContext {
  userId: string;
  memberId: string;
  role: string;
  isActive: boolean;
  isSuspended: boolean;
}

export const authMiddleware = new Elysia({ name: 'auth' })
  .derive({ as: 'scoped' }, async ({ headers, set }): Promise<{ auth: AuthContext | null }> => {
    const authHeader = headers.authorization;

    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return { auth: null };
    }

    const token = authHeader.slice(7);

    try {
      const payload = await jwtService.verifyAccessToken(token);

      // Verify user still exists and is active
      const user = await prisma.user.findUnique({
        where: { id: payload.userId },
        select: { isActive: true }
      });

      if (!user || !user.isActive) {
        set.status = 401;
        return { auth: null };
      }

      return {
        auth: {
          userId: payload.userId,
          memberId: payload.memberId,
          role: payload.role,
          isActive: payload.isActive,
          isSuspended: payload.isSuspended
        }
      };
    } catch {
      set.status = 401;
      return { auth: null };
    }
  });

export const requireAuth = new Elysia()
  .use(authMiddleware)
  .onBeforeHandle({ as: 'scoped' }, ({ auth, set }) => {
    if (!auth) {
      set.status = 401;
      return {
        success: false,
        message: 'Unauthorized - Silakan login terlebih dahulu'
      };
    }
  })
  .as('scoped');

export const requireActiveUser = new Elysia()
  .use(requireAuth)
  .onBeforeHandle({ as: 'scoped' }, ({ auth, set }) => {
    if (auth && !auth.isActive) {
      set.status = 403;
      return {
        success: false,
        message: 'Akun Anda telah dinonaktifkan'
      };
    }
  })
  .as('scoped');

export const requireNotSuspended = new Elysia()
  .use(requireAuth)
  .onBeforeHandle({ as: 'scoped' }, ({ auth, set }) => {
    if (auth && auth.isSuspended) {
      set.status = 403;
      return {
        success: false,
        message: 'Akun Anda sedang disuspend. Hubungi admin untuk informasi lebih lanjut.'
      };
    }
  })
  .as('scoped');
