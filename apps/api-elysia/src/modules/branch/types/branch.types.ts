import { CursorPaginationParams, PaginatedResponse } from '../../../shared/types/pagination.types';

export interface BranchImageData {
  id: string;
  fileUrl: string;
  sortOrder: number;
}

export interface BranchDetailData {
  id: number;
  branchCode: string;
  name: string;
  address: string;
  phone: string | null;
  locationUrl: string | null;
  isOnlineWarehouse: boolean;
  city: string;
  subdistrict: string;
  images: BranchImageData[];
  createdAt: Date;
  updatedAt: Date;
}

export interface BranchNearestData extends BranchDetailData {
  distanceKm: number;
}

export type BranchListItemData = BranchDetailData;

export interface BranchCityOption {
  id: number;
  name: string;
}

export interface GetBranchesParams extends CursorPaginationParams {
  /** Search name/address; apply when length > 2 */
  q?: string;
  /** Filter by city name (normalized address) */
  city?: string;
}

export type BranchDetailResponse = {
  success: boolean;
  message: string;
  data: BranchDetailData;
};

export type BranchListResponse = {
  success: boolean;
  message: string;
  data: PaginatedResponse<BranchListItemData>;
};
