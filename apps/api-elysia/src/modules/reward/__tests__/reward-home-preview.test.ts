import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { rewardService } from '../services/reward.service';
import { prisma } from '../../../db';

describe('Reward Module - getHomePreview', () => {
  const testSuffix = Date.now().toString().slice(-6);
  const categoryIds: number[] = [];
  const rewardIds: string[] = [];
  let branchId: number;

  const now = new Date();
  const startAt = new Date(now.getTime() - 7 * 86400000);
  const endAt = new Date(now.getTime() + 30 * 86400000);

  async function setUpdatedAt(id: string, updatedAt: Date) {
    await prisma.$executeRaw`
      UPDATE rewards SET updated_at = ${updatedAt} WHERE id = ${id}
    `;
  }

  beforeAll(async () => {
    const branch = await prisma.branch.create({
      data: {
        branchCode: `HP${testSuffix}`,
        name: `Home Preview Branch ${testSuffix}`,
        address: 'Jl. Home Preview Test',
        isOnlineWarehouse: false,
      },
    });
    branchId = branch.id;

    // 4 categories — only top 3 by max reward.updatedAt should appear
    for (let i = 1; i <= 4; i++) {
      const cat = await prisma.categoryReward.create({
        data: {
          name: `HomePrev Cat ${i} ${testSuffix}`,
          slug: `home-prev-cat-${i}-${testSuffix}`,
        },
      });
      categoryIds.push(cat.id);
    }

    // Cat1: max updated = T+40 (should rank #1), 3 rewards → only 2 newest
    // Cat2: max = T+30 (#2)
    // Cat3: max = T+20 (#3)
    // Cat4: max = T+10 (excluded)
    const specs: Array<{ catIdx: number; sku: string; ageHours: number }> = [
      { catIdx: 0, sku: `HP-C1-OLD-${testSuffix}`, ageHours: 10 },
      { catIdx: 0, sku: `HP-C1-MID-${testSuffix}`, ageHours: 30 },
      { catIdx: 0, sku: `HP-C1-NEW-${testSuffix}`, ageHours: 40 },
      { catIdx: 1, sku: `HP-C2-A-${testSuffix}`, ageHours: 25 },
      { catIdx: 1, sku: `HP-C2-B-${testSuffix}`, ageHours: 30 },
      { catIdx: 2, sku: `HP-C3-A-${testSuffix}`, ageHours: 15 },
      { catIdx: 2, sku: `HP-C3-B-${testSuffix}`, ageHours: 20 },
      { catIdx: 3, sku: `HP-C4-A-${testSuffix}`, ageHours: 10 },
    ];

    const base = Date.now();
    for (const spec of specs) {
      const reward = await prisma.reward.create({
        data: {
          categoryId: categoryIds[spec.catIdx],
          name: `Home preview ${spec.sku}`,
          sku: spec.sku,
          description: 'test home preview',
          pointsRequired: 1000,
          isActive: true,
          startAt,
          endAt,
        },
      });
      rewardIds.push(reward.id);
      await setUpdatedAt(reward.id, new Date(base + spec.ageHours * 3600000));
      await prisma.rewardBranchStock.create({
        data: {
          rewardId: reward.id,
          branchId,
          actualStock: 3,
          heldStock: 0,
        },
      });
    }

    // Inactive reward in cat1 — must not affect ranking / listing
    const inactive = await prisma.reward.create({
      data: {
        categoryId: categoryIds[0],
        name: 'Inactive home preview',
        sku: `HP-INACTIVE-${testSuffix}`,
        description: 'inactive',
        pointsRequired: 100,
        isActive: false,
        startAt,
        endAt,
      },
    });
    rewardIds.push(inactive.id);
    await setUpdatedAt(inactive.id, new Date(base + 100 * 3600000));
  });

  afterAll(async () => {
    if (rewardIds.length > 0) {
      await prisma.rewardBranchStock.deleteMany({ where: { rewardId: { in: rewardIds } } });
      await prisma.reward.deleteMany({ where: { id: { in: rewardIds } } });
    }
    if (categoryIds.length > 0) {
      await prisma.categoryReward.deleteMany({ where: { id: { in: categoryIds } } });
    }
    if (branchId) {
      await prisma.branch.delete({ where: { id: branchId } });
    }
  });

  test('returns ≤3 categories, ≤2 rewards each, freshest categories first', async () => {
    const result = await rewardService.getHomePreview();

    const ours = result.filter((g) => categoryIds.includes(g.id));
    expect(ours.length).toBeLessThanOrEqual(3);
    expect(ours.length).toBe(3);

    for (const group of ours) {
      expect(group.rewards.length).toBeLessThanOrEqual(2);
    }

    // Order: cat1, cat2, cat3 (by max updatedAt)
    expect(ours.map((g) => g.id)).toEqual([
      categoryIds[0],
      categoryIds[1],
      categoryIds[2],
    ]);

    // Cat1: NEW + MID (not OLD)
    const cat1Skus = ours[0].rewards.map((r) => r.sku);
    expect(cat1Skus).toContain(`HP-C1-NEW-${testSuffix}`);
    expect(cat1Skus).toContain(`HP-C1-MID-${testSuffix}`);
    expect(cat1Skus).not.toContain(`HP-C1-OLD-${testSuffix}`);
    expect(cat1Skus).not.toContain(`HP-INACTIVE-${testSuffix}`);

    // Cat4 excluded
    expect(ours.some((g) => g.id === categoryIds[3])).toBe(false);
  });
});
