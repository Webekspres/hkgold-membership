import {
  CreateRedeemTokenRequest,
  PaginationResponse,
  RedeemInvoiceData,
  RedeemTokenData,
} from '../types/redeem.types';

export interface IRedeemService {
  createRedeemToken(
    memberId: string,
    req: CreateRedeemTokenRequest,
  ): Promise<RedeemTokenData>;

  getActiveRedeemToken(memberId: string): Promise<RedeemTokenData | null>;

  getRedeemHistory(
    memberId: string,
    params: { cursor?: string; limit?: number },
  ): Promise<PaginationResponse<RedeemInvoiceData>>;

  getRedeemHistoryById(
    memberId: string,
    id: string,
  ): Promise<RedeemInvoiceData | null>;
}
