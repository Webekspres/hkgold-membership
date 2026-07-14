import { Image } from "expo-image";
import { router } from "expo-router";
import { Eye, EyeOff } from "lucide-react-native";
import { useState } from "react";
import { Pressable, View } from "react-native";

import {
  AuthField,
  AUTH_INPUT_CLASSNAME,
  AUTH_PLACEHOLDER_COLOR,
} from "@/components/auth/auth-field";
import { AuthFooterLink } from "@/components/auth/auth-footer-link";
import {
  authLogoStyle,
  AuthScreenShell,
} from "@/components/auth/auth-screen-shell";
import { GoldButton } from "@/components/shared/gold-button";
import { CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Icon } from "@/components/ui/icon";
import { Input } from "@/components/ui/input";
import { useAuth } from "@/hooks/use-auth";
import { toast } from "@/lib/sonner";

export default function RegisterScreen() {
  const { register } = useAuth();
  const [phoneNumber, setPhoneNumber] = useState("");
  const [email, setEmail] = useState("");
  const [fullName, setFullName] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleRegister() {
    if (isSubmitting) return;

    const trimmedPhone = phoneNumber.trim();
    const trimmedEmail = email.trim();
    const trimmedName = fullName.trim();

    if (!trimmedPhone || !trimmedEmail || !trimmedName || !password) {
      toast.error("Semua field wajib diisi", { duration: 4000 });
      return;
    }

    if (password.length < 8) {
      toast.error("Password minimal 8 karakter", { duration: 4000 });
      return;
    }

    if (password !== confirmPassword) {
      toast.error("Konfirmasi password tidak cocok", { duration: 4000 });
      return;
    }

    setIsSubmitting(true);
    try {
      await register({
        email: trimmedEmail,
        fullName: trimmedName,
        phoneNumber: trimmedPhone,
        password,
      });
      toast.success("Akun berhasil dibuat", { duration: 3000 });
      router.replace("/(tabs)");
    } catch (error) {
      toast.error(
        error instanceof Error ? error.message : "Registrasi gagal",
        { duration: 4000 }
      );
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <AuthScreenShell scrollable>
      <CardHeader className="items-center gap-4">
        <Image
          source={require("@/assets/logo/logo-hkgold.webp")}
          style={authLogoStyle.logo}
          contentFit="contain"
        />
        <CardTitle className="text-lg text-stone-600">Daftar</CardTitle>
      </CardHeader>
      <CardContent className="gap-4">
        <AuthField label="Nomor Telepon">
          <Input
            className={AUTH_INPUT_CLASSNAME}
            placeholder="+62 812-xxxx-xxxx"
            placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
            keyboardType="phone-pad"
            autoComplete="tel"
            value={phoneNumber}
            onChangeText={setPhoneNumber}
            editable={!isSubmitting}
          />
        </AuthField>

        <AuthField label="Email">
          <Input
            className={AUTH_INPUT_CLASSNAME}
            placeholder="email@example.com"
            placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
            keyboardType="email-address"
            autoCapitalize="none"
            autoComplete="email"
            value={email}
            onChangeText={setEmail}
            editable={!isSubmitting}
          />
        </AuthField>

        <AuthField label="Nama Lengkap">
          <Input
            className={AUTH_INPUT_CLASSNAME}
            placeholder="Nama lengkap"
            placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
            autoCapitalize="words"
            autoComplete="name"
            value={fullName}
            onChangeText={setFullName}
            editable={!isSubmitting}
          />
        </AuthField>

        <AuthField label="Password">
          <View className="relative">
            <Input
              className={`${AUTH_INPUT_CLASSNAME} pr-11`}
              placeholder="Password"
              placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
              secureTextEntry={!showPassword}
              autoComplete="new-password"
              value={password}
              onChangeText={setPassword}
              editable={!isSubmitting}
            />
            <Pressable
              onPress={() => setShowPassword((prev) => !prev)}
              className="absolute right-3 top-0 h-11 items-center justify-center"
              hitSlop={8}
            >
              <Icon
                as={showPassword ? EyeOff : Eye}
                size={18}
                className="text-stone-500"
              />
            </Pressable>
          </View>
        </AuthField>

        <AuthField label="Konfirmasi Password">
          <View className="relative">
            <Input
              className={`${AUTH_INPUT_CLASSNAME} pr-11`}
              placeholder="Ulangi password"
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
              hitSlop={8}
            >
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
          label={isSubmitting ? "Memproses..." : "Daftar"}
          onPress={() => {
            void handleRegister();
          }}
        />

        <AuthFooterLink
          prompt="Sudah punya akun?"
          linkText="Masuk di sini"
          onPress={() => router.push("/login")}
        />
      </CardContent>
    </AuthScreenShell>
  );
}
