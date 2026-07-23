import React, { useRef } from 'react';
import { View, Text, TextInput, Pressable, Platform } from 'react-native';
import { cn } from '@/lib/utils';

interface OtpInputProps {
  value: string;
  onChangeText: (val: string) => void;
  length?: number;
  editable?: boolean;
}

export function OtpInput({ value, onChangeText, length = 6, editable = true }: OtpInputProps) {
  const inputRef = useRef<TextInput>(null);

  const handlePress = () => {
    if (editable) {
      inputRef.current?.focus();
    }
  };

  const digits = value.split('');
  const boxArray = new Array(length).fill('');

  return (
    <View className="w-full items-center justify-center py-2 px-1">
      <Pressable onPress={handlePress} className="w-full flex-row justify-center gap-1.5 sm:gap-2">
        {boxArray.map((_, index) => {
          const digit = digits[index] || '';
          const isFocused = value.length === index;
          const isLastDigit = index === length - 1;
          const isLastDigitFocused = value.length === length && isLastDigit;
          const isActive = isFocused || isLastDigitFocused;

          return (
            <View
              key={index}
              className={cn(
                'h-12 flex-1 max-w-[44px] items-center justify-center rounded-lg border border-stone-300 bg-white shadow-sm',
                isActive && 'border-[#D1A13B] border-2 shadow-md shadow-[#D1A13B]/10',
                !editable && 'opacity-50 bg-stone-50',
              )}
            >
              <Text className="text-xl font-bold text-stone-800">
                {digit}
              </Text>
            </View>
          );
        })}
      </Pressable>

      {/* Hidden native textinput */}
      <TextInput
        ref={inputRef}
        value={value}
        onChangeText={(text) => {
          const filtered = text.replace(/[^0-9]/g, '').slice(0, length);
          onChangeText(filtered);
        }}
        keyboardType="number-pad"
        maxLength={length}
        editable={editable}
        style={{
          position: 'absolute',
          width: '100%',
          height: '100%',
          opacity: 0,
          zIndex: 1,
        }}
        textContentType="oneTimeCode"
        autoComplete="one-time-code"
        caretHidden
      />
    </View>
  );
}
