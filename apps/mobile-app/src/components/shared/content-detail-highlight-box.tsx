import type { ReactNode } from 'react';
import { View } from 'react-native';

import { Text } from '@/components/ui/text';

type ContentDetailHighlightBoxProps = {
  label: string;
  children: ReactNode;
};

export function ContentDetailHighlightBox({ label, children }: ContentDetailHighlightBoxProps) {
  return (
    <View className="rounded-lg bg-[#fffbeb] px-3 py-3">
      <Text className="text-xs font-semibold uppercase tracking-wide text-[#c4841a]">
        {label}
      </Text>
      {children}
    </View>
  );
}
