import { router } from "expo-router";
import { Pressable, View } from "react-native";

import { RewardCategorySubsection } from "@/components/reward/reward-category-subsection";
import { Text } from "@/components/ui/text";
import type { RewardCategoryGroup } from '@/types/reward';
import { cn } from "@/lib/utils";

type RewardCatalogSectionProps = {
  categories: RewardCategoryGroup[];
  isError?: boolean;
  className?: string;
};

export function RewardCatalogSection({
  categories,
  isError = false,
  className,
}: RewardCatalogSectionProps) {
  if (isError) {
    return (
      <View className={cn("gap-1 px-4", className)}>
        <Text className="text-base font-semibold text-stone-900">
          Katalog Hadiah
        </Text>
        <Text variant="muted" className="text-sm">
          Gagal memuat katalog hadiah.
        </Text>
      </View>
    );
  }

  if (categories.length === 0) {
    return null;
  }

  return (
    <View className={cn("gap-5", className)}>
      <View className="flex-row items-center justify-between px-4">
        <Text className="text-base font-semibold text-stone-900">
          Katalog Hadiah
        </Text>
        <Pressable
          onPress={() => router.push("/reward")}
          className="active:opacity-70"
        >
          <Text className="text-sm font-medium text-[#c4841a]">
            Lihat semua
          </Text>
        </Pressable>
      </View>

      {categories.map((category) => (
        <RewardCategorySubsection key={category.id} category={category} />
      ))}
    </View>
  );
}
