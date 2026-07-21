export type PointMutationItem = {
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
};
