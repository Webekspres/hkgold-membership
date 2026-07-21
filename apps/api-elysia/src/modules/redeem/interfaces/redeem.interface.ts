import {
  CreateRedeemTokenRequest,
  PaginationResponse,
  RedeemInvoiceData,
  RedeemTokenData,
  RedeemTokenStatusData,
} from '../types/redeem.types';

export interface IRedeemService {
  createRedeemToken(
    memberId: string,
    req: CreateRedeemTokenRequest,
  ): Promise<RedeemTokenData>;

  getActiveRedeemToken(memberId: string): Promise<RedeemTokenData | null>;

  cancelRedeemToken(memberId: string, redeemId: string): Promise<void>;

  getRedeemTokenStatus(
    memberId: string,
    redeemId: string,
  ): Promise<RedeemTokenStatusData>;

  getRedeemHistory(
    memberId: string,
    params: { cursor?: string; limit?: number },
  ): Promise<PaginationResponse<RedeemInvoiceData>>;

  getRedeemHistoryById(
    memberId: string,
    id: string,
  ): Promise<RedeemInvoiceData | null>;
}
