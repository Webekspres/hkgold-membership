import { prisma } from '../../../db';
import { IContentService } from '../interfaces/content.interface';
import { ContentDetailData, ContentListItemData, GetContentsParams } from '../types/content.types';
import { PaginatedResponse, encodeCursor, decodeCursor } from '../../../shared/types/pagination.types';

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

    // Status filter: PUBLISHED, atau PUBLISHED + ARCHIVED
    const statusFilter = includeArchived
      ? { in: ['PUBLISHED', 'ARCHIVED'] as const }
      : { equals: 'PUBLISHED' as const };

    // Decode cursor untuk pagination
    let whereClause: any = {
      type,
      status: statusFilter
    };

    if (params.cursor) {
      const decoded = decodeCursor(params.cursor);
      if (decoded && decoded.id && decoded.createdAt) {
        // Cursor pagination: createdAt < lastItem.createdAt OR (createdAt = lastItem.createdAt AND id < lastItem.id)
        whereClause = {
          ...whereClause,
          OR: [
            { createdAt: { lt: new Date(decoded.createdAt) } },
            {
              AND: [
                { createdAt: new Date(decoded.createdAt) },
                { id: { lt: decoded.id } }
              ]
            }
          ]
        };
      }
    }

    // Query limit+1 untuk check hasMore
    const contents = await prisma.content.findMany({
      where: whereClause,
      take: limit + 1,
      orderBy: [
        { createdAt: 'desc' },
        { id: 'desc' }
      ],
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

    const hasMore = contents.length > limit;
    const data = contents.slice(0, limit);

    // Generate nextCursor dari last item
    let nextCursor: string | null = null;
    if (hasMore && data.length > 0) {
      const lastItem = data[data.length - 1];
      nextCursor = encodeCursor({
        id: lastItem.id,
        createdAt: lastItem.createdAt.toISOString()
      });
    }

    return {
      data: data.map(content => ({
        id: content.id,
        type: content.type as 'NEWS' | 'EVENT',
        title: content.title,
        slug: content.slug,
        excerpt: content.bodyContent.substring(0, 200) + (content.bodyContent.length > 200 ? '...' : ''),
        eventDate: content.eventDate,
        coverImages: content.contentCoverImages.map(img => ({
          id: img.id,
          fileUrl: img.media.fileUrl,
          sortOrder: img.sortOrder
        })),
        createdAt: content.createdAt
      })),
      pagination: {
        nextCursor,
        hasMore,
        limit
      }
    };
  }
}

export const contentService = new ContentService();
