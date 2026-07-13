import { prisma } from '../../../db';
import { IContentService } from '../interfaces/content.interface';
import { ContentDetailData, ContentListItemData, GetContentsParams } from '../types/content.types';
import { PaginatedResponse, encodeCursor, decodeCursor } from '../../../shared/types/pagination.types';

function startOfDay(iso: string): Date {
  const day = iso.slice(0, 10);
  return new Date(`${day}T00:00:00.000Z`);
}

function endOfDay(iso: string): Date {
  const day = iso.slice(0, 10);
  return new Date(`${day}T23:59:59.999Z`);
}

export class ContentService implements IContentService {
  async getById(id: string): Promise<ContentDetailData | null> {
    const content = await prisma.content.findUnique({
      where: { id },
      include: {
        contentCoverImages: {
          include: {
            media: true
          },
          orderBy: {
            sortOrder: 'asc'
          }
        }
      }
    });

    if (!content) {
      return null;
    }

    return {
      id: content.id,
      type: content.type as 'NEWS' | 'EVENT',
      title: content.title,
      slug: content.slug,
      bodyContent: content.bodyContent,
      eventDate: content.eventDate,
      status: content.status as 'DRAFT' | 'ARCHIVED' | 'PUBLISHED',
      coverImages: content.contentCoverImages.map(img => ({
        id: img.id,
        fileUrl: img.media.fileUrl,
        sortOrder: img.sortOrder
      })),
      createdAt: content.createdAt,
      updatedAt: content.updatedAt
    };
  }

  async getAll(params: GetContentsParams): Promise<PaginatedResponse<ContentListItemData>> {
    const limit = Math.min(params.limit || 15, 50);
    const type = params.type || 'NEWS';
    const includeArchived = params.includeArchived || false;

    const statusFilter = includeArchived
      ? { in: ['PUBLISHED', 'ARCHIVED'] as const }
      : { equals: 'PUBLISHED' as const };

    const andFilters: Record<string, unknown>[] = [
      { type },
      { status: statusFilter },
    ];

    const q = params.q?.trim();
    if (q && q.length > 2) {
      andFilters.push({
        title: { contains: q },
      });
    }

    const dateField = type === 'EVENT' ? 'eventDate' : 'createdAt';
    if (params.dateFrom || params.dateTo) {
      const range: { gte?: Date; lte?: Date } = {};
      if (params.dateFrom) {
        range.gte = startOfDay(params.dateFrom);
      }
      if (params.dateTo) {
        range.lte = endOfDay(params.dateTo);
      }
      andFilters.push({ [dateField]: range });
    }

    if (params.cursor) {
      const decoded = decodeCursor(params.cursor);
      if (decoded && decoded.id && decoded.createdAt) {
        andFilters.push({
          OR: [
            { createdAt: { lt: new Date(decoded.createdAt) } },
            {
              AND: [
                { createdAt: new Date(decoded.createdAt) },
                { id: { lt: decoded.id } },
              ],
            },
          ],
        });
      }
    }

    const whereClause = { AND: andFilters };

    const contents = await prisma.content.findMany({
      where: whereClause,
      take: limit + 1,
      orderBy: [
        { createdAt: 'desc' },
        { id: 'desc' },
      ],
      include: {
        contentCoverImages: {
          include: {
            media: true,
          },
          orderBy: {
            sortOrder: 'asc',
          },
        },
      },
    });

    const hasMore = contents.length > limit;
    const data = contents.slice(0, limit);

    let nextCursor: string | null = null;
    if (hasMore && data.length > 0) {
      const lastItem = data[data.length - 1];
      nextCursor = encodeCursor({
        id: lastItem.id,
        createdAt: lastItem.createdAt.toISOString(),
      });
    }

    return {
      data: data.map(content => ({
        id: content.id,
        type: content.type as 'NEWS' | 'EVENT',
        title: content.title,
        slug: content.slug,
        excerpt:
          content.bodyContent.substring(0, 200) +
          (content.bodyContent.length > 200 ? '...' : ''),
        eventDate: content.eventDate,
        coverImages: content.contentCoverImages.map(img => ({
          id: img.id,
          fileUrl: img.media.fileUrl,
          sortOrder: img.sortOrder,
        })),
        createdAt: content.createdAt,
      })),
      pagination: {
        nextCursor,
        hasMore,
        limit,
      },
    };
  }
}

export const contentService = new ContentService();
