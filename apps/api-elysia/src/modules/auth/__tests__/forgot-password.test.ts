import { describe, test, expect, beforeAll, afterAll, beforeEach } from 'bun:test';
import { authService } from '../services/auth.service';
import { prisma } from '../../../db';
import {
  createMemoryRedis,
  setRedisForTests,
  getRedis,
} from '../../../lib/redis';
import { passwordResetOtpService } from '../../otp/services/password-reset-otp.service';
import { AuthError } from '../errors/auth.error';

describe('Auth Module - Forgot Password OTP', () => {
  const suffix = Date.now().toString().slice(-5);
  const testUser = {
    email: `forgot-${suffix}@example.com`,
    password: 'oldpassword123',
    fullName: 'Test Forgot Password',
    phoneNumber: `08123${suffix}01`,
  };

  let createdUserId: string;
  let createdMemberId: string;
  let phoneIdentifier: string;
  let cooldownKey: string;

  const originalFetch = globalThis.fetch;
  let fetchCalls: Array<{ url: string; init?: RequestInit }> = [];

  beforeAll(async () => {
    process.env.FONNTE_TOKEN = process.env.FONNTE_TOKEN || 'test-fonnte-token';
    process.env.FONNTE_BASE_URL =
      process.env.FONNTE_BASE_URL || 'https://api.fonnte.com';
    process.env.PASSWORD_RESET_OTP_EXPIRY_MINUTES = '5';
    process.env.PASSWORD_RESET_OTP_RESEND_SECONDS = '60';

    setRedisForTests(createMemoryRedis());

    const result = await authService.register(testUser);
    createdUserId = result.user.id;
    createdMemberId = result.member.id;
    phoneIdentifier = passwordResetOtpService.buildIdentifierForTest(
      result.member.phoneNumber,
    );
    cooldownKey = passwordResetOtpService.buildCooldownKeyForTest(
      result.member.phoneNumber,
    );
  });

  beforeEach(async () => {
    fetchCalls = [];
    globalThis.fetch = (async (input: RequestInfo | URL, init?: RequestInit) => {
      const url = String(input);
      fetchCalls.push({ url, init });
      return new Response(
        JSON.stringify({
          status: true,
          detail: 'success! message in queue',
        }),
        { status: 200, headers: { 'Content-Type': 'application/json' } },
      );
    }) as typeof fetch;

    await getRedis().del(phoneIdentifier);
    await getRedis().del(cooldownKey);
    await prisma.otpVerification.deleteMany({
      where: { identifier: phoneIdentifier },
    });
  });

  afterAll(async () => {
    globalThis.fetch = originalFetch;
    try {
      await getRedis().del(phoneIdentifier);
      await getRedis().del(cooldownKey);
      await prisma.otpVerification.deleteMany({
        where: { identifier: phoneIdentifier },
      });
      if (createdMemberId) {
        await prisma.member.delete({ where: { id: createdMemberId } });
      }
      if (createdUserId) {
        await prisma.user.delete({ where: { id: createdUserId } });
      }
    } catch {
      // ignore
    }
    setRedisForTests(null);
  });

  test('send-otp via email: writes Redis + calls Fonnte', async () => {
    const result = await authService.sendForgotPasswordOtp({
      identifier: testUser.email,
    });

    expect(result.expiresAt).toBeDefined();
    expect(result.resendAvailableAt).toBeDefined();
    expect(result.maskedPhone).toContain('****');
    expect(fetchCalls.length).toBe(1);

    const cached = await getRedis().get(phoneIdentifier);
    expect(cached).toMatch(/^\d{6}$/);
  });

  test('send-otp via phone works', async () => {
    const result = await authService.sendForgotPasswordOtp({
      identifier: testUser.phoneNumber,
    });
    expect(result.maskedPhone).toBeDefined();
    expect(fetchCalls.length).toBe(1);
  });

  test('send-otp rejects member number', async () => {
    try {
      await authService.sendForgotPasswordOtp({ identifier: '2607-0001' });
      throw new Error('Should have thrown');
    } catch (error) {
      expect(error).toBeInstanceOf(AuthError);
      expect((error as AuthError).code).toBe('VALIDATION');
      expect((error as AuthError).message).toContain('nomor member');
    }
  });

  test('send-otp via JWT (userId) ignores identifier', async () => {
    const result = await authService.sendForgotPasswordOtp(
      { identifier: 'ignored@example.com' },
      createdUserId,
    );
    expect(result.expiresAt).toBeDefined();
    expect(fetchCalls.length).toBe(1);
  });

  test('send-otp cooldown blocks resend', async () => {
    await authService.sendForgotPasswordOtp({ identifier: testUser.email });
    try {
      await authService.sendForgotPasswordOtp({ identifier: testUser.email });
      throw new Error('Should have thrown');
    } catch (error) {
      expect(error).toBeInstanceOf(AuthError);
      expect((error as AuthError).code).toBe('RESEND_COOLDOWN');
    }
  });

  test('send-otp unknown account → NOT_FOUND', async () => {
    try {
      await authService.sendForgotPasswordOtp({
        identifier: 'nobody-xyz@example.com',
      });
      throw new Error('Should have thrown');
    } catch (error) {
      expect(error).toBeInstanceOf(AuthError);
      expect((error as AuthError).code).toBe('NOT_FOUND');
    }
  });

  test('reset: happy path with OTP from Redis', async () => {
    await authService.sendForgotPasswordOtp({ identifier: testUser.email });
    const otp = await getRedis().get(phoneIdentifier);
    expect(otp).toBeTruthy();

    // clear cooldown so test isolation ok
    await getRedis().del(cooldownKey);

    const result = await authService.resetPasswordWithOtp({
      identifier: testUser.email,
      otp: otp!,
      newPassword: 'newpassword456',
    });
    expect(result.message).toBe('Password berhasil diubah');

    const login = await authService.login({
      identifier: testUser.email,
      password: 'newpassword456',
    });
    expect(login.accessToken).toBeDefined();

    // restore for other tests
    await authService.changePassword(createdUserId, {
      oldPassword: 'newpassword456',
      newPassword: testUser.password,
    });
  });

  test('reset: wrong OTP fails', async () => {
    await authService.sendForgotPasswordOtp({ identifier: testUser.email });
    try {
      await authService.resetPasswordWithOtp({
        identifier: testUser.email,
        otp: '000000',
        newPassword: 'newpassword456',
      });
      throw new Error('Should have thrown');
    } catch (error) {
      expect(error).toBeInstanceOf(AuthError);
      expect((error as AuthError).code).toBe('OTP_INVALID');
    }
  });

  test('WA_NOT_SET when member phone emptied', async () => {
    await prisma.member.update({
      where: { id: createdMemberId },
      data: { phoneNumber: '' },
    });

    try {
      await authService.sendForgotPasswordOtp(
        {},
        createdUserId,
      );
      throw new Error('Should have thrown');
    } catch (error) {
      expect(error).toBeInstanceOf(AuthError);
      expect((error as AuthError).code).toBe('WA_NOT_SET');
    } finally {
      // restore unique phone for cleanup/other tests
      const restored = `+62${testUser.phoneNumber.replace(/\D/g, '').replace(/^0/, '')}`;
      await prisma.member.update({
        where: { id: createdMemberId },
        data: { phoneNumber: restored },
      });
    }
  });
});
