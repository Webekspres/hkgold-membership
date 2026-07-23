import { useQuery } from '@tanstack/react-query';
import { useMemo } from 'react';

import type { BranchCityOption as DropdownOption } from '@/components/branch/branch-city-filter-dropdown';
import { fetchBranchCities } from '@/services/branches';

export function useBranchCities() {
  const query = useQuery({
    queryKey: ['branches', 'cities'],
    queryFn: fetchBranchCities,
    staleTime: 15 * 60_000,
    retry: 1,
  });

  const options: DropdownOption[] = useMemo(() => {
    const cities = query.data ?? [];
    return [
      { label: 'Semua kota', value: 'all' },
      ...cities.map((city) => ({ label: city.name, value: city.name })),
    ];
  }, [query.data]);

  return {
    options,
    isLoading: query.isLoading,
    isError: query.isError,
    refetch: query.refetch,
  };
}
