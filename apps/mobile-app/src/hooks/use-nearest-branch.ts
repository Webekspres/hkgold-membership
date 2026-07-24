import { useCallback, useEffect, useState } from 'react';
import { Linking } from 'react-native';
import { useQuery } from '@tanstack/react-query';
import * as Location from 'expo-location';

import { fetchNearestBranch } from '@/services/branches';

export const NEAREST_BRANCH_QUERY_KEY = ['branch', 'nearest'] as const;

export type NearestBranchStatus =
  | 'loading'
  | 'success'
  | 'denied'
  | 'error'
  | 'empty';

type Coords = { lat: number; lng: number };

async function resolveCoords(): Promise<
  { ok: true; coords: Coords } | { ok: false; reason: 'denied' | 'error' }
> {
  try {
    const current = await Location.getForegroundPermissionsAsync();
    let status = current.status;

    if (status !== Location.PermissionStatus.GRANTED) {
      const requested = await Location.requestForegroundPermissionsAsync();
      status = requested.status;
    }

    if (status !== Location.PermissionStatus.GRANTED) {
      return { ok: false, reason: 'denied' };
    }

    const position = await Location.getCurrentPositionAsync({
      accuracy: Location.Accuracy.Balanced,
    });

    return {
      ok: true,
      coords: {
        lat: position.coords.latitude,
        lng: position.coords.longitude,
      },
    };
  } catch {
    return { ok: false, reason: 'error' };
  }
}

export function useNearestBranch() {
  const [coords, setCoords] = useState<Coords | null>(null);
  const [locationStatus, setLocationStatus] = useState<'pending' | 'ready' | 'denied' | 'error'>(
    'pending',
  );

  const resolveLocation = useCallback(async () => {
    setLocationStatus('pending');
    const result = await resolveCoords();
    if (!result.ok) {
      setCoords(null);
      setLocationStatus(result.reason);
      return;
    }
    setCoords(result.coords);
    setLocationStatus('ready');
  }, []);

  useEffect(() => {
    void resolveLocation();
  }, [resolveLocation]);

  const query = useQuery({
    queryKey: [...NEAREST_BRANCH_QUERY_KEY, coords?.lat, coords?.lng],
    queryFn: () => fetchNearestBranch(coords!.lat, coords!.lng),
    enabled: locationStatus === 'ready' && coords != null,
    staleTime: 15 * 60_000,
    retry: 1,
  });

  let status: NearestBranchStatus = 'loading';
  if (locationStatus === 'denied') status = 'denied';
  else if (locationStatus === 'error') status = 'error';
  else if (locationStatus === 'ready') {
    if (query.isLoading) status = 'loading';
    else if (query.isError) status = 'error';
    else if (!query.data) status = 'empty';
    else status = 'success';
  }

  const refetch = useCallback(async () => {
    if (locationStatus !== 'ready' || !coords) {
      await resolveLocation();
      return;
    }
    await query.refetch();
  }, [coords, locationStatus, query, resolveLocation]);

  const requestPermission = useCallback(async () => {
    try {
      const current = await Location.getForegroundPermissionsAsync();
      if (
        current.status !== Location.PermissionStatus.GRANTED &&
        current.canAskAgain === false
      ) {
        await Linking.openSettings();
        return;
      }
    } catch {
      // fall through to resolve
    }
    await resolveLocation();
  }, [resolveLocation]);

  return {
    branch: query.data ?? null,
    status,
    isLoading: status === 'loading',
    refetch,
    requestPermission,
  };
}
