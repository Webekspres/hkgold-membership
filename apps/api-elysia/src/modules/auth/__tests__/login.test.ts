import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { authService } from '../services/auth.service';
import { prisma } from '../../../db';

describe('Auth Module - Login Endpoint', () => {
  const testUser = {
    email: 'test-login@example.com',
    password: 'password123',
    fullName: 'Test User Login',
    phoneNumber: '081234567899'
  };

  let createdUserId: string;
  let createdMemberId: string;
  let memberNumber: string;

  beforeAll(async () => {
    // Create test user for login tests
    const result = await authService.register(testUser);
    createdUserId = result.user.id;
    createdMemberId = result.member.id;
    memberNumber = result.member.memberNumber;
  });

  afterAll(async () => {
    // Cleanup
    try {
      if (createdMemberId) {
        await prisma.member.delete({ where: { id: createdMemberId } });
      }
      if (createdUserId) {
        await prisma.user.delete({ where: { id: createdUserId } });
      }
    } catch (error) {
      // Ignore cleanup errors
    }
  });

  test('Happy path 1: Valid phone + password returns token', async () => {
    const result = await authService.login({
      identifier: testUser.phoneNumber,
      password: testUser.password
    });

    expect(result).toBeDefined();
    expect(result.accessToken).toBeDefined();
    expect(result.refreshToken).toBeDefined();
    expect(result.user.email).toBe(testUser.email);
    expect(result.member.phoneNumber).toBe('+6281234567899');
  });

  test('Happy path 2: Valid memberNumber + password returns token', async () => {
    const result = await authService.login({
      identifier: memberNumber,
      password: testUser.password
    });

    expect(result).toBeDefined();
    expect(result.accessToken).toBeDefined();
    expect(result.refreshToken).toBeDefined();
    expect(result.user.email).toBe(testUser.email);
    expect(result.member.memberNumber).toBe(memberNumber);
  });

  test('Edge case 1: Wrong password returns error', async () => {
    try {
      await authService.login({
        identifier: testUser.phoneNumber,
        password: 'wrongpassword'
      });
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toBe('Password salah');
    }
  });

  test('Edge case 2: Non-existent user returns error', async () => {
    try {
      await authService.login({
        identifier: '089999999999',
        password: 'password123'
      });
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toBe('User tidak ditemukan');
    }
  });

  test('Edge case 3: User.isActive = false returns error', async () => {
    // Deactivate user
    await prisma.user.update({
      where: { id: createdUserId },
      data: { isActive: false }
    });

    try {
      await authService.login({
        identifier: testUser.phoneNumber,
        password: testUser.password
      });
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toBe('Akun Anda telah dinonaktifkan');
    }

    // Reactivate for other tests
    await prisma.user.update({
      where: { id: createdUserId },
      data: { isActive: true }
    });
  });

  test('Edge case 4: Member.isSuspended = true still allows login', async () => {
    // Suspend member
    await prisma.member.update({
      where: { id: createdMemberId },
      data: { isSuspended: true }
    });

    const result = await authService.login({
      identifier: testUser.phoneNumber,
      password: testUser.password
    });

    expect(result).toBeDefined();
    expect(result.accessToken).toBeDefined();
    expect(result.member.isSuspended).toBe(true);

    // Unsuspend
    await prisma.member.update({
      where: { id: createdMemberId },
      data: { isSuspended: false }
    });
  });

  test('Edge case 5: Missing identifier returns error', async () => {
    try {
      await authService.login({
        identifier: '',
        password: 'password123'
      });
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toBe('Identifier dan password wajib diisi');
    }
  });

  test('Edge case 6: Missing password returns error', async () => {
    try {
      await authService.login({
        identifier: testUser.phoneNumber,
        password: ''
      });
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toBe('Identifier dan password wajib diisi');
    }
  });

  test('Edge case 7: Phone number with 08 prefix works', async () => {
    const result = await authService.login({
      identifier: '081234567899', // 08 prefix instead of +62
      password: testUser.password
    });

    expect(result).toBeDefined();
    expect(result.accessToken).toBeDefined();
  });
});
