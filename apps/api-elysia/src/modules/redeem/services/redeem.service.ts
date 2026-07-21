import { Prisma } from '../../../generated/prisma/client';
import { prisma } from '../../../db';
import type { IRedeemService } from '../interfaces/redeem.interface';
import {
  CreateRedeemTokenRequest,
  decodeCursor,
  encodeCursor,
  PaginationResponse,
  RedeemError,
  RedeemInvoiceData,
  RedeemTokenData,
  RedeemTokenStatusData,
} from '../types/redeem.types';

const TOKEN_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
const TOKEN_LENGTH = 10;
const TOKEN_GEN_MAX_ATTEMPTS = 20;

const tokenInclude = {
  reward: {
    include: {
      rewardImages: {
        include: { media: true },
        orderBy: { sortOrder: 'asc' as const },
        take: 1,
      },
    },
  },
  branch: true,
} as const;

const invoiceInclude = {
  reward: {
    include: {
      rewardImages: {
        include: { media: true },
        orderBy: { sortOrder: 'asc' as const },
        take: 1,
      },
    },
  },
  branch: true,
} as const;

type TokenWithRelations = {
  id: string;
  tokenCode: string;
  heldPoints: number;
  isUsed: boolean;
  expiredAt: Date;
  reward: {
    id: string;
    sku: string;
    name: string;
    rewardImages: Array<{ media: { fileUrl: string } }>;
  };
  branch: {
    id: number;
    name: string;
    address: string;
  };
};

type InvoiceWithRelations = {
  id: string;
  invoiceNumber: string;
  pointsRedeemed: number;
  status: 'COMPLETED' | 'REFUNDED';
  createdAt: Date;
  reward: {
    id: string;
    sku: string;
    name: string;
    rewardImages: Array<{ media: { fileUrl: string } }>;
  };
  branch: {
    id: number;
    name: string;
    address: string;
  };
};

function mapToken(token: TokenWithRelations): RedeemTokenData {
  return {
    redeemId: token.id,
    tokenCode: token.tokenCode,
    heldPoints: token.heldPoints,
    isUsed: token.isUsed,
    expiresAt: token.expiredAt.toISOString(),
    reward: {
      id: token.reward.id,
      sku: token.reward.sku,
      name: token.reward.name,
      imageUrl: token.reward.rewardImages[0]?.media.fileUrl ?? null,
    },
    branch: {
      id: token.branch.id,
      name: token.branch.name,
      address: token.branch.address,
    },
  };
}

function mapInvoice(invoice: InvoiceWithRelations): RedeemInvoiceData {
  return {
    id: invoice.id,
    invoiceNumber: invoice.invoiceNumber,
    pointsRedeemed: invoice.pointsRedeemed,
    status: invoice.status,
    createdAt: invoice.createdAt.toISOString(),
    reward: {
      id: invoice.reward.id,
      sku: invoice.reward.sku,
      name: invoice.reward.name,
      imageUrl: invoice.reward.rewardImages[0]?.media.fileUrl ?? null,
    },
    branch: {
      id: invoice.branch.id,
      name: invoice.branch.name,
      address: invoice.branch.address,
    },
  };
}

function randomTokenCode(): string {
  const bytes = crypto.getRandomValues(new Uint8Array(TOKEN_LENGTH));
  let code = '';
  for (let i = 0; i < TOKEN_LENGTH; i++) {
    code += TOKEN_ALPHABET[bytes[i]! % TOKEN_ALPHABET.length]!;
  }
  return code;
}

async function generateUniqueTokenCode(): Promise<string> {
  for (let attempt = 0; attempt < TOKEN_GEN_MAX_ATTEMPTS; attempt++) {
    const tokenCode = randomTokenCode();
    const existing = await prisma.redeemToken.findUnique({
      where: { tokenCode },
      select: { id: true },
    });
    if (!existing) return tokenCode;
  }
  throw new Error('Gagal menghasilkan token redeem unik');
}

function tokenExpiryDate(): Date {
  const minutes = Number(process.env.REDEEM_TOKEN_EXPIRY_MINUTES ?? 30);
  return new Date(Date.now() + minutes * 60_000);
}

export class RedeemService implements IRedeemService {
  async createRedeemToken(
    memberId: string,
    req: CreateRedeemTokenRequest,
  ): Promise<RedeemTokenData> {
    const { rewardId, branchId } = req;
    const tokenCode = await generateUniqueTokenCode();

    const created = await prisma.$transaction(async (tx) => {
      const reward = await tx.reward.findUnique({ where: { id: rewardId } });
      if (!reward) {
        throw new RedeemError('REWARD_NOT_FOUND', 'Reward tidak ditemukan');
      }

      const now = new Date();
      if (!reward.isActive || now < reward.startAt || now > reward.endAt) {
        throw new RedeemError(
          'REWARD_NOT_ACTIVE',
          'Reward tidak aktif atau di luar periode',
        );
      }

      // FOR UPDATE agar race 2 member rebut stok terakhir tidak double-hold
      const lockedStock = await tx.$queryRaw<
        Array<{ id: string; actual_stock: number; held_stock: number }>
      >`
        SELECT id, actual_stock, held_stock
        FROM reward_branch_stocks
        WHERE reward_id = ${rewardId} AND branch_id = ${branchId}
        FOR UPDATE
      `;
      const stockRow = lockedStock[0];
      if (!stockRow) {
        throw new RedeemError(
          'STOCK_NOT_FOUND',
          'Stok reward di cabang tidak ditemukan',
        );
      }

      const available = stockRow.actual_stock - stockRow.held_stock;
      if (available < 1) {
        throw new RedeemError(
          'STOCK_UNAVAILABLE',
          'Stok reward tidak tersedia',
        );
      }

      const lockedMember = await tx.$queryRaw<
        Array<{
          id: string;
          is_suspended: number | boolean;
          point_balance: number;
        }>
      >`
        SELECT id, is_suspended, point_balance
        FROM members
        WHERE id = ${memberId}
        FOR UPDATE
      `;
      const memberRow = lockedMember[0];
      if (!memberRow) {
        throw new Error('Member tidak ditemukan');
      }
      if (Boolean(memberRow.is_suspended)) {
        throw new RedeemError(
          'MEMBER_SUSPENDED',
          'Akun Anda sedang disuspend. Hubungi admin untuk informasi lebih lanjut.',
        );
      }
      if (memberRow.point_balance < reward.pointsRequired) {
        throw new RedeemError(
          'INSUFFICIENT_POINTS',
          'Poin tidak mencukupi',
        );
      }

      const activeToken = await tx.redeemToken.findFirst({
        where: {
          memberId,
          isUsed: false,
          releasedAt: null,
          expiredAt: { gt: now },
        },
        select: { id: true },
      });
      if (activeToken) {
        throw new RedeemError(
          'TOKEN_ALREADY_ACTIVE',
          'Anda masih punya klaim reward aktif. Batalkan atau selesaikan dulu.',
        );
      }

      await tx.member.update({
        where: { id: memberId },
        data: { pointBalance: { decrement: reward.pointsRequired } },
      });

      await tx.rewardBranchStock.update({
        where: { id: stockRow.id },
        data: { heldStock: { increment: 1 } },
      });

      const token = await tx.redeemToken.create({
        data: {
          memberId,
          rewardId,
          branchId,
          tokenCode,
          heldPoints: reward.pointsRequired,
          isUsed: false,
          expiredAt: tokenExpiryDate(),
        },
        include: tokenInclude,
      });

      return token;
    }, {
      isolationLevel: Prisma.TransactionIsolationLevel.ReadCommitted,
    });

    return mapToken(created as TokenWithRelations);
  }

  async getActiveRedeemToken(memberId: string): Promise<RedeemTokenData | null> {
    const token = await prisma.redeemToken.findFirst({
      where: {
        memberId,
        isUsed: false,
        releasedAt: null,
        expiredAt: { gt: new Date() },
      },
      orderBy: { createdAt: 'desc' },
      include: tokenInclude,
    });

    return token ? mapToken(token as TokenWithRelations) : null;
  }

  async cancelRedeemToken(memberId: string, redeemId: string): Promise<void> {
    await prisma.$transaction(async (tx) => {
      const locked = await tx.$queryRaw<
        Array<{
          id: string;
          member_id: string;
          reward_id: string;
          branch_id: number | bigint;
          held_points: number | bigint;
          is_used: number | boolean;
          expired_at: Date;
          released_at: Date | null;
        }>
      >`
        SELECT id, member_id, reward_id, branch_id, held_points, is_used, expired_at, released_at
        FROM redeem_tokens
        WHERE id = ${redeemId} AND member_id = ${memberId}
        FOR UPDATE
      `;
      const row = locked[0];
      if (!row) {
        throw new RedeemError('TOKEN_NOT_FOUND', 'Token redeem tidak ditemukan');
      }
      if (Boolean(row.is_used)) {
        throw new RedeemError(
          'TOKEN_ALREADY_USED',
          'Token redeem sudah dikonfirmasi kasir',
        );
      }
      if (row.released_at != null) {
        throw new RedeemError(
          'TOKEN_ALREADY_RELEASED',
          'Token redeem sudah dibatalkan',
        );
      }
      if (new Date(row.expired_at).getTime() <= Date.now()) {
        throw new RedeemError('TOKEN_EXPIRED', 'Token redeem sudah kedaluwarsa');
      }

      const heldPoints = Number(row.held_points);
      const branchId = Number(row.branch_id);

      await tx.member.update({
        where: { id: memberId },
        data: { pointBalance: { increment: heldPoints } },
      });

      const stock = await tx.rewardBranchStock.findFirst({
        where: { rewardId: row.reward_id, branchId },
      });
      if (stock && stock.heldStock > 0) {
        await tx.rewardBranchStock.update({
          where: { id: stock.id },
          data: { heldStock: { decrement: 1 } },
        });
      }

      await tx.redeemToken.update({
        where: { id: redeemId },
        data: { releasedAt: new Date() },
      });
    });
  }

  async getRedeemTokenStatus(
    memberId: string,
    redeemId: string,
  ): Promise<RedeemTokenStatusData> {
    const token = await prisma.redeemToken.findFirst({
      where: { id: redeemId, memberId },
      select: {
        id: true,
        rewardId: true,
        isUsed: true,
        releasedAt: true,
        expiredAt: true,
        createdAt: true,
      },
    });

    if (!token) {
      throw new RedeemError('TOKEN_NOT_FOUND', 'Token redeem tidak ditemukan');
    }

    if (token.isUsed) {
      const invoice = await prisma.redeemInvoice.findFirst({
        where: {
          redeemTokenId: token.id,
          memberId,
        },
        select: { id: true },
      });
      return { status: 'completed', invoiceId: invoice?.id };
    }

    if (token.releasedAt != null) {
      return { status: 'released' };
    }

    if (token.expiredAt.getTime() <= Date.now()) {
      return { status: 'expired' };
    }

    return { status: 'active' };
  }

  async getRedeemHistory(
    memberId: string,
    params: { cursor?: string; limit?: number },
  ): Promise<PaginationResponse<RedeemInvoiceData>> {
    const limit = Math.min(Math.max(params.limit ?? 10, 1), 50);

    const where: {
      memberId: string;
      OR?: Array<Record<string, unknown>>;
    } = { memberId };

    if (params.cursor) {
      const decoded = decodeCursor(params.cursor);
      if (!decoded) {
        throw new RedeemError('HISTORY_NOT_FOUND', 'Cursor tidak valid');
      }

      const cursorCreatedAt = decoded.createdAt
        ? new Date(decoded.createdAt)
        : undefined;

      if (cursorCreatedAt && !Number.isNaN(cursorCreatedAt.getTime())) {
        where.OR = [
          { createdAt: { lt: cursorCreatedAt } },
          {
            AND: [
              { createdAt: cursorCreatedAt },
              { id: { lt: decoded.id } },
            ],
          },
        ];
      } else {
        where.OR = [{ id: { lt: decoded.id } }];
      }
    }

    const invoices = await prisma.redeemInvoice.findMany({
      where,
      take: limit + 1,
      orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
      include: invoiceInclude,
    });

    const hasMore = invoices.length > limit;
    const data = invoices.slice(0, limit);

    let nextCursor: string | null = null;
    if (hasMore && data.length > 0) {
      const last = data[data.length - 1]!;
      nextCursor = encodeCursor({
        id: last.id,
        createdAt: last.createdAt.toISOString(),
      });
    }

    return {
      data: data.map((row) => mapInvoice(row as InvoiceWithRelations)),
      pagination: {
        nextCursor,
        hasMore,
        limit,
      },
    };
  }

  async getRedeemHistoryById(
    memberId: string,
    id: string,
  ): Promise<RedeemInvoiceData | null> {
    const invoice = await prisma.redeemInvoice.findFirst({
      where: { id, memberId },
      include: invoiceInclude,
    });

    return invoice ? mapInvoice(invoice as InvoiceWithRelations) : null;
  }
}

export const redeemService = new RedeemService();
