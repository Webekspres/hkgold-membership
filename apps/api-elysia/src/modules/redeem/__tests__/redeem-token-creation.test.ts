import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { prisma } from '../../../db';
import { redeemService } from '../services/redeem.service';
import { RedeemError } from '../types/redeem.types';

describe('Redeem Module - createRedeemToken', () => {
  const suffix = Date.now().toString().slice(-6);

  const now = new Date();
  const startAt = new Date(now.getTime() - 7 * 86400000);
  const endAt = new Date(now.getTime() + 30 * 86400000);

  let branchId: number;
  let otherBranchId: number;
  let categoryId: number;
  let rewardId: string;
  let inactiveRewardId: string;
  let expiredPeriodRewardId: string;
  let futurePeriodRewardId: string;
  let lastUnitRewardId: string;
  let stockId: string;
  let inactiveStockId: string;
  let lastUnitStockId: string;

  let userId: string;
  let memberId: string;
  let lowPointUserId: string;
  let lowPointMemberId: string;
  let suspendedUserId: string;
  let suspendedMemberId: string;
  let exactUserId: string;
  let exactMemberId: string;
  let raceUserAId: string;
  let raceMemberAId: string;
  let raceUserBId: string;
  let raceMemberBId: string;

  const tokenIds: string[] = [];
  const POINTS_REQUIRED = 1000;

  const ymPrefix = `${now.getFullYear().toString().slice(-2)}${String(now.getMonth() + 1).padStart(2, '0')}`;

  beforeAll(async () => {
    const branch = await prisma.branch.create({
      data: {
        branchCode: `RD${suffix}`,
        name: `Redeem Test Branch ${suffix}`,
        address: 'Jl. Redeem Test 1',
        isOnlineWarehouse: false,
      },
    });
    branchId = branch.id;

    const otherBranch = await prisma.branch.create({
      data: {
        branchCode: `RX${suffix}`,
        name: `Redeem Other Branch ${suffix}`,
        address: 'Jl. Redeem Test 2',
        isOnlineWarehouse: false,
      },
    });
    otherBranchId = otherBranch.id;

    const category = await prisma.categoryReward.create({
      data: {
        name: `Redeem Cat ${suffix}`,
        slug: `redeem-cat-${suffix}`,
      },
    });
    categoryId = category.id;

    const reward = await prisma.reward.create({
      data: {
        categoryId,
        name: `Redeem Reward Active ${suffix}`,
        sku: `RD-ACT-${suffix}`,
        description: 'active reward for redeem tests',
        pointsRequired: POINTS_REQUIRED,
        isActive: true,
        startAt,
        endAt,
      },
    });
    rewardId = reward.id;

    const inactiveReward = await prisma.reward.create({
      data: {
        categoryId,
        name: `Redeem Reward Inactive ${suffix}`,
        sku: `RD-INA-${suffix}`,
        description: 'inactive reward for redeem tests',
        pointsRequired: POINTS_REQUIRED,
        isActive: false,
        startAt,
        endAt,
      },
    });
    inactiveRewardId = inactiveReward.id;

    const expiredPeriodReward = await prisma.reward.create({
      data: {
        categoryId,
        name: `Redeem Reward Ended ${suffix}`,
        sku: `RD-END-${suffix}`,
        description: 'ended period',
        pointsRequired: POINTS_REQUIRED,
        isActive: true,
        startAt: new Date(now.getTime() - 60 * 86400000),
        endAt: new Date(now.getTime() - 1 * 86400000),
      },
    });
    expiredPeriodRewardId = expiredPeriodReward.id;

    const futurePeriodReward = await prisma.reward.create({
      data: {
        categoryId,
        name: `Redeem Reward Future ${suffix}`,
        sku: `RD-FUT-${suffix}`,
        description: 'future period',
        pointsRequired: POINTS_REQUIRED,
        isActive: true,
        startAt: new Date(now.getTime() + 7 * 86400000),
        endAt: new Date(now.getTime() + 30 * 86400000),
      },
    });
    futurePeriodRewardId = futurePeriodReward.id;

    const lastUnitReward = await prisma.reward.create({
      data: {
        categoryId,
        name: `Redeem Reward LastUnit ${suffix}`,
        sku: `RD-LU-${suffix}`,
        description: 'last unit race',
        pointsRequired: POINTS_REQUIRED,
        isActive: true,
        startAt,
        endAt,
      },
    });
    lastUnitRewardId = lastUnitReward.id;

    const stock = await prisma.rewardBranchStock.create({
      data: {
        rewardId,
        branchId,
        actualStock: 5,
        heldStock: 0,
      },
    });
    stockId = stock.id;

    const inactiveStock = await prisma.rewardBranchStock.create({
      data: {
        rewardId: inactiveRewardId,
        branchId,
        actualStock: 5,
        heldStock: 0,
      },
    });
    inactiveStockId = inactiveStock.id;

    await prisma.rewardBranchStock.create({
      data: {
        rewardId: expiredPeriodRewardId,
        branchId,
        actualStock: 5,
        heldStock: 0,
      },
    });

    await prisma.rewardBranchStock.create({
      data: {
        rewardId: futurePeriodRewardId,
        branchId,
        actualStock: 5,
        heldStock: 0,
      },
    });

    const lastUnitStock = await prisma.rewardBranchStock.create({
      data: {
        rewardId: lastUnitRewardId,
        branchId,
        actualStock: 1,
        heldStock: 0,
      },
    });
    lastUnitStockId = lastUnitStock.id;

    async function makeMember(
      tag: string,
      seq: string,
      balance: number,
      suspended = false,
    ) {
      const user = await prisma.user.create({
        data: {
          email: `redeem-${tag}-${suffix}@example.com`,
          password: 'hashed',
          fullName: `Redeem ${tag}`,
          role: 'MEMBER',
          isActive: true,
        },
      });
      const member = await prisma.member.create({
        data: {
          userId: user.id,
          memberNumber: `${ymPrefix}-${seq}${suffix.slice(0, 3)}`,
          phoneNumber: `0811${suffix}${seq}`,
          currentTier: 'SILVER',
          pointBalance: balance,
          highestPoint: balance,
          isSuspended: suspended,
        },
      });
      return { userId: user.id, memberId: member.id };
    }

    ({ userId, memberId } = await makeMember('ok', '9', 5000));
    ({ userId: lowPointUserId, memberId: lowPointMemberId } = await makeMember(
      'low',
      '8',
      100,
    ));
    ({ userId: suspendedUserId, memberId: suspendedMemberId } =
      await makeMember('sus', '7', 5000, true));
    ({ userId: exactUserId, memberId: exactMemberId } = await makeMember(
      'exact',
      '6',
      POINTS_REQUIRED,
    ));
    ({ userId: raceUserAId, memberId: raceMemberAId } = await makeMember(
      'racea',
      '5',
      5000,
    ));
    ({ userId: raceUserBId, memberId: raceMemberBId } = await makeMember(
      'raceb',
      '4',
      5000,
    ));
  });

  afterAll(async () => {
    if (tokenIds.length > 0) {
      await prisma.redeemToken.deleteMany({ where: { id: { in: tokenIds } } });
    }
    await prisma.rewardBranchStock.deleteMany({
      where: {
        rewardId: {
          in: [
            rewardId,
            inactiveRewardId,
            expiredPeriodRewardId,
            futurePeriodRewardId,
            lastUnitRewardId,
          ].filter(Boolean),
        },
      },
    });
    await prisma.reward.deleteMany({
      where: {
        id: {
          in: [
            rewardId,
            inactiveRewardId,
            expiredPeriodRewardId,
            futurePeriodRewardId,
            lastUnitRewardId,
          ].filter(Boolean),
        },
      },
    });
    if (categoryId) {
      await prisma.categoryReward.deleteMany({ where: { id: categoryId } });
    }
    await prisma.member.deleteMany({
      where: {
        id: {
          in: [
            memberId,
            lowPointMemberId,
            suspendedMemberId,
            exactMemberId,
            raceMemberAId,
            raceMemberBId,
          ].filter(Boolean),
        },
      },
    });
    await prisma.user.deleteMany({
      where: {
        id: {
          in: [
            userId,
            lowPointUserId,
            suspendedUserId,
            exactUserId,
            raceUserAId,
            raceUserBId,
          ].filter(Boolean),
        },
      },
    });
    await prisma.branch.deleteMany({
      where: { id: { in: [branchId, otherBranchId].filter(Boolean) } },
    });
  });

  test('happy path: creates token, decrements points, increments heldStock', async () => {
    const memberBefore = await prisma.member.findUniqueOrThrow({
      where: { id: memberId },
    });
    const stockBefore = await prisma.rewardBranchStock.findUniqueOrThrow({
      where: { id: stockId },
    });

    const result = await redeemService.createRedeemToken(memberId, {
      rewardId,
      branchId,
    });
    tokenIds.push(result.redeemId);

    expect(result.tokenCode).toMatch(/^[A-Z0-9]{10}$/);
    expect(result.heldPoints).toBe(POINTS_REQUIRED);
    expect(result.isUsed).toBe(false);
    expect(result.reward.sku).toBe(`RD-ACT-${suffix}`);
    expect(result.branch.id).toBe(branchId);
    expect(new Date(result.expiresAt).getTime()).toBeGreaterThan(Date.now());

    const memberAfter = await prisma.member.findUniqueOrThrow({
      where: { id: memberId },
    });
    const stockAfter = await prisma.rewardBranchStock.findUniqueOrThrow({
      where: { id: stockId },
    });

    expect(memberAfter.pointBalance).toBe(
      memberBefore.pointBalance - POINTS_REQUIRED,
    );
    expect(stockAfter.heldStock).toBe(stockBefore.heldStock + 1);
  });

  test('insufficient points → INSUFFICIENT_POINTS', async () => {
    try {
      await redeemService.createRedeemToken(lowPointMemberId, {
        rewardId,
        branchId,
      });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(RedeemError);
      expect((error as RedeemError).code).toBe('INSUFFICIENT_POINTS');
    }
  });

  test('exact boundary balance == pointsRequired succeeds → balance 0', async () => {
    const result = await redeemService.createRedeemToken(exactMemberId, {
      rewardId,
      branchId,
    });
    tokenIds.push(result.redeemId);

    const member = await prisma.member.findUniqueOrThrow({
      where: { id: exactMemberId },
    });
    expect(member.pointBalance).toBe(0);
  });

  test('stock unavailable → STOCK_UNAVAILABLE', async () => {
    const stock = await prisma.rewardBranchStock.findUniqueOrThrow({
      where: { id: stockId },
    });
    await prisma.rewardBranchStock.update({
      where: { id: stockId },
      data: { heldStock: stock.actualStock },
    });

    try {
      await redeemService.createRedeemToken(memberId, {
        rewardId,
        branchId,
      });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(RedeemError);
      expect((error as RedeemError).code).toBe('STOCK_UNAVAILABLE');
    } finally {
      await prisma.rewardBranchStock.update({
        where: { id: stockId },
        data: { heldStock: stock.heldStock },
      });
    }
  });

  test('suspended member → MEMBER_SUSPENDED', async () => {
    try {
      await redeemService.createRedeemToken(suspendedMemberId, {
        rewardId,
        branchId,
      });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(RedeemError);
      expect((error as RedeemError).code).toBe('MEMBER_SUSPENDED');
    }
  });

  test('inactive reward → REWARD_NOT_ACTIVE', async () => {
    try {
      await redeemService.createRedeemToken(memberId, {
        rewardId: inactiveRewardId,
        branchId,
      });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(RedeemError);
      expect((error as RedeemError).code).toBe('REWARD_NOT_ACTIVE');
    }
  });

  test('reward ended period → REWARD_NOT_ACTIVE', async () => {
    try {
      await redeemService.createRedeemToken(memberId, {
        rewardId: expiredPeriodRewardId,
        branchId,
      });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(RedeemError);
      expect((error as RedeemError).code).toBe('REWARD_NOT_ACTIVE');
    }
  });

  test('reward future period → REWARD_NOT_ACTIVE', async () => {
    try {
      await redeemService.createRedeemToken(memberId, {
        rewardId: futurePeriodRewardId,
        branchId,
      });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(RedeemError);
      expect((error as RedeemError).code).toBe('REWARD_NOT_ACTIVE');
    }
  });

  test('unknown reward → REWARD_NOT_FOUND', async () => {
    try {
      await redeemService.createRedeemToken(memberId, {
        rewardId: '00000000-0000-4000-8000-000000000099',
        branchId,
      });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(RedeemError);
      expect((error as RedeemError).code).toBe('REWARD_NOT_FOUND');
    }
  });

  test('no stock row for branch → STOCK_NOT_FOUND', async () => {
    try {
      await redeemService.createRedeemToken(memberId, {
        rewardId,
        branchId: otherBranchId,
      });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(RedeemError);
      expect((error as RedeemError).code).toBe('STOCK_NOT_FOUND');
    }
  });

  test('last unit sequential: first OK, second STOCK_UNAVAILABLE', async () => {
    await prisma.rewardBranchStock.update({
      where: { id: lastUnitStockId },
      data: { actualStock: 1, heldStock: 0 },
    });

    // Restore race members points in case prior test touched them
    await prisma.member.update({
      where: { id: raceMemberAId },
      data: { pointBalance: 5000 },
    });

    const first = await redeemService.createRedeemToken(raceMemberAId, {
      rewardId: lastUnitRewardId,
      branchId,
    });
    tokenIds.push(first.redeemId);

    try {
      await redeemService.createRedeemToken(raceMemberBId, {
        rewardId: lastUnitRewardId,
        branchId,
      });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(RedeemError);
      expect((error as RedeemError).code).toBe('STOCK_UNAVAILABLE');
    }

    const stock = await prisma.rewardBranchStock.findUniqueOrThrow({
      where: { id: lastUnitStockId },
    });
    expect(stock.heldStock).toBe(1);
    expect(stock.actualStock).toBe(1);
  });

  test('concurrent last-unit race: exactly one wins', async () => {
    // Reset last-unit stock; release previous held if any by creating fresh state
    await prisma.redeemToken.deleteMany({
      where: { rewardId: lastUnitRewardId },
    });
    await prisma.rewardBranchStock.update({
      where: { id: lastUnitStockId },
      data: { actualStock: 1, heldStock: 0 },
    });
    await prisma.member.updateMany({
      where: { id: { in: [raceMemberAId, raceMemberBId] } },
      data: { pointBalance: 5000 },
    });

    const results = await Promise.allSettled([
      redeemService.createRedeemToken(raceMemberAId, {
        rewardId: lastUnitRewardId,
        branchId,
      }),
      redeemService.createRedeemToken(raceMemberBId, {
        rewardId: lastUnitRewardId,
        branchId,
      }),
    ]);

    const fulfilled = results.filter((r) => r.status === 'fulfilled');
    const rejected = results.filter((r) => r.status === 'rejected');

    expect(fulfilled.length).toBe(1);
    expect(rejected.length).toBe(1);

    if (fulfilled[0]?.status === 'fulfilled') {
      tokenIds.push(fulfilled[0].value.redeemId);
    }

    const fail = rejected[0];
    expect(fail?.status).toBe('rejected');
    if (fail?.status === 'rejected') {
      expect(fail.reason).toBeInstanceOf(RedeemError);
      expect((fail.reason as RedeemError).code).toBe('STOCK_UNAVAILABLE');
    }

    const stock = await prisma.rewardBranchStock.findUniqueOrThrow({
      where: { id: lastUnitStockId },
    });
    expect(stock.heldStock).toBe(1);
    expect(stock.actualStock).toBe(1);
    expect(stock.heldStock).toBeLessThanOrEqual(stock.actualStock);
  });

  test('getActiveRedeemToken returns active; null when only expired/used', async () => {
    // Isolasi: hapus semua token member ini dari test sebelumnya
    await prisma.redeemToken.deleteMany({ where: { memberId } });

    const active = await redeemService.createRedeemToken(memberId, {
      rewardId,
      branchId,
    });
    tokenIds.push(active.redeemId);

    const found = await redeemService.getActiveRedeemToken(memberId);
    expect(found).not.toBeNull();
    expect(found!.redeemId).toBe(active.redeemId);
    expect(found!.tokenCode).toBe(active.tokenCode);

    await prisma.redeemToken.update({
      where: { id: active.redeemId },
      data: { isUsed: true },
    });

    const afterUsed = await redeemService.getActiveRedeemToken(memberId);
    expect(afterUsed).toBeNull();

    const expiredOnly = await redeemService.createRedeemToken(memberId, {
      rewardId,
      branchId,
    });
    tokenIds.push(expiredOnly.redeemId);
    await prisma.redeemToken.update({
      where: { id: expiredOnly.redeemId },
      data: { expiredAt: new Date(Date.now() - 60_000) },
    });

    const afterExpired = await redeemService.getActiveRedeemToken(memberId);
    expect(afterExpired).toBeNull();
  });

  test('getRedeemHistoryById unknown id → null; bad cursor → HISTORY_NOT_FOUND', async () => {
    const missing = await redeemService.getRedeemHistoryById(
      memberId,
      '00000000-0000-4000-8000-000000000001',
    );
    expect(missing).toBeNull();

    const empty = await redeemService.getRedeemHistory(memberId, { limit: 5 });
    expect(Array.isArray(empty.data)).toBe(true);
    expect(empty.pagination.limit).toBe(5);

    try {
      await redeemService.getRedeemHistory(memberId, {
        cursor: 'not-valid-base64!!!',
        limit: 5,
      });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(RedeemError);
      expect((error as RedeemError).code).toBe('HISTORY_NOT_FOUND');
    }
  });
});
