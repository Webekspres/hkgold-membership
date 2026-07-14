import { Image } from "expo-image";
import { router, Stack } from "expo-router";
import { Eye, EyeOff } from "lucide-react-native";
import { useState } from "react";
import { Pressable, View } from "react-native";

import {
  AuthField,
  AUTH_INPUT_CLASSNAME,
  AUTH_PLACEHOLDER_COLOR,
} from "@/components/auth/auth-field";
import {
  authLogoStyle,
  AuthScreenShell,
} from "@/components/auth/auth-screen-shell";
import { GoldButton } from "@/components/shared/gold-button";
import { CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Icon } from "@/components/ui/icon";
import { Input } from "@/components/ui/input";
import { LOGO_ASSETS } from "@/config/assets";
import { toast } from "@/lib/sonner";
import { changePassword } from "@/services/auth";

export default function ChangePasswordScreen() {
  const [oldPassword, setOldPassword] = useState("");
  const [newPassword, setNewPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [showOldPassword, setShowOldPassword] = useState(false);
  const [showNewPassword, setShowNewPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleChangePassword() {
    if (isSubmitting) return;

    // Validasi field kosong
    if (!oldPassword || !newPassword || !confirmPassword) {
      toast.error("Semua field wajib diisi", { duration: 4000 });
      return;
    }

    // Validasi panjang password baru
    if (newPassword.length < 8) {
      toast.error("Password baru minimal 8 karakter", { duration: 4000 });
      return;
    }

    // Validasi konfirmasi password
    if (newPassword !== confirmPassword) {
      toast.error("Konfirmasi password tidak sama", { duration: 4000 });
      return;
    }

    // Validasi password baru beda dari lama
    if (oldPassword === newPassword) {
      toast.error("Password baru harus berbeda dari password lama", {
        duration: 4000,
      });
      return;
    }

    // Call API
    setIsSubmitting(true);
    try {
      await changePassword(oldPassword, newPassword);

      toast.success("Password berhasil diubah", { duration: 3000 });
      router.back(); // Kembali ke profil
    } catch (error) {
      toast.error(
        error instanceof Error ? error.message : "Gagal mengubah password",
        { duration: 4000 }
      );
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <>
      <Stack.Screen
        options={{
          title: "",
          headerBackTitle: "Profil",
          headerShown: true,
          headerTransparent: true,
          headerTintColor: "#57534e",
        }}
      />
      <AuthScreenShell scrollable>
        <CardHeader className="items-center gap-4">
          <Image
            source={LOGO_ASSETS.hkgold}
            style={authLogoStyle.logo}
            contentFit="contain"
          />
          <CardTitle className="text-lg text-stone-600">
            Ganti Password
          </CardTitle>
        </CardHeader>
        <CardContent className="gap-4">
          {/* Password Lama */}
          <AuthField label="Password Lama">
            <View className="relative">
              <Input
                className={`${AUTH_INPUT_CLASSNAME} pr-11`}
                placeholder="Password lama Anda"
                placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
                secureTextEntry={!showOldPassword}
                autoComplete="current-password"
                value={oldPassword}
                onChangeText={setOldPassword}
                editable={!isSubmitting}
              />
              <Pressable
                onPress={() => setShowOldPassword((prev) => !prev)}
                className="absolute right-3 top-0 h-11 items-center justify-center"
                hitSlop={8}>
                <Icon
                  as={showOldPassword ? EyeOff : Eye}
                  size={18}
                  className="text-stone-500"
                />
              </Pressable>
            </View>
          </AuthField>

          {/* Password Baru */}
          <AuthField label="Password Baru">
            <View className="relative">
              <Input
                className={`${AUTH_INPUT_CLASSNAME} pr-11`}
                placeholder="Password baru minimal 8 karakter"
                placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
                secureTextEntry={!showNewPassword}
                autoComplete="new-password"
                value={newPassword}
                onChangeText={setNewPassword}
                editable={!isSubmitting}
              />
              <Pressable
                onPress={() => setShowNewPassword((prev) => !prev)}
                className="absolute right-3 top-0 h-11 items-center justify-center"
                hitSlop={8}>
                <Icon
                  as={showNewPassword ? EyeOff : Eye}
                  size={18}
                  className="text-stone-500"
                />
              </Pressable>
            </View>
          </AuthField>

          {/* Konfirmasi Password Baru */}
          <AuthField label="Konfirmasi Password Baru">
            <View className="relative">
              <Input
                className={`${AUTH_INPUT_CLASSNAME} pr-11`}
                placeholder="Ketik ulang password baru"
                placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
                secureTextEntry={!showConfirmPassword}
                autoComplete="new-password"
                value={confirmPassword}
                onChangeText={setConfirmPassword}
                editable={!isSubmitting}
              />
              <Pressable
                onPress={() => setShowConfirmPassword((prev) => !prev)}
                className="absolute right-3 top-0 h-11 items-center justify-center"
                hitSlop={8}>
                <Icon
                  as={showConfirmPassword ? EyeOff : Eye}
                  size={18}
                  className="text-stone-500"
                />
              </Pressable>
            </View>
          </AuthField>

          <GoldButton
            variant="filled"
            width="full"
            label={isSubmitting ? "Menyimpan..." : "Simpan"}
            onPress={() => {
              void handleChangePassword();
            }}
          />
        </CardContent>
      </AuthScreenShell>
    </>
  );
}
