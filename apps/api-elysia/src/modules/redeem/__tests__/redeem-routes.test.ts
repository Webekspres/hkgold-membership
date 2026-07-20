import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { Elysia } from 'elysia';
import { prisma } from '../../../db';
import { jwtService } from '../../auth/services/jwt.service';
import { redeemRoutes } from '../routes/redeem.routes';

const app = new Elysia().use(redeemRoutes);

describe('Redeem Module - HTTP routes', () => {
  const suffix = Date.now().toString().slice(-6);
  const now = new Date();
  const startAt = new Date(now.getTime() - 7 * 86400000);
  const endAt = new Date(now.getTime() + 30 * 86400000);
  const ymPrefix = `${now.getFullYear().toString().slice(-2)}${String(now.getMonth() + 1).padStart(2, '0')}`;
  const POINTS_REQUIRED = 1000;

  let branchId: number;
  let rewardId: string;
  let stockId: string;
  let userId: string;
  let memberId: string;
  let suspendedUserId: string;
  let suspendedMemberId: string;
  let otherUserId: string;
  let otherMemberId: string;
  let staffId: number;
  let staffUserId: string;
  let accessToken: string;
  let suspendedAccessToken: string;
  let otherAccessToken: string;

  const tokenIds: string[] = [];
  const invoiceIds: string[] = [];

  async function bearer(user: string, member: string, suspended = false) {
    return jwtService.generateAccessToken({
      userId: user,
      memberId: member,
      role: 'MEMBER',
      isActive: true,
      isSuspended: suspended,
    });
  }

  async function http(
    path: string,
    opts: {
      method?: string;
      token?: string;
      body?: unknown;
    } = {},
  ) {
    const headers: Record<string, string> = {};
    if (opts.token) headers.Authorization = `Bearer ${opts.token}`;
    if (opts.body !== undefined) headers['Content-Type'] = 'application/json';

    const res = await app.handle(
      new Request(`http://local${path}`, {
        method: opts.method ?? 'GET',
        headers,
        body: opts.body !== undefined ? JSON.stringify(opts.body) : undefined,
      }),
    );

    const json = await res.json();
    return { status: res.status, body: json };
  }

  beforeAll(async () => {
    const branch = await prisma.branch.create({
      data: {
        branchCode: `RH${suffix}`,
        name: `Redeem HTTP Branch ${suffix}`,
        address: 'Jl. Redeem HTTP 1',
        isOnlineWarehouse: false,
      },
    });
    branchId = branch.id;

    const category = await prisma.categoryReward.create({
      data: {
        name: `Redeem HTTP Cat ${suffix}`,
        slug: `redeem-http-cat-${suffix}`,
      },
    });

    const reward = await prisma.reward.create({
      data: {
        categoryId: category.id,
        name: `Redeem HTTP Reward ${suffix}`,
        sku: `RH-RWD-${suffix}`,
        description: 'reward for HTTP redeem tests',
        pointsRequired: POINTS_REQUIRED,
        isActive: true,
        startAt,
        endAt,
      },
    });
    rewardId = reward.id;

    const stock = await prisma.rewardBranchStock.create({
      data: {
        rewardId,
        branchId,
        actualStock: 20,
        heldStock: 0,
      },
    });
    stockId = stock.id;

    async function makeMember(tag: string, seq: string, balance: number, suspended = false) {
      const user = await prisma.user.create({
        data: {
          email: `redeem-http-${tag}-${suffix}@example.com`,
          password: 'hashed',
          fullName: `Redeem HTTP ${tag}`,
          role: 'MEMBER',
          isActive: true,
        },
      });
      const member = await prisma.member.create({
        data: {
          userId: user.id,
          memberNumber: `RH-${suffix}-${seq}`,
          phoneNumber: `0812${suffix}${seq}`,
          currentTier: 'SILVER',
          pointBalance: balance,
          highestPoint: balance,
          isSuspended: suspended,
        },
      });
      return { userId: user.id, memberId: member.id };
    }

    ({ userId, memberId } = await makeMember('ok', '9', 5000));
    ({ userId: suspendedUserId, memberId: suspendedMemberId } = await makeMember(
      'sus',
      '8',
      5000,
      true,
    ));
    ({ userId: otherUserId, memberId: otherMemberId } = await makeMember('oth', '7', 5000));

    const staffUser = await prisma.user.create({
      data: {
        email: `redeem-http-staff-${suffix}@example.com`,
        password: 'hashed',
        fullName: 'Redeem HTTP Staff',
        role: 'ADMINISTRATOR',
        isActive: true,
      },
    });
    staffUserId = staffUser.id;
    const staff = await prisma.staff.create({
      data: {
        userId: staffUser.id,
        branchId,
        employeeCode: `RHST${suffix}`,
      },
    });
    staffId = staff.id;

    accessToken = await bearer(userId, memberId);
    suspendedAccessToken = await bearer(suspendedUserId, suspendedMemberId, true);
    otherAccessToken = await bearer(otherUserId, otherMemberId);
  });

  afterAll(async () => {
    if (invoiceIds.length > 0) {
      await prisma.redeemInvoice.deleteMany({ where: { id: { in: invoiceIds } } });
    }
    if (tokenIds.length > 0) {
      await prisma.redeemToken.deleteMany({ where: { id: { in: tokenIds } } });
    }
    if (stockId) {
      await prisma.rewardBranchStock.deleteMany({ where: { id: stockId } });
    }
    if (rewardId) {
      await prisma.reward.deleteMany({ where: { id: rewardId } });
    }
    await prisma.categoryReward.deleteMany({
      where: { slug: `redeem-http-cat-${suffix}` },
    });
    if (staffId) {
      await prisma.redeemInvoice.deleteMany({ where: { staffId } });
      await prisma.staff.deleteMany({ where: { id: staffId } });
    }
    if (staffUserId) {
      await prisma.user.deleteMany({ where: { id: staffUserId } });
    }
    if (memberId || suspendedMemberId || otherMemberId) {
      await prisma.member.deleteMany({
        where: { id: { in: [memberId, suspendedMemberId, otherMemberId].filter(Boolean) } },
      });
    }
    if (userId || suspendedUserId || otherUserId) {
      await prisma.user.deleteMany({
        where: {
          id: { in: [userId, suspendedUserId, otherUserId].filter(Boolean) },
        },
      });
    }
    if (branchId) {
      await prisma.branch.deleteMany({ where: { id: branchId } });
    }
  });

  async function resetMemberState() {
    await prisma.redeemToken.deleteMany({ where: { memberId } });
    await prisma.rewardBranchStock.update({
      where: { id: stockId },
      data: { heldStock: 0 },
    });
    await prisma.member.update({
      where: { id: memberId },
      data: { pointBalance: 5000 },
    });
  }

  test('GET /active without Bearer → 401', async () => {
    const res = await http('/api/redeem/active');
    expect(res.status).toBe(401);
    expect(res.body.success).toBe(false);
  });

  test('happy path: create → active → status → cancel → status released', async () => {
    await resetMemberState();

    const create = await http('/api/redeem/token', {
      method: 'POST',
      token: accessToken,
      body: { rewardId, branchId },
    });
    expect(create.status).toBe(201);
    expect(create.body.success).toBe(true);
    expect(create.body.data.redeemId).toBeTruthy();
    expect(create.body.data.tokenCode).toBeTruthy();
    tokenIds.push(create.body.data.redeemId);

    const active = await http('/api/redeem/active', { token: accessToken });
    expect(active.status).toBe(200);
    expect(active.body.data.redeemId).toBe(create.body.data.redeemId);

    const statusActive = await http(
      `/api/redeem/token/${create.body.data.redeemId}/status`,
      { token: accessToken },
    );
    expect(statusActive.status).toBe(200);
    expect(statusActive.body.data.status).toBe('active');

    const cancel = await http('/api/redeem/cancel', {
      method: 'POST',
      token: accessToken,
      body: { redeemId: create.body.data.redeemId },
    });
    expect(cancel.status).toBe(200);
    expect(cancel.body.success).toBe(true);

    const activeAfter = await http('/api/redeem/active', { token: accessToken });
    expect(activeAfter.body.data).toBeNull();

    const statusReleased = await http(
      `/api/redeem/token/${create.body.data.redeemId}/status`,
      { token: accessToken },
    );
    expect(statusReleased.body.data.status).toBe('released');
  });

  test('used token + invoice FK → status completed with invoiceId', async () => {
    await resetMemberState();

    const create = await http('/api/redeem/token', {
      method: 'POST',
      token: accessToken,
      body: { rewardId, branchId },
    });
    const redeemId = create.body.data.redeemId as string;
    tokenIds.push(redeemId);

    await prisma.redeemToken.update({
      where: { id: redeemId },
      data: { isUsed: true },
    });

    const invoice = await prisma.redeemInvoice.create({
      data: {
        invoiceNumber: `RH-INV-${suffix}`,
        memberId,
        staffId,
        branchId,
        rewardId,
        redeemTokenId: redeemId,
        pointsRedeemed: POINTS_REQUIRED,
        status: 'COMPLETED',
      },
    });
    invoiceIds.push(invoice.id);

    const status = await http(`/api/redeem/token/${redeemId}/status`, {
      token: accessToken,
    });
    expect(status.status).toBe(200);
    expect(status.body.data.status).toBe('completed');
    expect(status.body.data.invoiceId).toBe(invoice.id);
  });

  test('suspended member: POST /token → 403; POST /cancel still OK', async () => {
    await prisma.redeemToken.deleteMany({ where: { memberId: suspendedMemberId } });
    await prisma.member.update({
      where: { id: suspendedMemberId },
      data: { pointBalance: 5000 },
    });

    const blocked = await http('/api/redeem/token', {
      method: 'POST',
      token: suspendedAccessToken,
      body: { rewardId, branchId },
    });
    expect(blocked.status).toBe(403);
    expect(blocked.body.success).toBe(false);

    await resetMemberState();
    const create = await http('/api/redeem/token', {
      method: 'POST',
      token: accessToken,
      body: { rewardId, branchId },
    });
    tokenIds.push(create.body.data.redeemId);

    await prisma.member.update({
      where: { id: memberId },
      data: { isSuspended: true },
    });
    const suspendedToken = await bearer(userId, memberId, true);

    const cancel = await http('/api/redeem/cancel', {
      method: 'POST',
      token: suspendedToken,
      body: { redeemId: create.body.data.redeemId },
    });
    expect(cancel.status).toBe(200);

    await prisma.member.update({
      where: { id: memberId },
      data: { isSuspended: false },
    });
    accessToken = await bearer(userId, memberId);
  });

  test('TOKEN_ALREADY_ACTIVE → 409', async () => {
    await resetMemberState();

    const first = await http('/api/redeem/token', {
      method: 'POST',
      token: accessToken,
      body: { rewardId, branchId },
    });
    tokenIds.push(first.body.data.redeemId);

    const second = await http('/api/redeem/token', {
      method: 'POST',
      token: accessToken,
      body: { rewardId, branchId },
    });
    expect(second.status).toBe(409);
    expect(second.body.error).toBe('TOKEN_ALREADY_ACTIVE');

    await http('/api/redeem/cancel', {
      method: 'POST',
      token: accessToken,
      body: { redeemId: first.body.data.redeemId },
    });
  });

  test('cancel unknown / other member token → 404', async () => {
    await resetMemberState();

    const create = await http('/api/redeem/token', {
      method: 'POST',
      token: accessToken,
      body: { rewardId, branchId },
    });
    tokenIds.push(create.body.data.redeemId);

    const otherMember = await http('/api/redeem/cancel', {
      method: 'POST',
      token: otherAccessToken,
      body: { redeemId: create.body.data.redeemId },
    });
    expect(otherMember.status).toBe(404);
    expect(otherMember.body.error).toBe('TOKEN_NOT_FOUND');

    const unknown = await http('/api/redeem/cancel', {
      method: 'POST',
      token: accessToken,
      body: { redeemId: '00000000-0000-4000-8000-000000000001' },
    });
    expect(unknown.status).toBe(404);

    await http('/api/redeem/cancel', {
      method: 'POST',
      token: accessToken,
      body: { redeemId: create.body.data.redeemId },
    });
  });

  test('cancel used / released / expired → 409', async () => {
    await resetMemberState();

    const used = await http('/api/redeem/token', {
      method: 'POST',
      token: accessToken,
      body: { rewardId, branchId },
    });
    tokenIds.push(used.body.data.redeemId);
    await prisma.redeemToken.update({
      where: { id: used.body.data.redeemId },
      data: { isUsed: true },
    });
    await prisma.rewardBranchStock.update({
      where: { id: stockId },
      data: { heldStock: 0 },
    });

    const usedRes = await http('/api/redeem/cancel', {
      method: 'POST',
      token: accessToken,
      body: { redeemId: used.body.data.redeemId },
    });
    expect(usedRes.status).toBe(409);
    expect(usedRes.body.error).toBe('TOKEN_ALREADY_USED');

    const released = await http('/api/redeem/token', {
      method: 'POST',
      token: accessToken,
      body: { rewardId, branchId },
    });
    tokenIds.push(released.body.data.redeemId);
    await http('/api/redeem/cancel', {
      method: 'POST',
      token: accessToken,
      body: { redeemId: released.body.data.redeemId },
    });

    const releasedRes = await http('/api/redeem/cancel', {
      method: 'POST',
      token: accessToken,
      body: { redeemId: released.body.data.redeemId },
    });
    expect(releasedRes.status).toBe(409);
    expect(releasedRes.body.error).toBe('TOKEN_ALREADY_RELEASED');

    const expired = await http('/api/redeem/token', {
      method: 'POST',
      token: accessToken,
      body: { rewardId, branchId },
    });
    tokenIds.push(expired.body.data.redeemId);
    await prisma.redeemToken.update({
      where: { id: expired.body.data.redeemId },
      data: { expiredAt: new Date(Date.now() - 60_000) },
    });

    const expiredRes = await http('/api/redeem/cancel', {
      method: 'POST',
      token: accessToken,
      body: { redeemId: expired.body.data.redeemId },
    });
    expect(expiredRes.status).toBe(409);
    expect(expiredRes.body.error).toBe('TOKEN_EXPIRED');
  });

  test('POST /cancel without redeemId → validation error', async () => {
    const res = await http('/api/redeem/cancel', {
      method: 'POST',
      token: accessToken,
      body: {},
    });
    expect(res.status).toBeGreaterThanOrEqual(400);
    expect(res.status).toBeLessThan(500);
  });
});
