import { CursorPaginationParams, PaginatedResponse } from '../../../shared/types/pagination.types';

export interface ContentCoverImageData {
  id: string;
  fileUrl: string;
  sortOrder: number;
}

export interface ContentDetailData {
  id: string;
  type: 'NEWS' | 'EVENT';
  title: string;
  slug: string;
  bodyContent: string;
  eventDate: Date | null;
  status: 'DRAFT' | 'ARCHIVED' | 'PUBLISHED';
  coverImages: ContentCoverImageData[];
  createdAt: Date;
  updatedAt: Date;
}

export interface ContentListItemData {
  id: string;
  type: 'NEWS' | 'EVENT';
  title: string;
  slug: string;
  excerpt: string;
  eventDate: Date | null;
  coverImages: ContentCoverImageData[];
  createdAt: Date;
}

export interface GetContentsParams extends CursorPaginationParams {
  type?: 'NEWS' | 'EVENT';
  includeArchived?: boolean;
}

export type ContentDetailResponse = {
  success: boolean;
  message: string;
  data: ContentDetailData;
};

export type ContentListResponse = {
  success: boolean;
  message: string;
  data: PaginatedResponse<ContentListItemData>;
};
