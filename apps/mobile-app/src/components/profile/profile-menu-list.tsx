import { ChevronRight } from "lucide-react-native";
import type { LucideIcon } from "lucide-react-native";
import { Pressable, View } from "react-native";

import { GoldCircleIcon } from "@/components/shared/gold-circle-icon";
import { Icon } from "@/components/ui/icon";
import { Text } from "@/components/ui/text";
import { cn } from "@/lib/utils";

export type ProfileMenuItem = {
  key: string;
  title: string;
  icon: LucideIcon;
  /** Merah — dipakai menu destructive seperti Logout */
  destructive?: boolean;
};

export type ProfileMenuSection = {
  key: string;
  title: string;
  items: ProfileMenuItem[];
};

type ProfileMenuListProps = {
  sections: ProfileMenuSection[];
  onPressItem: (item: ProfileMenuItem) => void;
};

export function ProfileMenuList({ sections, onPressItem }: ProfileMenuListProps) {
  return (
    <View className="gap-5">
      {sections.map((section) => (
        <View key={section.key} className="gap-2">
          <Text className="text-base font-bold text-stone-900">{section.title}</Text>
          <View>
            {section.items.map((item, index) => (
              <Pressable
                key={item.key}
                className="flex-row items-center gap-3 rounded-xl py-2.5 active:opacity-70"
                onPress={() => onPressItem(item)}
                accessibilityRole="button"
                accessibilityLabel={item.title}
              >
                <GoldCircleIcon
                  icon={item.icon}
                  destructive={item.destructive}
                  circleClassName="size-9"
                  iconClassName="size-4 text-white"
                />
                <Text
                  className={cn(
                    "flex-1 text-sm font-medium",
                    item.destructive ? "text-red-600" : "text-stone-800",
                  )}
                >
                  {item.title}
                </Text>
                <Icon as={ChevronRight} size={14} className="text-stone-400" />
                {index < section.items.length - 1 ? (
                  <View className="absolute bottom-0 left-12 right-0 h-px bg-stone-100" />
                ) : null}
              </Pressable>
            ))}
          </View>
        </View>
      ))}
    </View>
  );
}
