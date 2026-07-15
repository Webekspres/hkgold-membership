import { describe, test, expect, beforeEach, afterAll } from 'bun:test';
import {
  FonnteError,
  fonnteService,
  normalizePhoneForFonnte,
} from '../services/fonnte.service';

describe('FonnteService', () => {
  const originalFetch = globalThis.fetch;
  const originalToken = process.env.FONNTE_TOKEN;
  const originalBase = process.env.FONNTE_BASE_URL;

  beforeEach(() => {
    process.env.FONNTE_TOKEN = 'test-fonnte-token';
    process.env.FONNTE_BASE_URL = 'https://api.fonnte.com';
    globalThis.fetch = (async () =>
      new Response(JSON.stringify({ status: true, detail: 'ok' }), {
        status: 200,
        headers: { 'Content-Type': 'application/json' },
      })) as typeof fetch;
  });

  afterAll(() => {
    globalThis.fetch = originalFetch;
    if (originalToken === undefined) delete process.env.FONNTE_TOKEN;
    else process.env.FONNTE_TOKEN = originalToken;
    if (originalBase === undefined) delete process.env.FONNTE_BASE_URL;
    else process.env.FONNTE_BASE_URL = originalBase;
  });

  test('normalizePhoneForFonnte: 08xxxx → 62xxxx', () => {
    expect(normalizePhoneForFonnte('081234567890')).toBe('6281234567890');
    expect(normalizePhoneForFonnte('+62 812-3456-7890')).toBe('6281234567890');
  });

  test('normalizePhoneForFonnte: rejects non-ID and bad length', () => {
    expect(() => normalizePhoneForFonnte('+15550100')).toThrow(FonnteError);
    expect(() => normalizePhoneForFonnte('0812')).toThrow(FonnteError);
    expect(() => normalizePhoneForFonnte('08123456789012345')).toThrow(
      FonnteError,
    );
  });

  test('missing FONNTE_TOKEN throws', async () => {
    delete process.env.FONNTE_TOKEN;
    try {
      await fonnteService.sendWhatsappMessage('081234567890', 'hello');
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(FonnteError);
      expect((error as FonnteError).message).toContain('FONNTE_TOKEN');
    }
  });

  test('status:false response throws with detail', async () => {
    globalThis.fetch = (async () =>
      new Response(JSON.stringify({ status: false, reason: 'quota exceeded' }), {
        status: 200,
        headers: { 'Content-Type': 'application/json' },
      })) as typeof fetch;

    try {
      await fonnteService.sendWhatsappMessage('081234567890', 'hello');
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(FonnteError);
      expect((error as FonnteError).message).toContain('quota exceeded');
    }
  });

  test('HTTP non-OK throws', async () => {
    globalThis.fetch = (async () =>
      new Response('server error', {
        status: 500,
        statusText: 'Internal Server Error',
      })) as typeof fetch;

    try {
      await fonnteService.sendWhatsappMessage('081234567890', 'hello');
      expect(true).toBe(false);
    } catch (error) {
      expect(error).toBeInstanceOf(FonnteError);
    }
  });
});
