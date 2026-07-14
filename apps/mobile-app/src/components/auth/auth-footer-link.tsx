import { Pressable, View } from 'react-native';

import { Text } from '@/components/ui/text';

type AuthFooterLinkProps = {
  prompt: string;
  linkText: string;
  onPress: () => void;
};

export function AuthFooterLink({ prompt, linkText, onPress }: AuthFooterLinkProps) {
  return (
    <View className="flex-row flex-wrap items-center justify-center gap-1 pt-1">
      <Text variant="small" className="text-stone-600">
        {prompt}
      </Text>
      <Pressable onPress={onPress} className="active:opacity-70">
        <Text variant="small" className="font-semibold text-[#c4841a]">
          {linkText}
        </Text>
      </Pressable>
    </View>
  );
}
