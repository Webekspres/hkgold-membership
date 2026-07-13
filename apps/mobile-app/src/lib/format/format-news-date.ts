import dayjs from 'dayjs';
import 'dayjs/locale/id';

dayjs.locale('id');

export function formatNewsDate(isoDate: string) {
  return dayjs(isoDate).format('D MMM YYYY');
}
