import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { authService } from '../../auth/services/auth.service';
import { devicePushService } from '../services/device-push.service';
import { prisma } from '../../../db';

describe('Device Module - push token', () => {
  const userA = {
    email: 'push-a@example.com',
    password: 'password123',
    fullName: 'Push User A',
    phoneNumber: '081111111101',
  };
  const userB = {
    email: 'push-b@example.com',
    password: 'password123',
    fullName: 'Push User B',
    phoneNumber: '081111111102',
  };

  let userIdA = '';
  let memberIdA = '';
  let userIdB = '';
  let memberIdB = '';
  const tokenValue = `fcm_test_${'x'.repeat(40)}`;

  beforeAll(async () => {
    const a = await authService.register(userA);
    userIdA = a.user.id;
    memberIdA = a.member.id;
    const b = await authService.register(userB);
    userIdB = b.user.id;
    memberIdB = b.member.id;
  });

  afterAll(async () => {
    await prisma.devicePushToken.deleteMany({
      where: { userId: { in: [userIdA, userIdB] } },
    });
    for (const memberId of [memberIdA, memberIdB]) {
      if (memberId) await prisma.member.delete({ where: { id: memberId } }).catch(() => undefined);
    }
    for (const userId of [userIdA, userIdB]) {
      if (userId) await prisma.user.delete({ where: { id: userId } }).catch(() => undefined);
    }
  });

  test('upsert creates new MOBILE token', async () => {
    const row = await devicePushService.upsertToken({
      userId: userIdA,
      token: tokenValue,
      deviceUuid: 'device-a-1',
    });

    expect(row.userId).toBe(userIdA);
    expect(row.platform).toBe('MOBILE');
    expect(row.token).toBe(tokenValue);
    expect(row.revokedAt).toBeNull();
  });

  test('upsert same token clears revokedAt', async () => {
    await prisma.devicePushToken.updateMany({
      where: { userId: userIdA, token: tokenValue },
      data: { revokedAt: new Date() },
    });

    const row = await devicePushService.upsertToken({
      userId: userIdA,
      token: tokenValue,
      deviceUuid: 'device-a-2',
    });

    expect(row.revokedAt).toBeNull();
    expect(row.deviceUuid).toBe('device-a-2');
  });

  test('user B can register same token string without taking A row', async () => {
    const rowB = await devicePushService.upsertToken({
      userId: userIdB,
      token: tokenValue,
    });

    expect(rowB.userId).toBe(userIdB);

    const count = await prisma.devicePushToken.count({
      where: { token: tokenValue },
    });
    expect(count).toBe(2);
  });

  test('revoke sets revokedAt for owner only', async () => {
    const revoked = await devicePushService.revokeToken({
      userId: userIdA,
      token: tokenValue,
    });

    expect(revoked?.revokedAt).not.toBeNull();

    const stillActiveB = await prisma.devicePushToken.findUnique({
      where: {
        userId_token: { userId: userIdB, token: tokenValue },
      },
    });
    expect(stillActiveB?.revokedAt).toBeNull();
  });
});
