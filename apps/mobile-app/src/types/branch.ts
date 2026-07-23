export type BranchItem = {
  id: number;
  branchCode: string;
  name: string;
  subdistrict: string;
  city: string;
  phone: string | null;
  locationUrl: string | null;
  /** URL foto cabang (gambar pertama dari API). */
  imageUrl?: string | null;
  /** Jarak ke user (km) — home nearest; null jika belum dihitung. */
  distanceKm?: number | null;
};

export type BranchCityOption = {
  id: number;
  name: string;
};

export type BranchPage = {
  items: BranchItem[];
  nextCursor: string | null;
  hasMore: boolean;
};
