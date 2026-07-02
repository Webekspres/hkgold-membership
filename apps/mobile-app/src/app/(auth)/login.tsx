import { Image } from "expo-image";
import { router } from "expo-router";

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
import { Input } from "@/components/ui/input";

export default function LoginScreen() {
  return (
    <AuthScreenShell>
      <CardHeader className="items-center gap-4">
        <Image
          source={require("@/assets/logo/logo-hkgold.webp")}
          style={authLogoStyle.logo}
          contentFit="contain"
        />
        <CardTitle className="text-lg text-stone-600">Masuk</CardTitle>
      </CardHeader>
      <CardContent className="gap-4">
        <AuthField label="Email">
          <Input
            className={AUTH_INPUT_CLASSNAME}
            placeholder="email@example.com"
            placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
            keyboardType="email-address"
            autoCapitalize="none"
            autoComplete="email"
          />
        </AuthField>

        <AuthField label="Password">
          <Input
            className={AUTH_INPUT_CLASSNAME}
            placeholder="Password"
            placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
            secureTextEntry
            autoComplete="password"
          />
        </AuthField>

        <GoldButton
          variant="filled"
          width="full"
          label="Masuk"
          onPress={() => router.replace("/(tabs)")}
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
