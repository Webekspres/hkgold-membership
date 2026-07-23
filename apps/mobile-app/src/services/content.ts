import { apiClient } from '@/lib/api-client';
import { formatNewsDate } from '@/lib/format/format-news-date';
import type { ApiEnvelope } from '@/types/auth';
import type { EventDetail, EventItem, EventPage } from '@/types/event';
import type {
  ContentDetailData,
  ContentListItem,
  ContentListPage,
  NewsArticle,
  NewsArticleDetail,
  NewsPage,
} from '@/types/news';

export type ContentType = 'NEWS' | 'EVENT';

export type FetchContentPageParams = {
  type: ContentType;
  limit: number;
  cursor?: string;
  q?: string;
  dateFrom?: string;
  dateTo?: string;
};

function toIsoString(value: string | Date): string {
  return typeof value === 'string' ? value : value.toISOString();
}

export async function fetchContentPage(
  params: FetchContentPageParams,
): Promise<{ items: ContentListItem[]; nextCursor: string | null; hasMore: boolean }> {
  const { data } = await apiClient.get<ApiEnvelope<ContentListPage>>('/api/content', {
    params: {
      type: params.type,
      limit: params.limit,
      ...(params.cursor ? { cursor: params.cursor } : {}),
      ...(params.q ? { q: params.q } : {}),
      ...(params.dateFrom ? { dateFrom: params.dateFrom } : {}),
      ...(params.dateTo ? { dateTo: params.dateTo } : {}),
    },
  });

  if (!data.success || !data.data) {
    throw new Error(data.message || 'Gagal mengambil daftar konten');
  }

  const page = data.data;
  return {
    items: page.data,
    nextCursor: page.pagination.nextCursor,
    hasMore: page.pagination.hasMore,
  };
}

export async function fetchContentById(id: string): Promise<ContentDetailData> {
  const { data } = await apiClient.get<ApiEnvelope<ContentDetailData>>(`/api/content/${id}`);

  if (!data.success || !data.data) {
    throw new Error(data.message || 'Konten tidak ditemukan');
  }

  return data.data;
}

export function mapContentToNewsArticle(item: ContentListItem): NewsArticle {
  const cover = item.coverImages[0];
  const publishedAt = toIsoString(item.createdAt);

  return {
    id: item.id,
    slug: item.slug,
    title: item.title,
    publishedAt,
    publishedAtLabel: formatNewsDate(publishedAt),
    imageUrl: cover?.fileUrl ?? null,
  };
}

export function mapContentToNewsDetail(item: ContentDetailData): NewsArticleDetail {
  const publishedAt = toIsoString(item.createdAt);
  return {
    id: item.id,
    slug: item.slug,
    title: item.title,
    publishedAt,
    publishedAtLabel: formatNewsDate(publishedAt),
    imageUrl: item.coverImages[0]?.fileUrl ?? null,
    bodyContent: item.bodyContent,
    imageUrls: item.coverImages.map((img) => img.fileUrl),
  };
}

export function mapContentToEventItem(item: ContentListItem): EventItem {
  const cover = item.coverImages[0];
  const eventDate = item.eventDate ? toIsoString(item.eventDate) : publishedFallback(item);

  return {
    id: item.id,
    slug: item.slug,
    title: item.title,
    eventDate,
    imageUrl: cover?.fileUrl ?? null,
  };
}

function publishedFallback(item: ContentListItem): string {
  return toIsoString(item.createdAt);
}

export function mapContentToEventDetail(item: ContentDetailData): EventDetail {
  const eventDate = item.eventDate
    ? toIsoString(item.eventDate)
    : toIsoString(item.createdAt);

  return {
    id: item.id,
    slug: item.slug,
    title: item.title,
    eventDate,
    imageUrl: item.coverImages[0]?.fileUrl ?? null,
    bodyContent: item.bodyContent,
    imageUrls: item.coverImages.map((img) => img.fileUrl),
    locationAddress: item.locationAddress ?? null,
    locationUrl: item.locationUrl ?? null,
  };
}

export async function fetchNewsPage(params: {
  limit: number;
  cursor?: string;
  q?: string;
  dateFrom?: string;
  dateTo?: string;
}): Promise<NewsPage> {
  const page = await fetchContentPage({ type: 'NEWS', ...params });
  return {
    items: page.items.map(mapContentToNewsArticle),
    nextCursor: page.nextCursor,
    hasMore: page.hasMore,
  };
}

export async function fetchNewsById(id: string): Promise<NewsArticleDetail> {
  const item = await fetchContentById(id);
  if (item.type !== 'NEWS') {
    throw new Error('Berita tidak ditemukan');
  }
  return mapContentToNewsDetail(item);
}

export async function fetchEventsPage(params: {
  limit: number;
  cursor?: string;
  q?: string;
  dateFrom?: string;
  dateTo?: string;
}): Promise<EventPage> {
  const page = await fetchContentPage({ type: 'EVENT', ...params });
  return {
    items: page.items.map(mapContentToEventItem),
    nextCursor: page.nextCursor,
    hasMore: page.hasMore,
  };
}

export async function fetchEventById(id: string): Promise<EventDetail> {
  const item = await fetchContentById(id);
  if (item.type !== 'EVENT') {
    throw new Error('Event tidak ditemukan');
  }
  return mapContentToEventDetail(item);
}
