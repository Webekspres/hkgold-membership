export type RedeemStatus = 'selesai' | 'ditolak';

export type RedeemApiStatus = 'COMPLETED' | 'REFUNDED';

export type RedeemHistoryReward = {
  id: string;
  sku: string;
  name: string;
  imageUrl: string | null;
};

export type RedeemHistoryBranch = {
  id: number;
  name: string;
  address: string;
};

export type RedeemHistoryItem = {
  id: string;
  invoiceNumber: string;
  pointsRedeemed: number;
  redeemedAt: string;
  status: RedeemStatus;
  reward: RedeemHistoryReward;
  branch: RedeemHistoryBranch;
};

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

export type RedeemTokenStatusKind = 'active' | 'completed' | 'released' | 'expired';

export type RedeemTokenStatus = {
  status: RedeemTokenStatusKind;
  invoiceId?: string;
};