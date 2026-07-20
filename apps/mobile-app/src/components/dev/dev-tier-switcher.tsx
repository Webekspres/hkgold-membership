import { Pressable, View } from "react-native";

import { Text } from "@/components/ui/text";
import { cn } from "@/lib/utils";
import type { MemberTier } from "@/types/auth";

const TIERS: MemberTier[] = ["SILVER", "GOLD", "PLATINUM", "ELITE"];

type DevTierSwitcherProps = {
  selected: MemberTier;
  onSelect: (tier: MemberTier) => void;
};

/** ponytail: dev-only — preview tier styling on member card. */
export function DevTierSwitcher({ selected, onSelect }: DevTierSwitcherProps) {
  if (!__DEV__) {
    return null;
  }

  return (
    <View className="mt-2 gap-1.5">
      <Text variant="muted" className="text-[10px] uppercase tracking-wide">
        Dev — preview tier
      </Text>
      <View className="flex-row flex-wrap gap-1.5">
        {TIERS.map((tier) => {
          const active = tier === selected;

          return (
            <Pressable
              key={tier}
              onPress={() => onSelect(tier)}
              className={cn(
                "rounded-md px-2.5 py-1 active:opacity-80",
                active ? "bg-stone-900" : "bg-stone-200",
              )}
              accessibilityRole="button"
              accessibilityLabel={`Preview tier ${tier}`}
            >
              <Text
                className={cn(
                  "text-xs font-semibold",
                  active ? "text-white" : "text-stone-700",
                )}
              >
                {tier}
              </Text>
            </Pressable>
          );
        })}
      </View>
    </View>
  );
}
