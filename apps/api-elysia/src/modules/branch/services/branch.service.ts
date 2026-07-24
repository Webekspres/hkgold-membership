import { prisma } from '../../../db';
import { IBranchService } from '../interfaces/branch.interface';
import {
  BranchCityOption,
  BranchDetailData,
  BranchListItemData,
  BranchNearestData,
  GetBranchesParams,
} from '../types/branch.types';
import { PaginatedResponse, encodeCursor, decodeCursor } from '../../../shared/types/pagination.types';
import { haversineKm } from '../lib/haversine';

const addressInclude = {
  village: {
    include: {
      subDistrict: {
        include: {
          city: true,
        },
      },
    },
  },
} as const;

const branchListInclude = {
  images: {
    include: {
      media: true,
    },
    orderBy: {
      sortOrder: 'asc' as const,
    },
  },
  normalizedAddress: {
    include: addressInclude,
  },
};

function mapCitySubdistrict(branch: {
  normalizedAddress?: {
    village?: {
      subDistrict?: {
        nama: string;
        city?: { nama: string } | null;
      } | null;
    } | null;
  } | null;
}): { city: string; subdistrict: string } {
  const sub = branch.normalizedAddress?.village?.subDistrict;
  return {
    city: sub?.city?.nama ?? '',
    subdistrict: sub?.nama ?? '',
  };
}

function mapBranch(branch: {
  id: number;
  branchCode: string;
  name: string;
  address: string;
  phone: string | null;
  locationUrl: string | null;
  isOnlineWarehouse: boolean;
  createdAt: Date;
  updatedAt: Date;
  images: { id: string; sortOrder: number; media: { fileUrl: string } }[];
  normalizedAddress?: {
    village?: {
      subDistrict?: {
        nama: string;
        city?: { nama: string } | null;
      } | null;
    } | null;
  } | null;
}): BranchDetailData {
  const { city, subdistrict } = mapCitySubdistrict(branch);
  return {
    id: branch.id,
    branchCode: branch.branchCode,
    name: branch.name,
    address: branch.address,
    phone: branch.phone,
    locationUrl: branch.locationUrl,
    isOnlineWarehouse: branch.isOnlineWarehouse,
    city,
    subdistrict,
    images: branch.images.map((img) => ({
      id: img.id,
      fileUrl: img.media.fileUrl,
      sortOrder: img.sortOrder,
    })),
    createdAt: branch.createdAt,
    updatedAt: branch.updatedAt,
  };
}

function toNumber(value: unknown): number | null {
  if (value == null) return null;
  const n = typeof value === 'number' ? value : Number(value);
  return Number.isFinite(n) ? n : null;
}

export class BranchService implements IBranchService {
  async getById(id: number): Promise<BranchDetailData | null> {
    const branch = await prisma.branch.findUnique({
      where: { id },
      include: branchListInclude,
    });

    if (!branch) {
      return null;
    }

    return mapBranch(branch);
  }

  async getCities(): Promise<BranchCityOption[]> {
    const branches = await prisma.branch.findMany({
      where: { addressId: { not: null } },
      select: {
        normalizedAddress: {
          select: {
            village: {
              select: {
                subDistrict: {
                  select: {
                    city: { select: { id: true, nama: true } },
                  },
                },
              },
            },
          },
        },
      },
    });

    const byId = new Map<number, string>();
    for (const branch of branches) {
      const city = branch.normalizedAddress?.village?.subDistrict?.city;
      if (city) {
        byId.set(city.id, city.nama);
      }
    }

    return [...byId.entries()]
      .map(([id, name]) => ({ id, name }))
      .sort((a, b) => a.name.localeCompare(b.name, 'id'));
  }

  async getNearest(lat: number, lng: number): Promise<BranchNearestData | null> {
    const branches = await prisma.branch.findMany({
      where: {
        isOnlineWarehouse: false,
        latitude: { not: null },
        longitude: { not: null },
      },
      include: branchListInclude,
    });

    let best: BranchNearestData | null = null;

    for (const branch of branches) {
      const branchLat = toNumber(branch.latitude);
      const branchLng = toNumber(branch.longitude);
      if (branchLat == null || branchLng == null) continue;

      const distanceKm =
        Math.round(haversineKm(lat, lng, branchLat, branchLng) * 10) / 10;
      if (!best || distanceKm < best.distanceKm) {
        best = { ...mapBranch(branch), distanceKm };
      }
    }

    return best;
  }

  async getAll(params: GetBranchesParams): Promise<PaginatedResponse<BranchListItemData>> {
    const limit = Math.min(params.limit || 15, 50);
    const andFilters: Record<string, unknown>[] = [];

    const q = params.q?.trim();
    if (q && q.length > 2) {
      andFilters.push({
        OR: [
          { name: { contains: q } },
          { address: { contains: q } },
        ],
      });
    }

    const city = params.city?.trim();
    if (city && city !== 'all') {
      andFilters.push({
        normalizedAddress: {
          village: {
            subDistrict: {
              city: { nama: city },
            },
          },
        },
      });
    }

    if (params.cursor) {
      const decoded = decodeCursor(params.cursor);
      if (decoded && decoded.id && decoded.name) {
        andFilters.push({
          OR: [
            { name: { gt: decoded.name } },
            {
              AND: [{ name: decoded.name }, { id: { gt: decoded.id } }],
            },
          ],
        });
      }
    }

    const whereClause = andFilters.length > 0 ? { AND: andFilters } : {};

    const branches = await prisma.branch.findMany({
      where: whereClause,
      take: limit + 1,
      orderBy: [{ name: 'asc' }, { id: 'asc' }],
      include: branchListInclude,
    });

    const hasMore = branches.length > limit;
    const data = branches.slice(0, limit);

    let nextCursor: string | null = null;
    if (hasMore && data.length > 0) {
      const lastItem = data[data.length - 1];
      nextCursor = encodeCursor({
        id: lastItem.id,
        name: lastItem.name,
      });
    }

    return {
      data: data.map(mapBranch),
      pagination: {
        nextCursor,
        hasMore,
        limit,
      },
    };
  }
}

export const branchService = new BranchService();
