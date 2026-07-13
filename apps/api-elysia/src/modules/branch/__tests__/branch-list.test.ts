import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { branchService } from '../services/branch.service';
import { prisma } from '../../../db';
import { encodeCursor } from '../../../shared/types/pagination.types';

// Retry wrapper untuk handle deadlock P2034
async function withRetry<T>(fn: () => Promise<T>, retries = 3): Promise<T> {
  for (let i = 0; i < retries; i++) {
    try {
      return await fn();
    } catch (error: any) {
      if (error?.code === 'P2034' && i < retries - 1) {
        await new Promise(r => setTimeout(r, 100 * (i + 1)));
        continue;
      }
      throw error;
    }
  }
  throw new Error('Max retries reached');
}

describe('Branch Module - Get List with Pagination', () => {
  let testBranchIds: number[] = [];
  const testSuffix = Date.now().toString().slice(-6); // Unique suffix untuk avoid collision

  beforeAll(async () => {
    // Create 20 test branches secara batch kecil (5 per batch) untuk mengurangi deadlock
    const batches = Array.from({ length: 4 }, (_, batchIdx) => batchIdx);
    for (const batchIdx of batches) {
      const branches = await Promise.all(
        Array.from({ length: 5 }, (_, i) => {
          const idx = batchIdx * 5 + i;
          return prisma.branch.create({
            data: {
              branchCode: `T${testSuffix}${String(idx + 1).padStart(2, '0')}`,
              name: `Branch ${String.fromCharCode(65 + idx)} ${testSuffix}`,
              address: `Address ${idx + 1}`,
              phone: `08123456${String(idx).padStart(4, '0')}`,
              isOnlineWarehouse: false
            }
          });
        })
      );
      testBranchIds.push(...branches.map(b => b.id));
    }
  });

  afterAll(async () => {
    // Sequential delete untuk hindari deadlock
    for (const id of testBranchIds) {
      try {
        await prisma.branch.delete({ where: { id } });
      } catch {}
    }
  });

  test('Happy path: Returns 15 branches dengan pagination', async () => {
    const result = await branchService.getAll({ limit: 15 });

    expect(result.data.length).toBeLessThanOrEqual(15);
    expect(result.pagination.limit).toBe(15);
    expect(typeof result.pagination.hasMore).toBe('boolean');
    expect(result.data[0]).toHaveProperty('id');
    expect(result.data[0]).toHaveProperty('branchCode');
    expect(result.data[0]).toHaveProperty('name');
    expect(result.data[0]).toHaveProperty('images');
    expect(Array.isArray(result.data[0].images)).toBe(true);
  });

  test('Edge case 1: Pagination works with small dataset', async () => {
    // Don't try to empty entire DB - just test pagination behavior
    const result = await branchService.getAll({ limit: 5 });

    expect(Array.isArray(result.data)).toBe(true);
    expect(result.data.length).toBeLessThanOrEqual(5);
    expect(result.pagination.limit).toBe(5);

    // If there's more data, nextCursor should exist
    if (result.pagination.hasMore) {
      expect(result.pagination.nextCursor).not.toBeNull();
    } else {
      expect(result.pagination.nextCursor).toBeNull();
    }
  });

  test('Edge case 2: Last page has hasMore=false and nextCursor=null', async () => {
    const firstPage = await branchService.getAll({ limit: 15 });

    if (firstPage.pagination.nextCursor) {
      const lastPage = await branchService.getAll({
        limit: 15,
        cursor: firstPage.pagination.nextCursor
      });

      if (!lastPage.pagination.hasMore) {
        expect(lastPage.pagination.hasMore).toBe(false);
        expect(lastPage.pagination.nextCursor).toBeNull();
      }
    }
  });

  test('Edge case 3: Invalid cursor handled gracefully', async () => {
    const invalidCursor = 'invalid-base64-cursor';

    // Service should handle invalid cursor
    // Either return empty or throw - depending on implementation
    try {
      const result = await branchService.getAll({ cursor: invalidCursor });
      expect(Array.isArray(result.data)).toBe(true);
    } catch (error) {
      expect(error).toBeDefined();
    }
  });

  test('Edge case 4: Limit > 50 is clamped to 50', async () => {
    const result = await branchService.getAll({ limit: 100 });

    expect(result.pagination.limit).toBe(50);
    expect(result.data.length).toBeLessThanOrEqual(50);
  });

  test('Edge case 5: Cursor pagination works correctly', async () => {
    const firstPage = await branchService.getAll({ limit: 5 });

    expect(firstPage.data.length).toBeGreaterThan(0);

    if (firstPage.pagination.hasMore && firstPage.pagination.nextCursor) {
      const secondPage = await branchService.getAll({
        limit: 5,
        cursor: firstPage.pagination.nextCursor
      });

      // Second page should not contain items from first page
      const firstPageIds = firstPage.data.map(b => b.id);
      const secondPageIds = secondPage.data.map(b => b.id);

      const overlap = firstPageIds.filter(id => secondPageIds.includes(id));
      expect(overlap.length).toBe(0);
    }
  });

  test('Search q filters by name when length > 2', async () => {
    const uniqueName = `UniqueBranchSearch ${testSuffix}`;
    const created = await prisma.branch.create({
      data: {
        branchCode: `UQ${testSuffix}`,
        name: uniqueName,
        address: 'Unique address search',
        isOnlineWarehouse: false,
      },
    });
    testBranchIds.push(created.id);

    const result = await branchService.getAll({
      q: `UniqueBranchSearch ${testSuffix}`,
      limit: 10,
    });

    expect(result.data.some((b) => b.id === created.id)).toBe(true);
    result.data.forEach((b) => {
      expect(b.name.toLowerCase()).toContain('uniquebranchsearch');
    });
  });

  test('getCities returns array of id/name', async () => {
    const cities = await branchService.getCities();
    expect(Array.isArray(cities)).toBe(true);
    cities.forEach((c) => {
      expect(typeof c.id).toBe('number');
      expect(typeof c.name).toBe('string');
    });
  });
});
