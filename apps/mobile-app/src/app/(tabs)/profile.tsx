import { router, type Href } from "expo-router";
import {
  BellRing,
  Crown,
  Gift,
  HelpCircle,
  History,
  LogOut,
  MapPin,
  Newspaper,
  Settings,
} from "lucide-react-native";
import { useMemo, useState } from "react";
import { ScrollView, View } from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";
import dayjs from "dayjs";
import "dayjs/locale/id";

import { ProfileLastRewardCard } from "@/components/profile/profile-last-reward-card";
import { ProfileMemberCard } from "@/components/profile/profile-member-card";
import { ProfileMenuList, type ProfileMenuItem } from "@/components/profile/profile-menu-list";
import { ProfilePointsTierCard } from "@/components/profile/profile-points-tier-card";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Text } from "@/components/ui/text";
import { useAuth } from "@/hooks/use-auth";
import { useMyProfile } from "@/hooks/use-my-profile";
import { copyMemberCode } from "@/lib/clipboard/copy-member-code";
import { toast } from "@/lib/sonner";
import { getRewardList } from "@/services/rewards";

dayjs.locale("id");

const LAST_REDEEMED_REWARD = getRewardList()[0];

const profileMenus: ProfileMenuItem[] = [
  { key: "redeem-history", title: "Riwayat Redeem", icon: History },
  { key: "reward-catalog", title: "Katalog Reward", icon: Gift },
  { key: "tier-benefit", title: "Tier Benefit", icon: Crown },
  { key: "event", title: "Event", icon: BellRing },
  { key: "news", title: "Berita", icon: Newspaper },
  { key: "branch-location", title: "Lokasi Cabang", icon: MapPin },
  { key: "faq", title: "FAQ", icon: HelpCircle },
  { key: "account-settings", title: "Pengaturan Akun", icon: Settings },
  { key: "logout", title: "Logout", icon: LogOut, destructive: true },
];

export default function ProfileScreen() {
  const { logout } = useAuth();
  const { card } = useMyProfile();
  const [logoutOpen, setLogoutOpen] = useState(false);
  const [isLoggingOut, setIsLoggingOut] = useState(false);

  const birthDateLabel = useMemo(() => {
    if (!card?.birthDate) return null;
    const parsed = dayjs(card.birthDate);
    return parsed.isValid() ? parsed.format("D MMMM YYYY") : null;
  }, [card?.birthDate]);

  function handlePressProfileMenu(item: ProfileMenuItem) {
    switch (item.key) {
      case "logout":
        setLogoutOpen(true);
        return;
      case "redeem-history":
        router.push("/redeem" as Href);
        return;
      case "reward-catalog":
        router.push("/reward");
        return;
      case "event":
        router.push("/events");
        return;
      case "news":
        router.push("/berita");
        return;
      case "branch-location":
        router.push("/cabang");
        return;
      case "faq":
        router.push("/faq" as Href);
        return;
      case "tier-benefit":
        router.push("/tier-benefit" as Href);
        return;
      default:
        router.push("/cms");
    }
  }

  async function handleConfirmLogout() {
    if (isLoggingOut) return;
    setIsLoggingOut(true);
    try {
      await logout();
      setLogoutOpen(false);
      toast.success("Berhasil keluar", { duration: 3000 });
      router.replace("/login");
    } catch {
      toast.error("Gagal keluar. Coba lagi.", { duration: 4000 });
    } finally {
      setIsLoggingOut(false);
    }
  }

  return (
    <View className="flex-1 bg-background">
      <SafeAreaView className="flex-1" style={{ paddingBottom: 4 }}>
        <ScrollView
          className="flex-1"
          contentContainerClassName="gap-4 px-4 pb-8 pt-4"
          showsVerticalScrollIndicator={false}>
          {card ? (
            <>
              <ProfileMemberCard
                fullName={card.fullName}
                memberCode={card.memberNumber}
                avatarUri={card.avatarUri}
                avatarFallback={card.avatarFallback}
                birthDateLabel={birthDateLabel}
                onPressMemberCode={() => void copyMemberCode(card.memberNumber)}
              />

              <ProfilePointsTierCard
                points={card.pointBalance}
                tierName={card.tierLabel}
              />
            </>
          ) : null}

          <ProfileLastRewardCard reward={LAST_REDEEMED_REWARD} onPress={() => router.push("/cms")} />

          <ProfileMenuList items={profileMenus} onPressItem={handlePressProfileMenu} />
        </ScrollView>
      </SafeAreaView>

      <Dialog open={logoutOpen} onOpenChange={setLogoutOpen}>
        <DialogContent className="gap-4">
          <DialogHeader>
            <DialogTitle>Keluar?</DialogTitle>
            <DialogDescription>
              Anda yakin ingin keluar dari akun?
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button
              variant="outline"
              disabled={isLoggingOut}
              onPress={() => setLogoutOpen(false)}>
              <Text>Batal</Text>
            </Button>
            <Button
              variant="destructive"
              disabled={isLoggingOut}
              onPress={() => {
                void handleConfirmLogout();
              }}>
              <Text>{isLoggingOut ? "Keluar..." : "Keluar"}</Text>
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </View>
  );
}
