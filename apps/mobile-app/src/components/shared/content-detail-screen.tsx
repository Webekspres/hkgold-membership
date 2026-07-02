import type { ReactNode } from 'react';
import { ScrollView, View } from 'react-native';

import { ContentDetailImageSlider } from '@/components/shared/content-detail-image-slider';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/layout/screen-layout';

type ContentDetailScreenProps = {
  images: number[];
  title: string;
  children: ReactNode;
};

export function ContentDetailScreen({ images, title, children }: ContentDetailScreenProps) {
  return (
    <View className="flex-1 bg-background">
      <ScrollView showsVerticalScrollIndicator={false}>
        <ContentDetailImageSlider images={images} title={title} />

        <View
          className="gap-5 py-5"
          style={{ paddingHorizontal: SCREEN_HORIZONTAL_PADDING }}>
          {children}
        </View>
      </ScrollView>
    </View>
  );
}
