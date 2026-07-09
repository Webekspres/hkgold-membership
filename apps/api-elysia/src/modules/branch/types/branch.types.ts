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
  images: BranchImageData[];
  createdAt: Date;
  updatedAt: Date;
}

export type BranchListItemData = BranchDetailData;

export interface GetBranchesParams extends CursorPaginationParams {}

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
