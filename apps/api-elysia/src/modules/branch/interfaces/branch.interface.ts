import {
  BranchCityOption,
  BranchDetailData,
  BranchListItemData,
  BranchNearestData,
  GetBranchesParams,
} from '../types/branch.types';
import { PaginatedResponse } from '../../../shared/types/pagination.types';

export interface IBranchService {
  getById(id: number): Promise<BranchDetailData | null>;
  getAll(params: GetBranchesParams): Promise<PaginatedResponse<BranchListItemData>>;
  getCities(): Promise<BranchCityOption[]>;
  getNearest(lat: number, lng: number): Promise<BranchNearestData | null>;
}
