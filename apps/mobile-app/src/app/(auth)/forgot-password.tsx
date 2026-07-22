import { Image } from 'expo-image';
import { router } from 'expo-router';

import { ForgotPasswordFlow } from '@/components/auth/forgot-password-flow';
import {
  authLogoStyle,
  AuthScreenShell,
} from '@/components/auth/auth-screen-shell';
import { AuthFooterLink } from '@/components/auth/auth-footer-link';
import { CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { LOGO_ASSETS } from '@/config/assets';

export default function ForgotPasswordScreen() {
  return (
    <AuthScreenShell scrollable>
      <CardHeader className="items-center gap-4">
        <Image
          source={LOGO_ASSETS.hkgold}
          style={authLogoStyle.logo}
          contentFit="contain"
        />
        <CardTitle className="text-lg text-stone-600">Lupa Password</CardTitle>
      </CardHeader>
      <CardContent className="gap-4">
        <ForgotPasswordFlow
          mode="public"
          onSuccess={() => {
            router.replace('/login');
          }}
        />
        <AuthFooterLink
          prompt="Ingat password Anda?"
          linkText="Kembali masuk"
          onPress={() => router.replace('/login')}
        />
      </CardContent>
    </AuthScreenShell>
  );
}
