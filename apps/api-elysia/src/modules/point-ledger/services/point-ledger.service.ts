import { prisma } from '../../../db';
import type { PointMutationItem, PaginationResponse } from '../types/point-ledger.types';
import { decodeCursor, encodeCursor } from '../types/point-ledger.types';

type GetPointLedgerParams = {
  cursor?: string;
  limit?: number;
  dateFrom?: string;
  dateTo?: string;
};

const mutationInclude = {
  branch: true,
  transactionType: true,
} as const;

type MutationWithRelations = {
  id: string;
  transactionDate: Date;
  pointsIssued: number;
  pointsRedeemed: number;
  balanceSnapshot: number;
  branch: {
    id: number;
    name: string;
  } | null;
  transactionType: {
    id: number;
    name: string;
  } | null;
};

function mapMutation(m: MutationWithRelations): PointMutationItem {
  return {
    id: m.id,
    transactionDate: m.transactionDate.toISOString(),
    type: m.transactionType?.name ?? 'Transaksi',
    pointsIssued: m.pointsIssued,
    pointsRedeemed: m.pointsRedeemed,
    balanceAfter: m.balanceSnapshot,
    branch: m.branch
      ? {
          id: m.branch.id,
          name: m.branch.name,
        }
      : undefined,
  };
}

class PointLedgerService {
  async getPointLedger(
    memberId: string,
    params: GetPointLedgerParams,
  ): Promise<PaginationResponse<PointMutationItem>> {
    const limit = Math.min(Math.max(params.limit ?? 20, 1), 50);

    const where: {
      memberId: string;
      transactionDate?: { gte?: Date; lte?: Date };
      OR?: Array<Record<string, unknown>>;
    } = { memberId };

    // Date filter
    if (params.dateFrom) {
      const fromDate = new Date(params.dateFrom);
      if (!Number.isNaN(fromDate.getTime())) {
        where.transactionDate = { ...where.transactionDate, gte: fromDate };
      }
    }
    if (params.dateTo) {
      const toDate = new Date(params.dateTo);
      if (!Number.isNaN(toDate.getTime())) {
        // Include the entire day
        toDate.setHours(23, 59, 59, 999);
        where.transactionDate = { ...where.transactionDate, lte: toDate };
      }
    }

    // Cursor pagination
    if (params.cursor) {
      const decoded = decodeCursor(params.cursor);
      if (decoded) {
        const cursorDate = decoded.transactionDate
          ? new Date(decoded.transactionDate)
          : undefined;

        if (cursorDate && !Number.isNaN(cursorDate.getTime())) {
          where.OR = [
            { transactionDate: { lt: cursorDate } },
            {
              AND: [
                { transactionDate: cursorDate },
                { id: { lt: decoded.id } },
              ],
            },
          ];
        } else {
          where.OR = [{ id: { lt: decoded.id } }];
        }
      }
    }

    const mutations = await prisma.pointMutation.findMany({
      where,
      take: limit + 1,
      orderBy: [{ transactionDate: 'desc' }, { id: 'desc' }],
      include: mutationInclude,
    });

    const hasMore = mutations.length > limit;
    const data = mutations.slice(0, limit);

    let nextCursor: string | null = null;
    if (hasMore && data.length > 0) {
      const last = data[data.length - 1]!;
      nextCursor = encodeCursor({
        id: last.id,
        transactionDate: last.transactionDate.toISOString(),
      });
    }

    return {
      data: data.map((m) => mapMutation(m as MutationWithRelations)),
      pagination: {
        nextCursor,
        hasMore,
        limit,
      },
    };
  }
}

export const pointLedgerService = new PointLedgerService();
