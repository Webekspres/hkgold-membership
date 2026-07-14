const MONTH_NAMES_ID = [
  'Januari',
  'Februari',
  'Maret',
  'April',
  'Mei',
  'Juni',
  'Juli',
  'Agustus',
  'September',
  'Oktober',
  'November',
  'Desember',
] as const;

export function formatEventDateLabel(eventDate: string) {
  const date = new Date(eventDate);

  return {
    day: date.getDate().toString(),
    month: MONTH_NAMES_ID[date.getMonth()],
  };
}
