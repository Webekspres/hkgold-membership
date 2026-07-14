import { DateRangeFilterModal } from '@/components/shared/date-range-filter-modal';
import type { DateRange } from '@/lib/date-range-filter';

type EventFilterModalProps = {
  visible: boolean;
  range: DateRange;
  onRangeChange: (range: DateRange) => void;
  onClose: () => void;
  onApply: () => void;
  onReset: () => void;
};

export function EventFilterModal(props: EventFilterModalProps) {
  return (
    <DateRangeFilterModal
      {...props}
      title="Filter Event"
      description="Pilih rentang tanggal event"
    />
  );
}
