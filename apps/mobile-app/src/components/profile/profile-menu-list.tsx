import { LinearGradient } from "expo-linear-gradient";
import { ChevronRight } from "lucide-react-native";
import type { LucideIcon } from "lucide-react-native";
import { cssInterop } from "nativewind";
import { Platform, Pressable, View } from "react-native";

import { Icon } from "@/components/ui/icon";
import { Text } from "@/components/ui/text";
import {
  GOLD_GRADIENT_COLORS,
  GOLD_GRADIENT_SHORTCUT_END,
  GOLD_GRADIENT_SHORTCUT_START,
} from "@/config/brand";
import { cn } from "@/lib/utils";

cssInterop(LinearGradient, { className: "style" });

const MENU_ICON_SIZE = 36;
const MENU_ICON_INNER = 16;

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

function MenuIconBadge({
  icon,
  destructive,
}: {
  icon: LucideIcon;
  destructive?: boolean;
}) {
  if (destructive) {
    return (
      <View className="size-9 items-center justify-center rounded-full bg-red-100">
        <Icon as={icon} size={MENU_ICON_INNER} className="text-red-600" />
      </View>
    );
  }

  return (
    <View
      className="rounded-full shadow-sm shadow-amber-700/30"
      style={Platform.OS === "android" ? { elevation: 4 } : undefined}
    >
      <LinearGradient
        colors={[...GOLD_GRADIENT_COLORS]}
        start={GOLD_GRADIENT_SHORTCUT_START}
        end={GOLD_GRADIENT_SHORTCUT_END}
        className="items-center justify-center rounded-full"
        style={{
          height: MENU_ICON_SIZE,
          width: MENU_ICON_SIZE,
          borderRadius: MENU_ICON_SIZE / 2,
        }}
      >
        <Icon as={icon} size={MENU_ICON_INNER} className="text-white" />
      </LinearGradient>
    </View>
  );
}

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
                <MenuIconBadge icon={item.icon} destructive={item.destructive} />
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
