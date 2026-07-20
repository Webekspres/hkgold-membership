import { afterAll, beforeAll, describe, expect, test } from 'bun:test';

import { prisma } from '../../../db';
import { rewardService } from '../services/reward.service';

describe('Reward Module - stock filtering', () => {
  const suffix = Date.now().toString().slice(-6);

  const now = new Date();
  const startAt = new Date(now.getTime() - 7 * 86400000);
  const endAt = new Date(now.getTime() + 30 * 86400000);

  let branchAId: number;
  let branchBId: number;
  let categoryInStockId: number;
  let categoryEmptyId: number;

  const rewardIds: string[] = [];
  const skus = {
    inStock: `RS-IN-${suffix}`,
    heldOut: `RS-HO-${suffix}`,
    noStockRow: `RS-NS-${suffix}`,
    multiBranch: `RS-MB-${suffix}`,
    branchAOnly: `RS-BA-${suffix}`,
    inactive: `RS-INA-${suffix}`,
  };

  async function createReward(
    sku: string,
    categoryId: number,
    options?: { isActive?: boolean },
  ) {
    const reward = await prisma.reward.create({
      data: {
        categoryId,
        name: `Stock filter ${sku}`,
        sku,
        description: 'stock filter test',
        pointsRequired: 500,
        isActive: options?.isActive ?? true,
        startAt,
        endAt,
      },
    });
    rewardIds.push(reward.id);
    return reward;
  }

  async function addStock(
    rewardId: string,
    branchId: number,
    actualStock: number,
    heldStock: number,
  ) {
    await prisma.rewardBranchStock.create({
      data: { rewardId, branchId, actualStock, heldStock },
    });
  }

  beforeAll(async () => {
    const branchA = await prisma.branch.create({
      data: {
        branchCode: `RSA${suffix}`,
        name: `Stock Filter Branch A ${suffix}`,
        address: 'Jl. A',
        isOnlineWarehouse: false,
      },
    });
    branchAId = branchA.id;

    const branchB = await prisma.branch.create({
      data: {
        branchCode: `RSB${suffix}`,
        name: `Stock Filter Branch B ${suffix}`,
        address: 'Jl. B',
        isOnlineWarehouse: false,
      },
    });
    branchBId = branchB.id;

    const categoryInStock = await prisma.categoryReward.create({
      data: {
        name: `Stock Filter In ${suffix}`,
        slug: `stock-filter-in-${suffix}`,
      },
    });
    categoryInStockId = categoryInStock.id;

    const categoryEmpty = await prisma.categoryReward.create({
      data: {
        name: `Stock Filter Empty ${suffix}`,
        slug: `stock-filter-empty-${suffix}`,
      },
    });
    categoryEmptyId = categoryEmpty.id;

    const inStock = await createReward(skus.inStock, categoryInStockId);
    await addStock(inStock.id, branchAId, 5, 0);

    const heldOut = await createReward(skus.heldOut, categoryInStockId);
    await addStock(heldOut.id, branchAId, 5, 5);

    await createReward(skus.noStockRow, categoryInStockId);

    const multiBranch = await createReward(skus.multiBranch, categoryInStockId);
    await addStock(multiBranch.id, branchAId, 3, 0);
    await addStock(multiBranch.id, branchBId, 2, 2);

    const branchAOnly = await createReward(skus.branchAOnly, categoryInStockId);
    await addStock(branchAOnly.id, branchAId, 1, 0);

    const emptyCategoryReward = await createReward(`RS-EC-${suffix}`, categoryEmptyId);
    await addStock(emptyCategoryReward.id, branchAId, 4, 4);

    const inactive = await createReward(skus.inactive, categoryInStockId, {
      isActive: false,
    });
    await addStock(inactive.id, branchAId, 9, 0);
  });

  afterAll(async () => {
    if (rewardIds.length > 0) {
      await prisma.rewardBranchStock.deleteMany({ where: { rewardId: { in: rewardIds } } });
      await prisma.reward.deleteMany({ where: { id: { in: rewardIds } } });
    }
    await prisma.categoryReward.deleteMany({
      where: { id: { in: [categoryInStockId, categoryEmptyId] } },
    });
    await prisma.branch.deleteMany({ where: { id: { in: [branchAId, branchBId] } } });
  });

  function ourSkus(items: Array<{ sku: string }>) {
    return items.map((item) => item.sku).filter((sku) => sku.includes(suffix));
  }

  test('getRewards excludes zero available stock and inactive rewards', async () => {
    const result = await rewardService.getRewards({
      limit: 50,
      categoryIds: [categoryInStockId],
      sortBy: 'sku',
      sortOrder: 'asc',
    });

    const skusFound = ourSkus(result.data);
    expect(skusFound).toContain(skus.inStock);
    expect(skusFound).toContain(skus.multiBranch);
    expect(skusFound).toContain(skus.branchAOnly);
    expect(skusFound).not.toContain(skus.heldOut);
    expect(skusFound).not.toContain(skus.noStockRow);
    expect(skusFound).not.toContain(skus.inactive);

    const inStockItem = result.data.find((item) => item.sku === skus.inStock);
    expect(inStockItem?.stockRemaining).toBe(5);

    const multiItem = result.data.find((item) => item.sku === skus.multiBranch);
    expect(multiItem?.stockRemaining).toBe(3);
  });

  test('getRewards with branchId hides reward when that branch is fully held', async () => {
    const branchA = await rewardService.getRewards({
      limit: 50,
      categoryIds: [categoryInStockId],
      branchId: branchAId,
      sortBy: 'sku',
      sortOrder: 'asc',
    });
    expect(ourSkus(branchA.data)).toContain(skus.inStock);
    expect(ourSkus(branchA.data)).not.toContain(skus.heldOut);

    const branchB = await rewardService.getRewards({
      limit: 50,
      categoryIds: [categoryInStockId],
      branchId: branchBId,
      sortBy: 'sku',
      sortOrder: 'asc',
    });
    expect(ourSkus(branchB.data)).not.toContain(skus.multiBranch);
    expect(ourSkus(branchB.data)).not.toContain(skus.branchAOnly);
  });

  test('getRewards with branchId still lists reward available only on that branch', async () => {
    const result = await rewardService.getRewards({
      limit: 50,
      categoryIds: [categoryInStockId],
      branchId: branchAId,
      sortBy: 'sku',
      sortOrder: 'asc',
    });
    expect(ourSkus(result.data)).toContain(skus.branchAOnly);
  });

  test('getCatalog omits out-of-stock rewards and empty categories', async () => {
    const catalog = await rewardService.getCatalog({
      categoryIds: [categoryInStockId, categoryEmptyId],
    });

    const inStockGroup = catalog.find((group) => group.id === categoryInStockId);
    expect(inStockGroup).toBeDefined();
    const skusFound = inStockGroup!.rewards.map((reward) => reward.sku);
    expect(skusFound).toContain(skus.inStock);
    expect(skusFound).not.toContain(skus.heldOut);
    expect(skusFound).not.toContain(skus.noStockRow);
    expect(inStockGroup!.rewards.every((reward) => reward.stockRemaining > 0)).toBe(true);

    expect(catalog.some((group) => group.id === categoryEmptyId)).toBe(false);
  });

  test('getBySku returns reward with stockRemaining 0 but omits zero-stock branches', async () => {
    const heldOut = await rewardService.getBySku(skus.heldOut);
    expect(heldOut).not.toBeNull();
    expect(heldOut!.stockRemaining).toBe(0);
    expect(heldOut!.branchStocks).toHaveLength(0);

    const multi = await rewardService.getBySku(skus.multiBranch);
    expect(multi).not.toBeNull();
    expect(multi!.stockRemaining).toBe(3);
    expect(multi!.branchStocks.map((stock) => stock.branchId)).toEqual([branchAId]);
    expect(multi!.branchStocks.every((stock) => stock.actualStock - stock.heldStock > 0)).toBe(
      true,
    );
  });

  test('getBySku returns null for inactive reward', async () => {
    const detail = await rewardService.getBySku(skus.inactive);
    expect(detail).toBeNull();
  });

  test('getHomePreview skips newest out-of-stock rewards within category', async () => {
    const homeSuffix = `${suffix}HP`;
    const homeCategory = await prisma.categoryReward.create({
      data: {
        name: `Stock Home ${homeSuffix}`,
        slug: `stock-home-${homeSuffix}`,
      },
    });

    const homeRewardIds: string[] = [];
    const base = Date.now();

    async function setUpdatedAt(id: string, updatedAt: Date) {
      await prisma.$executeRaw`
        UPDATE rewards SET updated_at = ${updatedAt} WHERE id = ${id}
      `;
    }

    try {
      const newestOos = await prisma.reward.create({
        data: {
          categoryId: homeCategory.id,
          name: 'Newest OOS',
          sku: `RS-HP-OOS-${homeSuffix}`,
          description: 'newest but held out',
          pointsRequired: 100,
          isActive: true,
          startAt,
          endAt,
        },
      });
      homeRewardIds.push(newestOos.id);
      await setUpdatedAt(newestOos.id, new Date(base + 3000));
      await addStock(newestOos.id, branchAId, 2, 2);

      const secondOos = await prisma.reward.create({
        data: {
          categoryId: homeCategory.id,
          name: 'Second OOS',
          sku: `RS-HP-OOS2-${homeSuffix}`,
          description: 'second also held out',
          pointsRequired: 100,
          isActive: true,
          startAt,
          endAt,
        },
      });
      homeRewardIds.push(secondOos.id);
      await setUpdatedAt(secondOos.id, new Date(base + 2000));
      await addStock(secondOos.id, branchAId, 1, 1);

      const olderInStock = await prisma.reward.create({
        data: {
          categoryId: homeCategory.id,
          name: 'Older in stock',
          sku: `RS-HP-OK-${homeSuffix}`,
          description: 'older but available',
          pointsRequired: 100,
          isActive: true,
          startAt,
          endAt,
        },
      });
      homeRewardIds.push(olderInStock.id);
      await setUpdatedAt(olderInStock.id, new Date(base + 1000));
      await addStock(olderInStock.id, branchAId, 4, 0);

      const preview = await rewardService.getHomePreview();
      const group = preview.find((entry) => entry.id === homeCategory.id);

      expect(group).toBeDefined();
      expect(group!.rewards.map((reward) => reward.sku)).toEqual([`RS-HP-OK-${homeSuffix}`]);
      expect(group!.rewards[0].stockRemaining).toBe(4);
    } finally {
      if (homeRewardIds.length > 0) {
        await prisma.rewardBranchStock.deleteMany({
          where: { rewardId: { in: homeRewardIds } },
        });
        await prisma.reward.deleteMany({ where: { id: { in: homeRewardIds } } });
      }
      await prisma.categoryReward.delete({ where: { id: homeCategory.id } });
    }
  test('getRewards over-fetches past consecutive out-of-stock rows', async () => {
    const overflowCategory = await prisma.categoryReward.create({
      data: {
        name: `Stock Overflow ${suffix}`,
        slug: `stock-overflow-${suffix}`,
      },
    });
    const overflowIds: string[] = [];

    try {
      for (let i = 0; i < 6; i++) {
        const reward = await createReward(`RS-OOS-${i}-${suffix}`, overflowCategory.id);
        await addStock(reward.id, branchAId, 2, 2);
        overflowIds.push(reward.id);
      }

      const lastInStock = await createReward(`RS-ZZ-LAST-${suffix}`, overflowCategory.id);
      await addStock(lastInStock.id, branchAId, 2, 0);
      overflowIds.push(lastInStock.id);

      const result = await rewardService.getRewards({
        limit: 3,
        categoryIds: [overflowCategory.id],
        sortBy: 'sku',
        sortOrder: 'asc',
      });

      expect(result.data.map((item) => item.sku)).toEqual([`RS-ZZ-LAST-${suffix}`]);
      expect(result.data[0]?.stockRemaining).toBe(2);
      expect(result.pagination.hasMore).toBe(false);
    } finally {
      if (overflowIds.length > 0) {
        await prisma.rewardBranchStock.deleteMany({
          where: { rewardId: { in: overflowIds } },
        });
        await prisma.reward.deleteMany({ where: { id: { in: overflowIds } } });
      }
      await prisma.categoryReward.delete({ where: { id: overflowCategory.id } });
    }
  });
});
