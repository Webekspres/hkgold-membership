import { prisma } from '../../../db';
import { IRewardService } from '../interfaces/reward.interface';
import {
  RewardCategoryData,
  RewardCatalogItemData,
  RewardDetailData,
  RewardCategoryGroupData,
  RewardBranchStockData,
  GetRewardsParams,
  PaginationResponse,
  encodeCursor,
  decodeCursor
} from '../types/reward.types';

export class RewardService implements IRewardService {
  /**
   * Helper: Get first image URL from reward images
   */
  private async getFirstImageUrl(rewardId: string): Promise<string | null> {
    const firstImage = await prisma.rewardImage.findFirst({
      where: { rewardId },
      include: { media: true },
      orderBy: { sortOrder: 'asc' }
    });

    return firstImage?.media.fileUrl || null;
  }

  /**
   * Helper: Get all image URLs ordered by sortOrder
   */
  private async getImageUrls(rewardId: string): Promise<string[]> {
    const images = await prisma.rewardImage.findMany({
      where: { rewardId },
      include: { media: true },
      orderBy: { sortOrder: 'asc' }
    });

    return images.map(img => img.media.fileUrl);
  }

  /**
   * Helper: Calculate total available stock across branches
   * availableStock = MAX(actualStock - heldStock, 0)
   * stockRemaining = SUM(availableStock)
   */
  private async calculateStockRemaining(rewardId: string, branchId?: number): Promise<number> {
    const stocks = await prisma.rewardBranchStock.findMany({
      where: {
        rewardId,
        ...(branchId ? { branchId } : {})
      }
    });

    return stocks.reduce((total, stock) => {
      const available = Math.max(stock.actualStock - stock.heldStock, 0);
      return total + available;
    }, 0);
  }

  /**
   * Helper: Get branch stocks with location details
   * Joins: Branch → Address → Village → SubDistrict → City
   */
  private async getBranchStocks(rewardId: string): Promise<RewardBranchStockData[]> {
    const stocks = await prisma.rewardBranchStock.findMany({
      where: { rewardId },
      include: {
        branch: {
          include: {
            normalizedAddress: {
              include: {
                village: {
                  include: {
                    subDistrict: {
                      include: {
                        city: true
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    });

    return stocks.map(stock => ({
      branchId: stock.branchId,
      branchName: stock.branch.name,
      subdistrict: stock.branch.normalizedAddress?.village.subDistrict.nama || '-',
      city: stock.branch.normalizedAddress?.village.subDistrict.city.nama || '-',
      locationUrl: stock.branch.locationUrl,
      actualStock: stock.actualStock,
      heldStock: stock.heldStock
    }));
  }

  /**
   * Get all reward categories
   */
  async getCategories(): Promise<RewardCategoryData[]> {
    const categories = await prisma.categoryReward.findMany({
      orderBy: { name: 'asc' }
    });

    return categories.map(cat => ({
      id: cat.id,
      name: cat.name,
      slug: cat.slug
    }));
  }

  /**
   * Get rewards with filters and pagination
   */
  async getRewards(params: GetRewardsParams): Promise<PaginationResponse<RewardCatalogItemData>> {
    const limit = Math.min(params.limit || 15, 50);
    const now = new Date();
    const sortBy = params.sortBy ?? 'sku';
    const sortOrder = params.sortOrder ?? 'asc';

    const andFilters: Record<string, unknown>[] = [
      { isActive: true },
      { startAt: { lte: now } },
      { endAt: { gte: now } },
    ];

    if (params.categoryIds && params.categoryIds.length > 0) {
      andFilters.push({ categoryId: { in: params.categoryIds } });
    }

    if (params.pointsMin !== undefined || params.pointsMax !== undefined) {
      const pointsRange: { gte?: number; lte?: number } = {};
      if (params.pointsMin !== undefined) pointsRange.gte = params.pointsMin;
      if (params.pointsMax !== undefined) pointsRange.lte = params.pointsMax;
      andFilters.push({ pointsRequired: pointsRange });
    }

    if (params.search) {
      andFilters.push({
        OR: [
          { name: { contains: params.search } },
          { description: { contains: params.search } },
        ],
      });
    }

    if (params.branchId) {
      andFilters.push({
        rewardBranchStocks: {
          some: {
            branchId: params.branchId,
            actualStock: { gt: 0 },
          },
        },
      });
    }

    if (params.cursor) {
      const decoded = decodeCursor(params.cursor);
      if (decoded) {
        const cursorWhere = this.buildSortCursorWhere(sortBy, sortOrder, decoded);
        if (cursorWhere) {
          andFilters.push(cursorWhere);
        }
      }
    }

    const orderBy = this.buildOrderBy(sortBy, sortOrder);

    const rewards = await prisma.reward.findMany({
      where: { AND: andFilters },
      take: limit + 1,
      orderBy,
      include: {
        category: true,
        rewardImages: {
          include: { media: true },
          orderBy: { sortOrder: 'asc' },
          take: 1,
        },
        rewardBranchStocks: true,
      },
    });

    const hasMore = rewards.length > limit;
    const data = rewards.slice(0, limit);

    let nextCursor: string | null = null;
    if (hasMore && data.length > 0) {
      const lastItem = data[data.length - 1];
      nextCursor = encodeCursor({
        id: lastItem.id,
        sku: lastItem.sku,
        name: lastItem.name,
        pointsRequired: lastItem.pointsRequired,
      });
    }

    const catalogItems: RewardCatalogItemData[] = data.map((reward) => {
      const stockRemaining = reward.rewardBranchStocks.reduce((total, stock) => {
        const available = Math.max(stock.actualStock - stock.heldStock, 0);
        return total + available;
      }, 0);

      return {
        id: reward.id,
        sku: reward.sku,
        name: reward.name,
        categoryId: reward.categoryId,
        categoryName: reward.category.name,
        categorySlug: reward.category.slug,
        pointsRequired: reward.pointsRequired,
        stockRemaining,
        image: reward.rewardImages[0]?.media.fileUrl || null,
      };
    });

    return {
      data: catalogItems,
      pagination: {
        nextCursor,
        hasMore,
        limit,
      },
    };
  }

  private buildOrderBy(
    sortBy: 'sku' | 'name' | 'points',
    sortOrder: 'asc' | 'desc',
  ): Array<Record<string, 'asc' | 'desc'>> {
    if (sortBy === 'name') {
      return [{ name: sortOrder }, { id: sortOrder }];
    }
    if (sortBy === 'points') {
      return [{ pointsRequired: sortOrder }, { id: sortOrder }];
    }
    return [{ sku: sortOrder }, { id: sortOrder }];
  }

  private buildSortCursorWhere(
    sortBy: 'sku' | 'name' | 'points',
    sortOrder: 'asc' | 'desc',
    cursor: { id: string; sku?: string; name?: string; pointsRequired?: number },
  ): Record<string, unknown> | null {
    const op = sortOrder === 'asc' ? 'gt' : 'lt';

    if (sortBy === 'name' && cursor.name != null) {
      return {
        OR: [
          { name: { [op]: cursor.name } },
          { AND: [{ name: cursor.name }, { id: { [op]: cursor.id } }] },
        ],
      };
    }

    if (sortBy === 'points' && cursor.pointsRequired != null) {
      return {
        OR: [
          { pointsRequired: { [op]: cursor.pointsRequired } },
          {
            AND: [
              { pointsRequired: cursor.pointsRequired },
              { id: { [op]: cursor.id } },
            ],
          },
        ],
      };
    }

    if (cursor.sku != null) {
      return {
        OR: [
          { sku: { [op]: cursor.sku } },
          { AND: [{ sku: cursor.sku }, { id: { [op]: cursor.id } }] },
        ],
      };
    }

    return null;
  }

  /**
   * Get reward detail by SKU
   */
  async getBySku(sku: string): Promise<RewardDetailData | null> {
    const now = new Date();

    const reward = await prisma.reward.findUnique({
      where: { sku },
      include: {
        category: true,
        rewardImages: {
          include: { media: true },
          orderBy: { sortOrder: 'asc' }
        },
        rewardBranchStocks: true
      }
    });

    if (!reward) {
      return null;
    }

    // Check if reward is active
    if (!reward.isActive || reward.startAt > now || reward.endAt < now) {
      return null;
    }

    // Calculate stock remaining
    const stockRemaining = reward.rewardBranchStocks.reduce((total, stock) => {
      const available = Math.max(stock.actualStock - stock.heldStock, 0);
      return total + available;
    }, 0);

    // Get all images
    const images = reward.rewardImages.map(img => img.media.fileUrl);

    // Get branch stocks with location details
    const branchStocks = await this.getBranchStocks(reward.id);

    return {
      id: reward.id,
      sku: reward.sku,
      name: reward.name,
      categoryId: reward.categoryId,
      categoryName: reward.category.name,
      categorySlug: reward.category.slug,
      pointsRequired: reward.pointsRequired,
      stockRemaining,
      image: images[0] || null,
      description: reward.description,
      images,
      branchStocks
    };
  }

  /**
   * Get catalog grouped by categories
   */
  async getCatalog(params?: { categoryIds?: number[] }): Promise<RewardCategoryGroupData[]> {
    const now = new Date();

    // Get categories
    let categoryWhere: any = {};
    if (params?.categoryIds && params.categoryIds.length > 0) {
      categoryWhere.id = { in: params.categoryIds };
    }

    const categories = await prisma.categoryReward.findMany({
      where: categoryWhere,
      orderBy: { name: 'asc' },
      include: {
        rewards: {
          where: {
            isActive: true,
            startAt: { lte: now },
            endAt: { gte: now }
          },
          orderBy: [
            { sku: 'asc' },
            { id: 'asc' }
          ],
          include: {
            rewardImages: {
              include: { media: true },
              orderBy: { sortOrder: 'asc' },
              take: 1
            },
            rewardBranchStocks: true
          }
        }
      }
    });

    return categories.map(category => ({
      id: category.id,
      name: category.name,
      slug: category.slug,
      rewards: category.rewards.map(reward => {
        const stockRemaining = reward.rewardBranchStocks.reduce((total, stock) => {
          const available = Math.max(stock.actualStock - stock.heldStock, 0);
          return total + available;
        }, 0);

        return {
          id: reward.id,
          sku: reward.sku,
          name: reward.name,
          categoryId: reward.categoryId,
          categoryName: category.name,
          categorySlug: category.slug,
          pointsRequired: reward.pointsRequired,
          stockRemaining,
          image: reward.rewardImages[0]?.media.fileUrl || null
        };
      })
    }));
  }
}

export const rewardService = new RewardService();
