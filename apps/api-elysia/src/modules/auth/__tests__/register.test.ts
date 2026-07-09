import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { authService } from '../services/auth.service';
import { prisma } from '../../../db';

describe('Auth Module - Register Endpoint', () => {
  const testUser = {
    email: 'test-register@example.com',
    password: 'password123',
    fullName: 'Test User Register',
    phoneNumber: '081234567890'
  };

  afterAll(async () => {
    // Cleanup: Delete test user if exists
    try {
      const user = await prisma.user.findUnique({
        where: { email: testUser.email },
        include: { member: true }
      });
      if (user) {
        if (user.member) {
          await prisma.member.delete({ where: { id: user.member.id } });
        }
        await prisma.user.delete({ where: { id: user.id } });
      }
    } catch (error) {
      // Ignore cleanup errors
    }
  });

  test('Happy path: Valid data creates User + Member and returns token', async () => {
    const result = await authService.register(testUser);

    expect(result).toBeDefined();
    expect(result.accessToken).toBeDefined();
    expect(result.refreshToken).toBeDefined();
    expect(result.user.email).toBe(testUser.email);
    expect(result.user.fullName).toBe(testUser.fullName);
    expect(result.user.role).toBe('MEMBER');
    expect(result.user.isActive).toBe(true);
    expect(result.member.memberNumber).toMatch(/^HK[A-Z]\d{7}$/);
    expect(result.member.phoneNumber).toBe('+6281234567890'); // Normalized
    expect(result.member.currentTier).toBe('SILVER');
    expect(result.member.pointBalance).toBe(0);
  });

  test('Edge case 1: Duplicate email returns error', async () => {
    try {
      await authService.register(testUser);
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toBe('Email sudah terdaftar');
    }
  });

  test('Edge case 2: Duplicate phone number returns error', async () => {
    const duplicatePhone = {
      ...testUser,
      email: 'different@example.com'
    };

    try {
      await authService.register(duplicatePhone);
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toBe('Nomor HP sudah terdaftar');
    }
  });

  test('Edge case 3: Missing required fields returns error', async () => {
    const incomplete = {
      email: 'incomplete@example.com',
      password: 'password123'
      // Missing fullName and phoneNumber
    };

    try {
      // @ts-ignore - intentionally passing incomplete data
      await authService.register(incomplete);
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toBe('Semua field wajib diisi');
    }
  });

  test('Edge case 4: Invalid email format returns error', async () => {
    const invalidEmail = {
      email: 'not-an-email',
      password: 'password123',
      fullName: 'Test User',
      phoneNumber: '081234567891'
    };

    try {
      await authService.register(invalidEmail);
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toBe('Format email tidak valid');
    }
  });

  test('Edge case 5: Password less than 8 characters returns error', async () => {
    const weakPassword = {
      email: 'weakpass@example.com',
      password: 'short',
      fullName: 'Test User',
      phoneNumber: '081234567892'
    };

    try {
      await authService.register(weakPassword);
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toBe('Password minimal 8 karakter');
    }
  });

  test('Edge case 6: Invalid phone format returns error', async () => {
    const invalidPhone = {
      email: 'invalid-phone@example.com',
      password: 'password123',
      fullName: 'Test User',
      phoneNumber: '123456' // Not Indonesian format
    };

    try {
      await authService.register(invalidPhone);
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toContain('Nomor HP');
    }
  });
});
