import { describe, test, expect } from 'bun:test';
import sharp from 'sharp';
import { compressToWebp, keyFromUrl } from '../services/media.service';

describe('Media helpers - keyFromUrl + compressToWebp', () => {
  test('keyFromUrl strips publicUrl prefix including folder path', () => {
    const publicUrl = 'http://127.0.0.1:9002/mala-emas-media';
    expect(keyFromUrl(`${publicUrl}/member/photo/x.webp`, publicUrl)).toBe(
      'member/photo/x.webp'
    );
  });

  test('keyFromUrl handles trailing slash on publicUrl', () => {
    const publicUrl = 'http://127.0.0.1:9002/mala-emas-media/';
    expect(keyFromUrl(`${publicUrl}member/photo/y.webp`, publicUrl)).toBe(
      'member/photo/y.webp'
    );
  });

  test('compressToWebp yields webp buffer with dimensions <= 512', async () => {
    const jpeg = await sharp({
      create: {
        width: 1200,
        height: 800,
        channels: 3,
        background: { r: 200, g: 100, b: 50 }
      }
    })
      .jpeg()
      .toBuffer();

    const out = await compressToWebp(jpeg, 512, 80);
    const meta = await sharp(out).metadata();

    expect(meta.format).toBe('webp');
    expect(meta.width).toBeLessThanOrEqual(512);
    expect(meta.height).toBeLessThanOrEqual(512);
    expect(meta.width).toBe(512);
    expect(meta.height).toBe(512);
  });
});
