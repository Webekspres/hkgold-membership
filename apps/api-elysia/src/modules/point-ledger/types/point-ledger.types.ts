export interface PointMutationItem {
  id: string;
  transactionDate: string;
  type: string;
  pointsIssued: number;
  pointsRedeemed: number;
  balanceAfter: number;
  branch?: {
    id: number;
    name: string;
  };
}

export interface PaginationResponse<T> {
  data: T[];
  pagination: {
    nextCursor: string | null;
    hasMore: boolean;
    limit: number;
  };
}

export type PointLedgerCursorPayload = {
  id: string;
  transactionDate?: string;
};

export function encodeCursor(data: PointLedgerCursorPayload): string {
  return Buffer.from(JSON.stringify(data)).toString('base64');
}

export function decodeCursor(cursor: string): PointLedgerCursorPayload | null {
  try {
    const decoded = Buffer.from(cursor, 'base64').toString('utf8');
    const parsed = JSON.parse(decoded);
    if (!parsed?.id) return null;
    return parsed as PointLedgerCursorPayload;
  } catch {
    return null;
  }
}
