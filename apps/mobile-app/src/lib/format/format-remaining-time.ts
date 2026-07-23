export function formatRemainingTime(expiresAt: string, now = Date.now()) {
  const remainingMs = new Date(expiresAt).getTime() - now;

  if (remainingMs <= 0) {
    return 'Waktu habis';
  }

  const totalMinutes = Math.ceil(remainingMs / (60 * 1000));

  if (totalMinutes < 1) {
    return 'Kurang dari 1 menit lagi';
  }

  const hours = Math.floor(totalMinutes / 60);
  const minutes = totalMinutes % 60;

  if (hours > 0 && minutes > 0) {
    return `${hours} jam ${minutes} menit lagi`;
  }

  if (hours > 0) {
    return `${hours} jam lagi`;
  }

  return `${minutes} menit lagi`;
}
