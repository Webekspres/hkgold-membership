import { LinearGradient } from 'expo-linear-gradient';
import { Pressable, View } from 'react-native';
import QRCode from 'react-native-qrcode-svg';

import { Text } from '@/components/ui/text';
import {
  GOLD_GRADIENT_COLORS,
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
} from '@/config/brand';

type QrCodeCardProps = {
  value: string;
  label: string;
  onPressLabel?: () => void;
  copyAccessibilityLabel?: string;
};

export function QrCodeCard({
  value,
  label,
  onPressLabel,
  copyAccessibilityLabel = 'Salin kode',
}: QrCodeCardProps) {
  return (
    <LinearGradient
      colors={[...GOLD_GRADIENT_COLORS]}
      start={GOLD_GRADIENT_START}
      end={GOLD_GRADIENT_END}
      style={{ borderRadius: 20, padding: 2 }}>
      <View className="items-center rounded-[18px] bg-white px-5 py-6">
        <View className="w-full max-w-[240px] items-center">
          <View className="aspect-square w-full items-center justify-center rounded-2xl border border-stone-100 bg-white p-4">
            <QRCode value={value} size={200} backgroundColor="#ffffff" color="#1c1917" />
          </View>
          {onPressLabel ? (
            <Pressable
              className="mt-3 rounded-full bg-stone-100 px-3 py-1 active:opacity-80"
              onPress={onPressLabel}
              accessibilityRole="button"
              accessibilityLabel={copyAccessibilityLabel}>
              <Text variant="small" className="text-stone-700">
                {label}
              </Text>
            </Pressable>
          ) : (
            <Text variant="small" className="mt-3 text-center text-stone-600">
              {label}
            </Text>
          )}
        </View>
      </View>
    </LinearGradient>
  );
}
