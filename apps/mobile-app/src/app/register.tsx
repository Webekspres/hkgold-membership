import { Image } from 'expo-image';
import { router } from 'expo-router';

import { AuthField, AUTH_INPUT_CLASSNAME, AUTH_PLACEHOLDER_COLOR } from '@/components/auth/auth-field';
import { AuthFooterLink } from '@/components/auth/auth-footer-link';
import { authLogoStyle, AuthScreenShell } from '@/components/auth/auth-screen-shell';
import { GoldButton } from '@/components/gold-button';
import { CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

export default function RegisterScreen() {
  return (
    <AuthScreenShell scrollable>
      <CardHeader className="items-center gap-4">
        <Image
          source={require('@/assets/logo/logo-hkgold.webp')}
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
          />
        </AuthField>

        <AuthField label="Nama Lengkap">
          <Input
            className={AUTH_INPUT_CLASSNAME}
            placeholder="Nama lengkap"
            placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
            autoCapitalize="words"
            autoComplete="name"
          />
        </AuthField>

        <AuthField label="Password">
          <Input
            className={AUTH_INPUT_CLASSNAME}
            placeholder="Password"
            placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
            secureTextEntry
            autoComplete="new-password"
          />
        </AuthField>

        <AuthField label="Konfirmasi Password">
          <Input
            className={AUTH_INPUT_CLASSNAME}
            placeholder="Ulangi password"
            placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
            secureTextEntry
            autoComplete="new-password"
          />
        </AuthField>

        <GoldButton
          variant="filled"
          width="full"
          label="Daftar"
          onPress={() => router.replace('/(tabs)')}
        />

        <AuthFooterLink
          prompt="Sudah punya akun?"
          linkText="Masuk di sini"
          onPress={() => router.push('/login')}
        />
      </CardContent>
    </AuthScreenShell>
  );
}
