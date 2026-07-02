import { SymbolView } from "expo-symbols";
import { useMemo, useState } from "react";
import { ScrollView, View } from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";

import { RewardCatalogGrid } from "@/components/reward/reward-catalog-grid";
import { RewardFilterModal } from "@/components/reward/reward-filter-modal";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Text } from "@/components/ui/text";
import { SCREEN_HORIZONTAL_PADDING } from "@/constants/layout/screen-layout";
import {
  applyRewardFilters,
  createDefaultRewardFilter,
  getRewardPointsBounds,
  hasActiveRewardFilter,
  type RewardFilterState,
} from "@/lib/filters/filter-rewards";
import { getRewardCategories, getRewardList } from "@/services/rewards";

const FILTER_ICON = {
  ios: "line.3.horizontal.decrease.circle",
  android: "filter_list",
  web: "filter_list",
} as const;

const rewardList = getRewardList();
const rewardCategories = getRewardCategories();

const POINTS_BOUNDS = getRewardPointsBounds(rewardList);
const DEFAULT_FILTER = createDefaultRewardFilter(POINTS_BOUNDS);

export default function RewardTabScreen() {
  const [filterVisible, setFilterVisible] = useState(false);
  const [appliedFilter, setAppliedFilter] = useState<RewardFilterState>(DEFAULT_FILTER);
  const [draftFilter, setDraftFilter] = useState<RewardFilterState>(DEFAULT_FILTER);

  const filteredRewards = useMemo(
    () => applyRewardFilters(rewardList, appliedFilter),
    [appliedFilter]
  );

  const hasActiveFilter = hasActiveRewardFilter(appliedFilter, POINTS_BOUNDS);

  const openFilter = () => {
    setDraftFilter(appliedFilter);
    setFilterVisible(true);
  };

  const handleApplyFilter = () => {
    setAppliedFilter(draftFilter);
    setFilterVisible(false);
  };

  const handleResetFilter = () => {
    setDraftFilter(DEFAULT_FILTER);
    setAppliedFilter(DEFAULT_FILTER);
    setFilterVisible(false);
  };

  return (
    <View className="flex-1 bg-background">
      <SafeAreaView className="flex-1" edges={["top"]}>
        <View
          className="flex-row items-center gap-2 border-b border-stone-200 bg-background py-3"
          style={{ paddingHorizontal: SCREEN_HORIZONTAL_PADDING }}>
          <Input
            className="min-w-0 flex-1"
            placeholder="Cari reward..."
            placeholderTextColor="#a8a29e"
            editable
          />

          <Button
            variant="outline"
            size="icon"
            className={hasActiveFilter ? "border-[#e8a020] bg-[#fffbeb]" : undefined}
            onPress={openFilter}>
            <SymbolView
              name={FILTER_ICON}
              size={20}
              tintColor={hasActiveFilter ? "#b45309" : "#44403c"}
            />
          </Button>
        </View>

        <ScrollView
          showsVerticalScrollIndicator={false}
          contentContainerStyle={{ paddingVertical: 16, paddingBottom: 24 }}>
          {filteredRewards.length === 0 ? (
            <View className="items-center px-4 py-12">
              <Text variant="muted">Tidak ada hadiah pada filter ini.</Text>
            </View>
          ) : (
            <RewardCatalogGrid rewards={filteredRewards} />
          )}
        </ScrollView>
      </SafeAreaView>

      <RewardFilterModal
        visible={filterVisible}
        categories={rewardCategories}
        bounds={POINTS_BOUNDS}
        filter={draftFilter}
        onFilterChange={setDraftFilter}
        onClose={() => setFilterVisible(false)}
        onApply={handleApplyFilter}
        onReset={handleResetFilter}
      />
    </View>
  );
}
