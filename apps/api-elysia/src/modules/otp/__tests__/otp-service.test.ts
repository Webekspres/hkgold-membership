import { describe, test, expect, beforeAll, afterAll, beforeEach } from 'bun:test';
import { prisma } from '../../../db';
import {
  createMemoryRedis,
  setRedisForTests,
  getRedis,
} from '../../../lib/redis';
import { otpService } from '../services/otp.service';
import { OtpError } from '../types/otp.types';

describe('OTP Module - generateOtp / verifyOtp', () => {
  const suffix = Date.now().toString().slice(-6);
  const phone = `0812${suffix}99`;
  const redeemTokenCode = `T${suffix}XXXX`.slice(0, 10).toUpperCase();
  let identifier: string;

  const originalFetch = globalThis.fetch;
  let fetchCalls: Array<{ url: string; init?: RequestInit }> = [];

  beforeAll(() => {
    process.env.FONNTE_TOKEN = process.env.FONNTE_TOKEN || 'test-fonnte-token';
    process.env.FONNTE_BASE_URL =
      process.env.FONNTE_BASE_URL || 'https://api.fonnte.com';
    process.env.FONNTE_OTP_MESSAGE_TEMPLATE =
      process.env.FONNTE_OTP_MESSAGE_TEMPLATE ||
      'Kode OTP HK GOLD VIP Anda: {otp}. Berlaku 5 menit. Jangan bagikan kepada siapapun.';
    process.env.REDEEM_OTP_EXPIRY_MINUTES =
      process.env.REDEEM_OTP_EXPIRY_MINUTES || '5';

    setRedisForTests(createMemoryRedis());
    identifier = otpService.buildIdentifierForTest(phone, redeemTokenCode);
  });

  beforeEach(() => {
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
  });

  afterAll(async () => {
    globalThis.fetch = originalFetch;
    try {
      await getRedis().del(identifier);
      await prisma.otpVerification.deleteMany({ where: { identifier } });
    } catch {
      // ignore cleanup
    }
    setRedisForTests(null);
  });

  test('generateOtp: writes Redis + DB and calls Fonnte', async () => {
    const result = await otpService.generateOtp({ phone, redeemTokenCode });

    expect(result.expiresAt).toBeDefined();
    expect(new Date(result.expiresAt).getTime()).toBeGreaterThan(Date.now());

    const cached = await getRedis().get(identifier);
    expect(cached).toMatch(/^\d{6}$/);

    const row = await prisma.otpVerification.findFirst({
      where: { identifier, isUsed: false },
      orderBy: { createdAt: 'desc' },
    });
    expect(row).toBeTruthy();
    expect(row!.otpCode).toBe(cached);

    expect(fetchCalls.length).toBe(1);
    expect(fetchCalls[0]!.url).toContain('/send');
    const headers = fetchCalls[0]!.init?.headers as Record<string, string>;
    expect(headers?.Authorization).toBeTruthy();
    const body = String(fetchCalls[0]!.init?.body ?? '');
    expect(body).toContain('target=');
    expect(body).toContain('message=');
  });

  test('verifyOtp: Redis hit succeeds then consumes OTP', async () => {
    await otpService.generateOtp({ phone, redeemTokenCode });
    const cached = await getRedis().get(identifier);
    expect(cached).toBeTruthy();

    const verified = await otpService.verifyOtp({
      phone,
      redeemTokenCode,
      otp: cached!,
    });
    expect(verified.verified).toBe(true);

    expect(await getRedis().get(identifier)).toBeNull();

    try {
      await otpService.verifyOtp({
        phone,
        redeemTokenCode,
        otp: cached!,
      });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(OtpError);
      expect((error as OtpError).code).toBe('OTP_INVALID');
    }
  });

  test('verifyOtp: DB fallback when Redis key missing', async () => {
    await otpService.generateOtp({ phone, redeemTokenCode });
    const cached = await getRedis().get(identifier);
    expect(cached).toBeTruthy();

    await getRedis().del(identifier);

    const verified = await otpService.verifyOtp({
      phone,
      redeemTokenCode,
      otp: cached!,
    });
    expect(verified.verified).toBe(true);

    const used = await prisma.otpVerification.findFirst({
      where: { identifier, otpCode: cached!, isUsed: true },
    });
    expect(used).toBeTruthy();
  });

  test('resend invalidates previous OTP', async () => {
    await otpService.generateOtp({ phone, redeemTokenCode });
    const firstOtp = await getRedis().get(identifier);
    expect(firstOtp).toBeTruthy();

    await otpService.generateOtp({ phone, redeemTokenCode });
    const secondOtp = await getRedis().get(identifier);
    expect(secondOtp).toBeTruthy();
    expect(secondOtp).not.toBe(firstOtp);

    try {
      await otpService.verifyOtp({
        phone,
        redeemTokenCode,
        otp: firstOtp!,
      });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(OtpError);
      expect((error as OtpError).code).toBe('OTP_INVALID');
    }

    const verified = await otpService.verifyOtp({
      phone,
      redeemTokenCode,
      otp: secondOtp!,
    });
    expect(verified.verified).toBe(true);
  });
``
  test('INVALID_PHONE for malformed number', async () => {
    try {
      await otpService.generateOtp({
        phone: '+1-555-0100',
        redeemTokenCode,
      });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(OtpError);
      expect((error as OtpError).code).toBe('INVALID_PHONE');
    }
  });

  test('INVALID_TOKEN_CODE for wrong length', async () => {
    try {
      await otpService.generateOtp({ phone, redeemTokenCode: 'SHORT' });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(OtpError);
      expect((error as OtpError).code).toBe('INVALID_TOKEN_CODE');
    }
  });

  test('wrong OTP while Redis live → OTP_INVALID, correct OTP still works', async () => {
    await otpService.generateOtp({ phone, redeemTokenCode });
    const cached = await getRedis().get(identifier);
    expect(cached).toBeTruthy();

    try {
      await otpService.verifyOtp({
        phone,
        redeemTokenCode,
        otp: '000000' === cached ? '111111' : '000000',
      });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(OtpError);
      expect((error as OtpError).code).toBe('OTP_INVALID');
    }

    expect(await getRedis().get(identifier)).toBe(cached);

    const verified = await otpService.verifyOtp({
      phone,
      redeemTokenCode,
      otp: cached!,
    });
    expect(verified.verified).toBe(true);
  });

  test('OTP_EXPIRED via DB fallback when Redis missing', async () => {
    await otpService.generateOtp({ phone, redeemTokenCode });
    const cached = await getRedis().get(identifier);
    expect(cached).toBeTruthy();

    await getRedis().del(identifier);
    await prisma.otpVerification.updateMany({
      where: { identifier, otpCode: cached!, isUsed: false },
      data: { expiredAt: new Date(Date.now() - 60_000) },
    });

    try {
      await otpService.verifyOtp({
        phone,
        redeemTokenCode,
        otp: cached!,
      });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(OtpError);
      expect((error as OtpError).code).toBe('OTP_EXPIRED');
    }
  });

  test('Fonnte failure cleans Redis + marks DB used', async () => {
    globalThis.fetch = (async () =>
      new Response(JSON.stringify({ status: false, reason: 'device offline' }), {
        status: 200,
        headers: { 'Content-Type': 'application/json' },
      })) as typeof fetch;

    try {
      await otpService.generateOtp({ phone, redeemTokenCode });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(OtpError);
      expect((error as OtpError).code).toBe('FONNTE_FAILED');
    }

    expect(await getRedis().get(identifier)).toBeNull();

    const live = await prisma.otpVerification.findFirst({
      where: { identifier, isUsed: false },
    });
    expect(live).toBeNull();
  });
});
