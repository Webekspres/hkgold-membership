import { Image } from 'expo-image';
import { View } from 'react-native';

import { authLogoStyle } from '@/components/auth/auth-screen-shell';
import { CardHeader, CardTitle } from '@/components/ui/card';
import { Text } from '@/components/ui/text';
import { LOGO_ASSETS } from '@/config/assets';

type AuthCardHeaderProps = {
  title: string;
};

/** Shared auth card header — match login / backoffice mobile. */
export function AuthCardHeader({ title }: AuthCardHeaderProps) {
  return (
    <CardHeader className="items-center gap-3">
      <Image
        source={LOGO_ASSETS.icon}
        style={authLogoStyle.icon}
        contentFit="contain"
      />
      <View className="items-center gap-1">
        <Text className="text-sm font-bold tracking-wide text-[#d9a838]">
          Portal HK GOLD VIP
        </Text>
        <CardTitle className="text-[26px] font-extrabold text-stone-900">
          {title}
        </CardTitle>
      </View>
    </CardHeader>
  );
}
