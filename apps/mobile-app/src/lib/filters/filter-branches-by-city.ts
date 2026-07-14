import type { BranchItem } from '@/types/branch';

export function filterBranchesByCity(branches: BranchItem[], city: string | null) {
  if (!city || city === 'all') {
    return branches;
  }

  return branches.filter((branch) => branch.city === city);
}

export function getBranchCityOptions(branches: BranchItem[]) {
  const cities = [...new Set(branches.map((branch) => branch.city))].sort((a, b) =>
    a.localeCompare(b, 'id')
  );

  return [
    { label: 'Semua kota', value: 'all' },
    ...cities.map((city) => ({ label: city, value: city })),
  ];
}
