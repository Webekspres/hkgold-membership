export type NewsArticle = {
  id: string;
  slug: string;
  title: string;
  publishedAt: string;
  publishedAtLabel: string;
  imageUrl: string | null;
};

export type NewsArticleDetail = NewsArticle & {
  bodyContent: string;
  imageUrls: string[];
};

/** Satu halaman dari GET /api/content (nested di envelope.data). */
export type ContentListPage = {
  data: ContentListItem[];
  pagination: {
    nextCursor: string | null;
    hasMore: boolean;
    limit: number;
  };
};

export type ContentListItem = {
  id: string;
  type: 'NEWS' | 'EVENT';
  title: string;
  slug: string;
  excerpt: string;
  eventDate: string | null;
  coverImages: { id: string; fileUrl: string; sortOrder: number }[];
  createdAt: string;
};

export type ContentDetailData = {
  id: string;
  type: 'NEWS' | 'EVENT';
  title: string;
  slug: string;
  bodyContent: string;
  eventDate: string | null;
  locationAddress?: string | null;
  locationUrl?: string | null;
  status: string;
  coverImages: { id: string; fileUrl: string; sortOrder: number }[];
  createdAt: string;
  updatedAt: string;
};

export type NewsPage = {
  items: NewsArticle[];
  nextCursor: string | null;
  hasMore: boolean;
};
