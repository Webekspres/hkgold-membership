import type { PropsWithChildren } from 'react';
import { View } from 'react-native';

import { Text } from '@/components/ui/text';

type AuthFieldProps = PropsWithChildren<{
  label: string;
  helperText?: string;
}>;

export function AuthField({ label, helperText, children }: AuthFieldProps) {
  return (
    <View className="gap-1.5">
      <Text variant="small" className="text-stone-600">
        {label}
      </Text>
      {children}
      {helperText ? (
        <Text variant="muted" className="text-xs text-stone-500">
          {helperText}
        </Text>
      ) : null}
    </View>
  );
}

export const AUTH_INPUT_CLASSNAME =
  'h-11 rounded-lg border-stone-300 bg-white text-stone-700';

export const AUTH_PLACEHOLDER_COLOR = '#a8a29e';
