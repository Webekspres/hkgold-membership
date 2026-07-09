import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { branchService } from '../services/branch.service';
import { prisma } from '../../../db';

describe('Branch Module - Get Detail', () => {
  let testBranchId: number;
  const testSuffix = Date.now().toString().slice(-6);

  beforeAll(async () => {
    // Create test branch
    const branch = await prisma.branch.create({
      data: {
        branchCode: `TD${testSuffix}`,
        name: 'Test Branch Detail',
        address: 'Test Address 123',
        phone: '081234567890',
        locationUrl: 'https://maps.google.com/test',
        isOnlineWarehouse: false
      }
    });
    testBranchId = branch.id;
  });

  afterAll(async () => {
    // Cleanup
    try {
      await prisma.branch.delete({ where: { id: testBranchId } });
    } catch (error) {
      // Ignore cleanup errors
    }
  });

  test('Happy path: Valid ID returns branch dengan images', async () => {
    const result = await branchService.getById(testBranchId);

    expect(result).toBeDefined();
    expect(result?.id).toBe(testBranchId);
    expect(result?.branchCode).toBe(`TD${testSuffix}`);
    expect(result?.name).toBe('Test Branch Detail');
    expect(result?.address).toBe('Test Address 123');
    expect(result?.phone).toBe('081234567890');
    expect(result?.locationUrl).toBe('https://maps.google.com/test');
    expect(result?.isOnlineWarehouse).toBe(false);
    expect(Array.isArray(result?.images)).toBe(true);
  });

  test('Edge case 1: Non-existent ID returns null', async () => {
    const result = await branchService.getById(999999);

    expect(result).toBeNull();
  });

  test('Edge case 2: Negative ID returns null', async () => {
    const result = await branchService.getById(-1);

    expect(result).toBeNull();
  });

  test('Edge case 3: Zero ID returns null', async () => {
    const result = await branchService.getById(0);

    expect(result).toBeNull();
  });
});
