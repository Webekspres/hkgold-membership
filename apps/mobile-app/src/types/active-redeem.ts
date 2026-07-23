export type ActiveRedeemReward = {
  id: string;
  sku: string;
  name: string;
  imageUrl: string | null;
};

export type ActiveRedeemBranch = {
  id: number;
  name: string;
  address: string;
};

/** Mirror API `RedeemTokenData` */
export type ActiveRedeemItem = {
  redeemId: string;
  tokenCode: string;
  heldPoints: number;
  isUsed: boolean;
  expiresAt: string;
  reward: ActiveRedeemReward;
  branch: ActiveRedeemBranch;
};
