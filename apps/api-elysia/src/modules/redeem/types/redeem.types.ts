export interface CreateRedeemTokenRequest {
  rewardId: string;
  branchId: number;
}

export interface RedeemRewardSummary {
  id: string;
  sku: string;
  name: string;
  imageUrl: string | null;
}

export interface RedeemBranchSummary {
  id: number;
  name: string;
  address: string;
}

/** Mobile ↔ API contract for active/created redeem token */
export interface RedeemTokenData {
  redeemId: string;
  tokenCode: string;
  heldPoints: number;
  isUsed: boolean;
  expiresAt: string;
  reward: RedeemRewardSummary;
  branch: RedeemBranchSummary;
}

export interface RedeemInvoiceData {
  id: string;
  invoiceNumber: string;
  pointsRedeemed: number;
  status: 'COMPLETED' | 'REFUNDED';
  createdAt: string;
  reward: RedeemRewardSummary;
  branch: RedeemBranchSummary;
}

export type RedeemTokenStatusKind =
  | 'active'
  | 'completed'
  | 'released'
  | 'expired';

export interface RedeemTokenStatusData {
  status: RedeemTokenStatusKind;
  invoiceId?: string;
}

export type RedeemErrorCode =
  | 'REWARD_NOT_FOUND'
  | 'REWARD_NOT_ACTIVE'
  | 'STOCK_NOT_FOUND'
  | 'STOCK_UNAVAILABLE'
  | 'MEMBER_SUSPENDED'
  | 'INSUFFICIENT_POINTS'
  | 'TOKEN_NOT_FOUND'
  | 'TOKEN_ALREADY_USED'
  | 'TOKEN_ALREADY_RELEASED'
  | 'TOKEN_ALREADY_ACTIVE'
  | 'TOKEN_EXPIRED'
  | 'HISTORY_NOT_FOUND';

export class RedeemError extends Error {
  constructor(
    public readonly code: RedeemErrorCode,
    message: string,
  ) {
    super(message);
    this.name = 'RedeemError';
  }
}

export interface PaginationResponse<T> {
  data: T[];
  pagination: {
    nextCursor: string | null;
    hasMore: boolean;
    limit: number;
  };
}

export type RedeemHistoryCursorPayload = {
  id: string;
  createdAt?: string;
};

export function encodeCursor(data: RedeemHistoryCursorPayload): string {
  return Buffer.from(JSON.stringify(data)).toString('base64');
}

export function decodeCursor(cursor: string): RedeemHistoryCursorPayload | null {
  try {
    const decoded = Buffer.from(cursor, 'base64').toString('utf8');
    const parsed = JSON.parse(decoded);
    if (!parsed?.id) return null;
    return parsed as RedeemHistoryCursorPayload;
  } catch {
    return null;
  }
}
