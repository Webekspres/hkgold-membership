import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { authService } from '../services/auth.service';
import { prisma } from '../../../db';

describe('Auth Module - Change Password Endpoint', () => {
  const testUser = {
    email: 'test-changepass@example.com',
    password: 'oldpassword123',
    fullName: 'Test User Change Password',
    phoneNumber: '081234567898'
  };

  let createdUserId: string;
  let createdMemberId: string;

  beforeAll(async () => {
    // Create test user
    const result = await authService.register(testUser);
    createdUserId = result.user.id;
    createdMemberId = result.member.id;
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

  test('Happy path: Valid old password + new password changes password', async () => {
    const result = await authService.changePassword(createdUserId, {
      oldPassword: testUser.password,
      newPassword: 'newpassword123'
    });

    expect(result).toBeDefined();
    expect(result.message).toBe('Password berhasil diubah');

    // Verify can login with new password
    const loginResult = await authService.login({
      identifier: testUser.phoneNumber,
      password: 'newpassword123'
    });
    expect(loginResult).toBeDefined();
    expect(loginResult.accessToken).toBeDefined();

    // Reset password for other tests
    await authService.changePassword(createdUserId, {
      oldPassword: 'newpassword123',
      newPassword: testUser.password
    });
  });

  test('Edge case 1: Wrong old password returns error', async () => {
    try {
      await authService.changePassword(createdUserId, {
        oldPassword: 'wrongoldpassword',
        newPassword: 'newpassword123'
      });
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toBe('Password lama salah');
    }
  });

  test('Edge case 2: New password same as old returns error', async () => {
    try {
      await authService.changePassword(createdUserId, {
        oldPassword: testUser.password,
        newPassword: testUser.password
      });
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toBe('Password baru tidak boleh sama dengan password lama');
    }
  });

  test('Edge case 3: Non-existent userId returns error', async () => {
    try {
      await authService.changePassword('non-existent-user-id', {
        oldPassword: testUser.password,
        newPassword: 'newpassword123'
      });
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toBe('User tidak ditemukan');
    }
  });

  test('Edge case 4: Weak new password (< 8 chars) returns error', async () => {
    try {
      await authService.changePassword(createdUserId, {
        oldPassword: testUser.password,
        newPassword: 'short'
      });
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toBe('Password baru minimal 8 karakter');
    }
  });

  test('Edge case 5: Missing old password returns error', async () => {
    try {
      await authService.changePassword(createdUserId, {
        oldPassword: '',
        newPassword: 'newpassword123'
      });
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toBe('Old password dan new password wajib diisi');
    }
  });

  test('Edge case 6: Missing new password returns error', async () => {
    try {
      await authService.changePassword(createdUserId, {
        oldPassword: testUser.password,
        newPassword: ''
      });
      throw new Error('Should have thrown error');
    } catch (error) {
      expect(error instanceof Error).toBe(true);
      expect((error as Error).message).toBe('Old password dan new password wajib diisi');
    }
  });
});
