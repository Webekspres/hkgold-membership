export class FonnteError extends Error {
  constructor(message: string) {
    super(message);
    this.name = 'FonnteError';
  }
}

/** Normalize to Fonnte target format: digits only `62…` (no `+`). */
export function normalizePhoneForFonnte(phone: string): string {
  let cleaned = phone.replace(/\D/g, '');

  if (cleaned.startsWith('08')) {
    cleaned = '62' + cleaned.slice(1);
  }

  if (!cleaned.startsWith('62')) {
    throw new FonnteError('Nomor HP harus format Indonesia (+62 atau 08)');
  }

  if (cleaned.length < 11 || cleaned.length > 14) {
    throw new FonnteError('Nomor HP tidak valid');
  }

  return cleaned;
}

export class FonnteService {
  async sendWhatsappMessage(phone: string, message: string): Promise<void> {
    const target = normalizePhoneForFonnte(phone);
    const token = process.env.FONNTE_TOKEN;
    const baseUrl = (process.env.FONNTE_BASE_URL ?? 'https://api.fonnte.com').replace(
      /\/$/,
      '',
    );

    if (!token) {
      throw new FonnteError('FONNTE_TOKEN belum dikonfigurasi');
    }

    const body = new URLSearchParams({
      target,
      message,
      countryCode: '62',
    });

    let response: Response;
    try {
      response = await fetch(`${baseUrl}/send`, {
        method: 'POST',
        headers: {
          Authorization: token,
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body,
      });
    } catch (error) {
      const detail = error instanceof Error ? error.message : 'network error';
      throw new FonnteError(`Gagal menghubungi Fonnte: ${detail}`);
    }

    const raw = await response.text();
    let payload: { status?: boolean; reason?: string; detail?: string } = {};
    try {
      payload = JSON.parse(raw) as typeof payload;
    } catch {
      // non-JSON body
    }

    if (!response.ok || payload.status === false) {
      const reason =
        payload.reason ?? payload.detail ?? raw.slice(0, 200) ?? response.statusText;
      throw new FonnteError(`Fonnte menolak kirim WA: ${reason}`);
    }
  }
}

export const fonnteService = new FonnteService();
