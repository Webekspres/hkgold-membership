import { ChevronRight } from "lucide-react-native";
import type { LucideIcon } from "lucide-react-native";
import { Pressable, View } from "react-native";

import { Icon } from "@/components/ui/icon";
import { Text } from "@/components/ui/text";

export type ProfileMenuItem = {
  key: string;
  title: string;
  icon: LucideIcon;
};

type ProfileMenuListProps = {
  title?: string;
  items: ProfileMenuItem[];
  onPressItem: (item: ProfileMenuItem) => void;
};

export function ProfileMenuList({ title = "Menu", items, onPressItem }: ProfileMenuListProps) {
  return (
    <View className="gap-2">
      <Text className="text-base font-semibold text-stone-900">{title}</Text>
      <View className="overflow-hidden rounded-2xl border border-stone-100 bg-white">
        {items.map((item, index) => (
          <Pressable
            key={item.key}
            className="flex-row items-center gap-3 px-4 py-3 active:opacity-80"
            onPress={() => onPressItem(item)}>
            <View className="size-8 items-center justify-center rounded-full bg-amber-100">
              <Icon as={item.icon} size={16} className="text-amber-700" />
            </View>
            <Text className="flex-1 text-sm text-stone-800">{item.title}</Text>
            <Icon as={ChevronRight} size={14} className="text-stone-400" />
            {index < items.length - 1 ? (
              <View className="absolute bottom-0 left-14 right-4 h-px bg-stone-100" />
            ) : null}
          </Pressable>
        ))}
      </View>
    </View>
  );
}
