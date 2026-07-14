import { SymbolView } from "expo-symbols";
import { View } from "react-native";

import { GoldButton } from "@/components/shared/gold-button";
import { Button } from "@/components/ui/button";
import { Text } from "@/components/ui/text";
import type { BranchItem } from "@/types/branch";
import { formatBranchLocation } from "@/lib/format/format-branch-location";
import { openLocationUrl } from "@/lib/open-location-url";

const MAP_ICON = {
  ios: "map",
  android: "map",
  web: "map",
} as const;

type NearestBranchCardProps = {
  branch: BranchItem;
};

export function NearestBranchCard({ branch }: NearestBranchCardProps) {
  return (
    <View className="rounded-xl border border-stone-200 shadow-md shadow-stone-900/15">
      <View className="flex-row items-center gap-3 rounded-[10px] bg-white p-4">
        <View className="min-w-0 flex-1 gap-1">
          <Text
            className="text-base font-semibold leading-snug text-stone-900"
            numberOfLines={2}
          >
            {branch.name}
          </Text>
          <Text variant="muted" className="text-sm">
            {formatBranchLocation(branch.subdistrict, branch.city)}
          </Text>
        </View>

        <Button
          variant="outline"
          size="icon"
          className="border-[#e8a020] bg-[#fffbeb]"
          disabled={!branch.locationUrl}
          onPress={() => openLocationUrl(branch.locationUrl)}
        >
          <SymbolView name={MAP_ICON} size={20} tintColor="#b45309" />
        </Button>
      </View>
    </View>
  );
}
