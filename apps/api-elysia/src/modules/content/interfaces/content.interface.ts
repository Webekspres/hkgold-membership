import { ContentDetailData, ContentListItemData, GetContentsParams } from '../types/content.types';
import { PaginatedResponse } from '../../../shared/types/pagination.types';

export interface IContentService {
  getById(id: string): Promise<ContentDetailData | null>;
  getAll(params: GetContentsParams): Promise<PaginatedResponse<ContentListItemData>>;
}
