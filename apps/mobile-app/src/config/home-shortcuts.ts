import {
  Gift,
  MapPin,
  Newspaper,
  CalendarDays,
  type LucideIcon,
} from "lucide-react-native";

export type HomeShortcutHref = "/cms" | "/events" | "/berita" | "/cabang" | "/reward";

export type HomeShortcut = {
  id: string;
  label: string;
  icon: LucideIcon;
  href: HomeShortcutHref;
};

export const HOME_SHORTCUTS: HomeShortcut[] = [
  {
    id: "event",
    label: "Event",
    icon: CalendarDays,
    href: "/events",
  },
  {
    id: "berita",
    label: "Berita",
    icon: Newspaper,
    href: "/berita",
  },
  {
    id: "cabang",
    label: "Cabang",
    icon: MapPin,
    href: "/cabang",
  },
  {
    id: "reward",
    label: "Reward",
    icon: Gift,
    href: "/reward",
  },
];

export const CMS_HUB_ROUTE = "/cms" as const;
