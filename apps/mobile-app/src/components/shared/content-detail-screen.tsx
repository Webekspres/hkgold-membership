import type { ReactElement, ReactNode } from 'react';
import { ScrollView, View } from 'react-native';

import {
  ContentDetailImageSlider,
  type ContentDetailImage,
} from '@/components/shared/content-detail-image-slider';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/layout/screen-layout';

type ContentDetailScreenProps = {
  images: ContentDetailImage[];
  title: string;
  children: ReactNode;
  refreshControl?: ReactElement;
};

export function ContentDetailScreen({
  images,
  title,
  children,
  refreshControl,
}: ContentDetailScreenProps) {
  return (
    <View className="flex-1 bg-background">
      <ScrollView showsVerticalScrollIndicator={false} refreshControl={refreshControl}>
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
