import { router } from 'expo-router';

import { AuthCardHeader } from '@/components/auth/auth-card-header';
import { ForgotPasswordFlow } from '@/components/auth/forgot-password-flow';
import { AuthScreenShell } from '@/components/auth/auth-screen-shell';
import { AuthFooterLink } from '@/components/auth/auth-footer-link';
import { CardContent } from '@/components/ui/card';

export default function ForgotPasswordScreen() {
  return (
    <AuthScreenShell scrollable>
      <AuthCardHeader title="Lupa Password" />
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
