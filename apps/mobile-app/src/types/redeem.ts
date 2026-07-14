export type RedeemStatus = 'selesai' | 'diproses' | 'ditolak';

export type RedeemHistoryItem = {
  id: string;
  sku: string;
  name: string;
  categoryId: number;
  categoryName: string;
  categorySlug: string;
  pointsRequired: number;
  image: number;
  redeemedAt: string;
  branchName: string;
  status: RedeemStatus;
};
