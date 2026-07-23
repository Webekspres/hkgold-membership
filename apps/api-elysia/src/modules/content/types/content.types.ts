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
  locationAddress: string | null;
  locationUrl: string | null;
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
  /** Search title; apply only when length > 2 */
  q?: string;
  /** ISO date YYYY-MM-DD or full ISO */
  dateFrom?: string;
  dateTo?: string;
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
