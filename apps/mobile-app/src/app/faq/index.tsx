import { SymbolView } from 'expo-symbols';
import { router } from 'expo-router';
import { HelpCircle } from 'lucide-react-native';
import { ScrollView, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { FaqAccordionList } from '@/components/faq/faq-accordion-list';
import { Button } from '@/components/ui/button';
import { Icon } from '@/components/ui/icon';
import { Text } from '@/components/ui/text';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/layout/screen-layout';
import { getFaqList } from '@/services/faq';

const BACK_ICON = { ios: 'chevron.left', android: 'arrow_back', web: 'arrow_back' } as const;

const faqList = getFaqList();

export default function FaqScreen() {
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

          <FaqAccordionList items={faqList} />
        </ScrollView>
      </SafeAreaView>
    </View>
  );
}
