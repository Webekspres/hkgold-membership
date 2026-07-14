export type BranchItem = {
  id: number;
  branchCode: string;
  name: string;
  subdistrict: string;
  city: string;
  phone: string | null;
  locationUrl: string | null;
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
