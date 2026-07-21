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
          memberNumber: `RD-${suffix}-${seq}`,
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

  test('cancelRedeemToken refunds points and held stock', async () => {
    await prisma.redeemToken.deleteMany({ where: { memberId } });
    await prisma.rewardBranchStock.update({
      where: { id: stockId },
      data: { heldStock: 0 },
    });
    await prisma.member.update({
      where: { id: memberId },
      data: { pointBalance: 5000 },
    });

    const created = await redeemService.createRedeemToken(memberId, {
      rewardId,
      branchId,
    });
    tokenIds.push(created.redeemId);

    const beforeCancel = await prisma.member.findUniqueOrThrow({
      where: { id: memberId },
    });
    expect(beforeCancel.pointBalance).toBe(5000 - POINTS_REQUIRED);

    const stockBefore = await prisma.rewardBranchStock.findUniqueOrThrow({
      where: { id: stockId },
    });
    expect(stockBefore.heldStock).toBe(1);

    await redeemService.cancelRedeemToken(memberId, created.redeemId);

    const afterMember = await prisma.member.findUniqueOrThrow({
      where: { id: memberId },
    });
    expect(afterMember.pointBalance).toBe(5000);

    const stockAfter = await prisma.rewardBranchStock.findUniqueOrThrow({
      where: { id: stockId },
    });
    expect(stockAfter.heldStock).toBe(0);

    const token = await prisma.redeemToken.findUniqueOrThrow({
      where: { id: created.redeemId },
    });
    expect(token.releasedAt).not.toBeNull();
    expect(token.isUsed).toBe(false);

    const active = await redeemService.getActiveRedeemToken(memberId);
    expect(active).toBeNull();

    const status = await redeemService.getRedeemTokenStatus(
      memberId,
      created.redeemId,
    );
    expect(status.status).toBe('released');
  });

  test('cancelRedeemToken other member → TOKEN_NOT_FOUND', async () => {
    await prisma.redeemToken.deleteMany({ where: { memberId } });
    await prisma.rewardBranchStock.update({
      where: { id: stockId },
      data: { heldStock: 0 },
    });
    await prisma.member.update({
      where: { id: memberId },
      data: { pointBalance: 5000 },
    });

    const created = await redeemService.createRedeemToken(memberId, {
      rewardId,
      branchId,
    });
    tokenIds.push(created.redeemId);

    try {
      await redeemService.cancelRedeemToken(raceMemberAId, created.redeemId);
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(RedeemError);
      expect((error as RedeemError).code).toBe('TOKEN_NOT_FOUND');
    }

    await redeemService.cancelRedeemToken(memberId, created.redeemId);
  });

  test('cancelRedeemToken used / released / expired → error codes', async () => {
    await prisma.redeemToken.deleteMany({ where: { memberId } });
    await prisma.rewardBranchStock.update({
      where: { id: stockId },
      data: { heldStock: 0 },
    });
    await prisma.member.update({
      where: { id: memberId },
      data: { pointBalance: 5000 },
    });

    const used = await redeemService.createRedeemToken(memberId, {
      rewardId,
      branchId,
    });
    tokenIds.push(used.redeemId);
    await prisma.redeemToken.update({
      where: { id: used.redeemId },
      data: { isUsed: true },
    });
    // used path holds stock without release — reset held so next creates work
    await prisma.rewardBranchStock.update({
      where: { id: stockId },
      data: { heldStock: 0 },
    });
    try {
      await redeemService.cancelRedeemToken(memberId, used.redeemId);
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(RedeemError);
      expect((error as RedeemError).code).toBe('TOKEN_ALREADY_USED');
    }

    const released = await redeemService.createRedeemToken(memberId, {
      rewardId,
      branchId,
    });
    tokenIds.push(released.redeemId);
    await redeemService.cancelRedeemToken(memberId, released.redeemId);
    try {
      await redeemService.cancelRedeemToken(memberId, released.redeemId);
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(RedeemError);
      expect((error as RedeemError).code).toBe('TOKEN_ALREADY_RELEASED');
    }

    const expired = await redeemService.createRedeemToken(memberId, {
      rewardId,
      branchId,
    });
    tokenIds.push(expired.redeemId);
    await prisma.redeemToken.update({
      where: { id: expired.redeemId },
      data: { expiredAt: new Date(Date.now() - 60_000) },
    });
    try {
      await redeemService.cancelRedeemToken(memberId, expired.redeemId);
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(RedeemError);
      expect((error as RedeemError).code).toBe('TOKEN_EXPIRED');
    }

    const statusExpired = await redeemService.getRedeemTokenStatus(
      memberId,
      expired.redeemId,
    );
    expect(statusExpired.status).toBe('expired');

    const statusUsed = await redeemService.getRedeemTokenStatus(
      memberId,
      used.redeemId,
    );
    expect(statusUsed.status).toBe('completed');
  });

  test('second create while active → TOKEN_ALREADY_ACTIVE; after cancel → OK', async () => {
    await prisma.redeemToken.deleteMany({ where: { memberId } });
    await prisma.rewardBranchStock.update({
      where: { id: stockId },
      data: { heldStock: 0 },
    });
    await prisma.member.update({
      where: { id: memberId },
      data: { pointBalance: 5000 },
    });

    const first = await redeemService.createRedeemToken(memberId, {
      rewardId,
      branchId,
    });
    tokenIds.push(first.redeemId);

    try {
      await redeemService.createRedeemToken(memberId, {
        rewardId,
        branchId,
      });
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(RedeemError);
      expect((error as RedeemError).code).toBe('TOKEN_ALREADY_ACTIVE');
    }

    await redeemService.cancelRedeemToken(memberId, first.redeemId);

    const second = await redeemService.createRedeemToken(memberId, {
      rewardId,
      branchId,
    });
    tokenIds.push(second.redeemId);
    expect(second.redeemId).not.toBe(first.redeemId);
  });

  test('concurrent createRedeemToken same member → one success, one TOKEN_ALREADY_ACTIVE', async () => {
    await prisma.redeemToken.deleteMany({ where: { memberId } });
    await prisma.rewardBranchStock.update({
      where: { id: stockId },
      data: { heldStock: 0 },
    });
    await prisma.member.update({
      where: { id: memberId },
      data: { pointBalance: 5000 },
    });

    const results = await Promise.allSettled([
      redeemService.createRedeemToken(memberId, { rewardId, branchId }),
      redeemService.createRedeemToken(memberId, { rewardId, branchId }),
    ]);

    const fulfilled = results.filter((r) => r.status === 'fulfilled');
    const rejected = results.filter((r) => r.status === 'rejected');

    expect(fulfilled.length).toBe(1);
    expect(rejected.length).toBe(1);
    expect((rejected[0] as PromiseRejectedResult).reason).toBeInstanceOf(RedeemError);
    expect((rejected[0] as PromiseRejectedResult).reason.code).toBe('TOKEN_ALREADY_ACTIVE');

    tokenIds.push((fulfilled[0] as PromiseFulfilledResult<{ redeemId: string }>).value.redeemId);

    await redeemService.cancelRedeemToken(
      memberId,
      (fulfilled[0] as PromiseFulfilledResult<{ redeemId: string }>).value.redeemId,
    );
  });

  test('concurrent cancelRedeemToken same id → exactly one success', async () => {
    await prisma.redeemToken.deleteMany({ where: { memberId } });
    await prisma.rewardBranchStock.update({
      where: { id: stockId },
      data: { heldStock: 0 },
    });
    await prisma.member.update({
      where: { id: memberId },
      data: { pointBalance: 5000 },
    });

    const created = await redeemService.createRedeemToken(memberId, {
      rewardId,
      branchId,
    });
    tokenIds.push(created.redeemId);

    const beforeMember = await prisma.member.findUniqueOrThrow({
      where: { id: memberId },
    });
    const beforeStock = await prisma.rewardBranchStock.findUniqueOrThrow({
      where: { id: stockId },
    });

    const results = await Promise.allSettled([
      redeemService.cancelRedeemToken(memberId, created.redeemId),
      redeemService.cancelRedeemToken(memberId, created.redeemId),
    ]);

    const fulfilled = results.filter((r) => r.status === 'fulfilled');
    const rejected = results.filter((r) => r.status === 'rejected');

    expect(fulfilled.length).toBe(1);
    expect(rejected.length).toBe(1);
    expect((rejected[0] as PromiseRejectedResult).reason).toBeInstanceOf(RedeemError);
    expect((rejected[0] as PromiseRejectedResult).reason.code).toBe('TOKEN_ALREADY_RELEASED');

    const afterMember = await prisma.member.findUniqueOrThrow({
      where: { id: memberId },
    });
    const afterStock = await prisma.rewardBranchStock.findUniqueOrThrow({
      where: { id: stockId },
    });

    expect(afterMember.pointBalance).toBe(beforeMember.pointBalance + POINTS_REQUIRED);
    expect(afterStock.heldStock).toBe(beforeStock.heldStock - 1);
  });

  test('getRedeemTokenStatus used + invoice FK → completed with invoiceId', async () => {
    await prisma.redeemToken.deleteMany({ where: { memberId } });
    await prisma.rewardBranchStock.update({
      where: { id: stockId },
      data: { heldStock: 0 },
    });
    await prisma.member.update({
      where: { id: memberId },
      data: { pointBalance: 5000 },
    });

    const created = await redeemService.createRedeemToken(memberId, {
      rewardId,
      branchId,
    });
    tokenIds.push(created.redeemId);

    await prisma.redeemToken.update({
      where: { id: created.redeemId },
      data: { isUsed: true },
    });

    const staffUser = await prisma.user.create({
      data: {
        email: `redeem-svc-staff-${suffix}@example.com`,
        password: 'hashed',
        fullName: 'Redeem Svc Staff',
        role: 'ADMINISTRATOR',
        isActive: true,
      },
    });
    const staff = await prisma.staff.create({
      data: {
        userId: staffUser.id,
        branchId,
        employeeCode: `RDSV${suffix}`,
      },
    });

    const invoice = await prisma.redeemInvoice.create({
      data: {
        invoiceNumber: `RD-SVC-${suffix}`,
        memberId,
        staffId: staff.id,
        branchId,
        rewardId,
        redeemTokenId: created.redeemId,
        pointsRedeemed: POINTS_REQUIRED,
        status: 'COMPLETED',
      },
    });

    const status = await redeemService.getRedeemTokenStatus(memberId, created.redeemId);
    expect(status.status).toBe('completed');
    expect(status.invoiceId).toBe(invoice.id);

    await prisma.redeemInvoice.delete({ where: { id: invoice.id } });
    await prisma.staff.delete({ where: { id: staff.id } });
    await prisma.user.delete({ where: { id: staffUser.id } });
  });

  test('getRedeemTokenStatus used without invoice FK → completed without invoiceId', async () => {
    await prisma.redeemToken.deleteMany({ where: { memberId } });
    await prisma.rewardBranchStock.update({
      where: { id: stockId },
      data: { heldStock: 0 },
    });
    await prisma.member.update({
      where: { id: memberId },
      data: { pointBalance: 5000 },
    });

    const created = await redeemService.createRedeemToken(memberId, {
      rewardId,
      branchId,
    });
    tokenIds.push(created.redeemId);

    await prisma.redeemToken.update({
      where: { id: created.redeemId },
      data: { isUsed: true },
    });
    await prisma.rewardBranchStock.update({
      where: { id: stockId },
      data: { heldStock: 0 },
    });

    const status = await redeemService.getRedeemTokenStatus(memberId, created.redeemId);
    expect(status.status).toBe('completed');
    expect(status.invoiceId).toBeUndefined();
  });
});
