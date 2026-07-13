import { prisma } from '../../../db';
import { IBranchService } from '../interfaces/branch.interface';
import { BranchDetailData, BranchListItemData, GetBranchesParams } from '../types/branch.types';
import { PaginatedResponse, encodeCursor, decodeCursor } from '../../../shared/types/pagination.types';

export class BranchService implements IBranchService {
  async getById(id: number): Promise<BranchDetailData | null> {
    const branch = await prisma.branch.findUnique({
      where: { id },
      include: {
        images: {
          include: {
            media: true
          },
          orderBy: {
            sortOrder: 'asc'
          }
        }
      }
    });

    if (!branch) {
      return null;
    }

    return {
      id: branch.id,
      branchCode: branch.branchCode,
      name: branch.name,
      address: branch.address,
      phone: branch.phone,
      locationUrl: branch.locationUrl,
      isOnlineWarehouse: branch.isOnlineWarehouse,
      images: branch.images.map(img => ({
        id: img.id,
        fileUrl: img.media.fileUrl,
        sortOrder: img.sortOrder
      })),
      createdAt: branch.createdAt,
      updatedAt: branch.updatedAt
    };
  }

  async getAll(params: GetBranchesParams): Promise<PaginatedResponse<BranchListItemData>> {
    const limit = Math.min(params.limit || 15, 50);

    // Decode cursor untuk pagination
    let whereClause: any = {};
    if (params.cursor) {
      const decoded = decodeCursor(params.cursor);
      if (decoded && decoded.id && decoded.name) {
        // Cursor pagination: name > lastItem.name OR (name = lastItem.name AND id > lastItem.id)
        whereClause = {
          OR: [
            { name: { gt: decoded.name } },
            {
              AND: [
                { name: decoded.name },
                { id: { gt: decoded.id } }
              ]
            }
          ]
        };
      }
    }

    // Query limit+1 untuk check hasMore
    const branches = await prisma.branch.findMany({
      where: whereClause,
      take: limit + 1,
      orderBy: [
        { name: 'asc' },
        { id: 'asc' }
      ],
      include: {
        images: {
          include: {
            media: true
          },
          orderBy: {
            sortOrder: 'asc'
          }
        }
      }
    });

    const hasMore = branches.length > limit;
    const data = branches.slice(0, limit);

    // Generate nextCursor dari last item
    let nextCursor: string | null = null;
    if (hasMore && data.length > 0) {
      const lastItem = data[data.length - 1];
      nextCursor = encodeCursor({
        id: lastItem.id,
        name: lastItem.name
      });
    }

    return {
      data: data.map(branch => ({
        id: branch.id,
        branchCode: branch.branchCode,
        name: branch.name,
        address: branch.address,
        phone: branch.phone,
        locationUrl: branch.locationUrl,
        isOnlineWarehouse: branch.isOnlineWarehouse,
        images: branch.images.map(img => ({
          id: img.id,
          fileUrl: img.media.fileUrl,
          sortOrder: img.sortOrder
        })),
        createdAt: branch.createdAt,
        updatedAt: branch.updatedAt
      })),
      pagination: {
        nextCursor,
        hasMore,
        limit
      }
    };
  }
}

export const branchService = new BranchService();
