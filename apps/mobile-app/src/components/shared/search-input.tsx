import { SymbolView } from 'expo-symbols';
import { Pressable, View } from 'react-native';

import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

const CLEAR_ICON = {
  ios: 'xmark.circle.fill',
  android: 'cancel',
  web: 'cancel',
} as const;

type SearchInputProps = {
  value: string;
  onChangeText: (text: string) => void;
  placeholder?: string;
  className?: string;
};

export function SearchInput({
  value,
  onChangeText,
  placeholder,
  className,
}: SearchInputProps) {
  const hasValue = value.length > 0;

  return (
    <View className={cn('relative min-w-0 flex-1', className)}>
      <Input
        className={cn('min-w-0 flex-1', hasValue && 'pr-10')}
        placeholder={placeholder}
        placeholderTextColor="#a8a29e"
        value={value}
        onChangeText={onChangeText}
        autoCorrect={false}
        autoCapitalize="none"
        returnKeyType="search"
      />
      {hasValue ? (
        <Pressable
          accessibilityRole="button"
          accessibilityLabel="Hapus pencarian"
          hitSlop={8}
          className="absolute right-2 top-0 bottom-0 items-center justify-center px-1 active:opacity-70"
          onPress={() => onChangeText('')}>
          <SymbolView name={CLEAR_ICON} size={20} tintColor="#a8a29e" />
        </Pressable>
      ) : null}
    </View>
  );
}
