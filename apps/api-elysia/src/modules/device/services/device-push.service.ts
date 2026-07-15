import { prisma } from '../../../db';

const MOBILE_PLATFORM = 'MOBILE';

export const devicePushService = {
  async upsertToken(params: {
    userId: string;
    token: string;
    deviceUuid?: string | null;
  }) {
    const token = params.token.trim();
    if (!token) {
      throw new Error('TOKEN_REQUIRED');
    }

    return prisma.devicePushToken.upsert({
      where: {
        userId_token: {
          userId: params.userId,
          token,
        },
      },
      create: {
        userId: params.userId,
        token,
        platform: MOBILE_PLATFORM,
        deviceUuid: params.deviceUuid?.trim() || null,
        revokedAt: null,
      },
      update: {
        platform: MOBILE_PLATFORM,
        deviceUuid: params.deviceUuid?.trim() || null,
        revokedAt: null,
      },
    });
  },

  async revokeToken(params: { userId: string; token: string }) {
    const token = params.token.trim();
    if (!token) {
      throw new Error('TOKEN_REQUIRED');
    }

    const existing = await prisma.devicePushToken.findUnique({
      where: {
        userId_token: {
          userId: params.userId,
          token,
        },
      },
    });

    if (!existing) {
      return null;
    }

    return prisma.devicePushToken.update({
      where: { id: existing.id },
      data: { revokedAt: new Date() },
    });
  },
};
