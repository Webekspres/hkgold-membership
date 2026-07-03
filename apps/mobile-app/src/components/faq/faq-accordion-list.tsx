import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion';
import { Text } from '@/components/ui/text';
import type { FaqItem } from '@/types/faq';
import { View } from 'react-native';

type FaqAccordionListProps = {
  items: FaqItem[];
};

export function FaqAccordionList({ items }: FaqAccordionListProps) {
  return (
    <View className="overflow-hidden rounded-2xl border border-stone-100 bg-white">
      <Accordion type="single" collapsible>
        {items.map((item, index) => (
          <AccordionItem
            key={item.id}
            value={item.id}
            className={index < items.length - 1 ? 'border-stone-100' : 'border-b-0'}>
            <AccordionTrigger className="px-4 py-3.5 active:opacity-80">
              <Text className="flex-1 pr-2 text-sm font-semibold leading-5 text-stone-900">
                {item.question}
              </Text>
            </AccordionTrigger>
            <AccordionContent className="px-4 pb-4 pt-0">
              <Text className="text-sm leading-6 text-stone-600">{item.answer}</Text>
            </AccordionContent>
          </AccordionItem>
        ))}
      </Accordion>
    </View>
  );
}
