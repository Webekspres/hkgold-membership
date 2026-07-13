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

export default function LoginScreen() {
  const { login } = useAuth();
  const [identifier, setIdentifier] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleLogin() {
    if (isSubmitting) return;

    const trimmedIdentifier = identifier.trim();
    if (!trimmedIdentifier || !password) {
      toast.error("Nomor HP/email/member dan password wajib diisi", {
        duration: 4000,
      });
      return;
    }

    setIsSubmitting(true);
    try {
      await login(trimmedIdentifier, password);
      router.replace("/(tabs)");
    } catch (error) {
      toast.error(
        error instanceof Error ? error.message : "Login gagal",
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
        <CardTitle className="text-lg text-stone-600">Masuk</CardTitle>
      </CardHeader>
      <CardContent className="gap-4">
        <AuthField label="Nomor HP, Email, atau Nomor Member">
          <Input
            className={AUTH_INPUT_CLASSNAME}
            placeholder="081234xxxx / email@... / HKA0000001"
            placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
            autoCapitalize="none"
            autoCorrect={false}
            value={identifier}
            onChangeText={setIdentifier}
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
              autoComplete="password"
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

        <GoldButton
          variant="filled"
          width="full"
          label={isSubmitting ? "Memproses..." : "Masuk"}
          onPress={() => {
            void handleLogin();
          }}
        />

        <AuthFooterLink
          prompt="Belum punya akun?"
          linkText="Daftar sekarang"
          onPress={() => router.push("/register")}
        />
      </CardContent>
    </AuthScreenShell>
  );
}
