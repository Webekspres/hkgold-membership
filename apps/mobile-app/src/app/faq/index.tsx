import { SymbolView } from 'expo-symbols';
import { router } from 'expo-router';
import { HelpCircle } from 'lucide-react-native';
import { ActivityIndicator, Pressable, ScrollView, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { FaqAccordionList } from '@/components/faq/faq-accordion-list';
import { Button } from '@/components/ui/button';
import { Icon } from '@/components/ui/icon';
import { Text } from '@/components/ui/text';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/layout/screen-layout';
import { useFaq } from '@/hooks/use-faq';

const BACK_ICON = { ios: 'chevron.left', android: 'arrow_back', web: 'arrow_back' } as const;

export default function FaqScreen() {
  const { items, isLoading, isError, refetch } = useFaq();

  return (
    <View className="flex-1 bg-background">
      <SafeAreaView className="flex-1" edges={['top']}>
        <View
          className="flex-row items-center gap-3 border-b border-stone-200 bg-background py-3"
          style={{ paddingHorizontal: SCREEN_HORIZONTAL_PADDING }}>
          <Button variant="outline" size="icon" onPress={() => router.back()}>
            <SymbolView name={BACK_ICON} size={20} tintColor="#44403c" />
          </Button>
          <Text className="text-lg font-semibold text-stone-900">FAQ</Text>
        </View>

        {isLoading ? (
          <View className="flex-1 items-center justify-center">
            <ActivityIndicator color="#b45309" />
          </View>
        ) : isError ? (
          <View className="flex-1 items-center justify-center gap-3 px-6">
            <Text className="text-center text-base font-semibold text-stone-900">
              Gagal memuat FAQ
            </Text>
            <Text variant="muted" className="text-center">
              Periksa koneksi internet Anda lalu coba lagi.
            </Text>
            <Pressable onPress={() => void refetch()} className="active:opacity-70">
              <Text className="font-semibold text-[#c4841a]">Coba lagi</Text>
            </Pressable>
          </View>
        ) : (
          <ScrollView
            showsVerticalScrollIndicator={false}
            contentContainerStyle={{
              paddingHorizontal: SCREEN_HORIZONTAL_PADDING,
              paddingVertical: 16,
              paddingBottom: 24,
              gap: 16,
            }}>
            <View className="items-center gap-2 rounded-2xl border border-amber-100 bg-[#fffbeb] px-4 py-5">
              <View className="size-12 items-center justify-center rounded-full bg-amber-100">
                <Icon as={HelpCircle} size={24} className="text-amber-700" />
              </View>
              <Text className="text-center text-base font-semibold text-stone-900">
                Pertanyaan Umum
              </Text>
              <Text className="text-center text-sm leading-5 text-stone-600">
                Temukan jawaban seputar program HK GOLD VIP, poin, redeem, dan keanggotaan Anda.
              </Text>
            </View>

            {items.length === 0 ? (
              <Text variant="muted" className="text-center">
                Belum ada FAQ.
              </Text>
            ) : (
              <FaqAccordionList items={items} />
            )}
          </ScrollView>
        )}
      </SafeAreaView>
    </View>
  );
}
