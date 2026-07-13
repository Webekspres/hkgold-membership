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

    // Build where clause
    let whereClause: any = {
      isActive: true,
      startAt: { lte: now },
      endAt: { gte: now }
    };

    // Cursor pagination
    if (params.cursor) {
      const decoded = decodeCursor(params.cursor);
      if (decoded) {
        whereClause.OR = [
          { sku: { gt: decoded.sku } },
          {
            AND: [
              { sku: decoded.sku },
              { id: { gt: decoded.id } }
            ]
          }
        ];
      }
    }

    // Category filter
    if (params.categoryIds && params.categoryIds.length > 0) {
      whereClause.categoryId = { in: params.categoryIds };
    }

    // Point range filter
    if (params.pointsMin !== undefined) {
      whereClause.pointsRequired = { ...whereClause.pointsRequired, gte: params.pointsMin };
    }
    if (params.pointsMax !== undefined) {
      whereClause.pointsRequired = { ...whereClause.pointsRequired, lte: params.pointsMax };
    }

    // Search filter
    if (params.search) {
      whereClause.OR = [
        { name: { contains: params.search, mode: 'insensitive' } },
        { description: { contains: params.search, mode: 'insensitive' } }
      ];
    }

    // Branch filter - only include rewards with stock in specific branch
    if (params.branchId) {
      whereClause.rewardBranchStocks = {
        some: {
          branchId: params.branchId,
          actualStock: { gt: 0 }
        }
      };
    }

    // Query limit+1 to check hasMore
    const rewards = await prisma.reward.findMany({
      where: whereClause,
      take: limit + 1,
      orderBy: [
        { sku: 'asc' },
        { id: 'asc' }
      ],
      include: {
        category: true,
        rewardImages: {
          include: { media: true },
          orderBy: { sortOrder: 'asc' },
          take: 1
        },
        rewardBranchStocks: true
      }
    });

    const hasMore = rewards.length > limit;
    const data = rewards.slice(0, limit);

    // Generate nextCursor
    let nextCursor: string | null = null;
    if (hasMore && data.length > 0) {
      const lastItem = data[data.length - 1];
      nextCursor = encodeCursor({
        sku: lastItem.sku,
        id: lastItem.id
      });
    }

    // Map to catalog items
    const catalogItems: RewardCatalogItemData[] = data.map(reward => {
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
        image: reward.rewardImages[0]?.media.fileUrl || null
      };
    });

    return {
      data: catalogItems,
      pagination: {
        nextCursor,
        hasMore,
        limit
      }
    };
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
