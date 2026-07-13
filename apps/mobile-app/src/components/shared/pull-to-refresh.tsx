import { RefreshControl } from 'react-native';

const BRAND_GOLD = '#e8a020';

type PullToRefreshOptions = {
  refreshing: boolean;
  onRefresh: () => void;
};

/** Props siap pakai untuk `ScrollView`/`FlatList` `refreshControl`. */
export function createPullToRefreshControl({
  refreshing,
  onRefresh,
}: PullToRefreshOptions) {
  return (
    <RefreshControl
      refreshing={refreshing}
      onRefresh={onRefresh}
      tintColor={BRAND_GOLD}
      colors={[BRAND_GOLD]}
    />
  );
}
