import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { tierService } from '../services/tier.service';
import { prisma } from '../../../db';

describe('Tier Module - Benefits on levels', () => {
  let goldTierId: number;
  const createdBenefitIds: string[] = [];

  beforeAll(async () => {
    const goldTier = await prisma.tierMember.upsert({
      where: { tierCode: 'GOLD' },
      update: {},
      create: { tierCode: 'GOLD', minPoints: 1001, maxPoints: 2000 }
    });
    goldTierId = goldTier.id;

    const first = await prisma.tierBenefit.create({
      data: {
        tierMemberId: goldTierId,
        title: 'Benefit urutan 2',
        description: 'Deskripsi benefit urutan 2',
        sortOrder: 2,
        isActive: true
      }
    });
    const second = await prisma.tierBenefit.create({
      data: {
        tierMemberId: goldTierId,
        title: 'Benefit urutan 0',
        description: 'Deskripsi benefit urutan 0',
        sortOrder: 0,
        isActive: true
      }
    });
    const inactive = await prisma.tierBenefit.create({
      data: {
        tierMemberId: goldTierId,
        title: 'Benefit nonaktif',
        description: 'Tidak boleh muncul',
        sortOrder: 1,
        isActive: false
      }
    });

    createdBenefitIds.push(first.id, second.id, inactive.id);
  });

  afterAll(async () => {
    if (createdBenefitIds.length > 0) {
      await prisma.tierBenefit.deleteMany({
        where: { id: { in: createdBenefitIds } }
      });
    }
  });

  test('Returns tier levels ordered by minPoints with benefit shape', async () => {
    const levels = await tierService.getTierLevels();

    expect(levels.length).toBeGreaterThan(0);
    for (let index = 1; index < levels.length; index += 1) {
      expect(levels[index].minPoints).toBeGreaterThanOrEqual(levels[index - 1].minPoints);
    }

    levels.forEach((level) => {
      expect(typeof level.id).toBe('number');
      expect(typeof level.tierCode).toBe('string');
      expect(typeof level.tierName).toBe('string');
      expect(Array.isArray(level.benefits)).toBe(true);
      expect(Array.isArray(level.conversionRules)).toBe(true);

      level.benefits.forEach((benefit) => {
        expect(typeof benefit.id).toBe('string');
        expect(typeof benefit.title).toBe('string');
        expect(typeof benefit.description).toBe('string');
        expect(typeof benefit.sortOrder).toBe('number');
      });
    });
  });

  test('Filters inactive benefits and sorts by sortOrder', async () => {
    const levels = await tierService.getTierLevels();
    const gold = levels.find((level) => level.tierCode === 'GOLD');

    expect(gold).toBeDefined();
    const testBenefits = gold!.benefits.filter((benefit) =>
      createdBenefitIds.includes(benefit.id)
    );

    expect(testBenefits.some((benefit) => benefit.title === 'Benefit nonaktif')).toBe(false);
    expect(testBenefits.map((benefit) => benefit.title)).toEqual([
      'Benefit urutan 0',
      'Benefit urutan 2'
    ]);
  });
});
