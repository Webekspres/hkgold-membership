import { describe, test, expect } from 'bun:test';
import { faqService } from '../services/faq.service';

describe('Faq Module - Get List', () => {
  test('returns array with expected shape', async () => {
    const result = await faqService.getAll();

    expect(Array.isArray(result)).toBe(true);
    result.forEach((item) => {
      expect(typeof item.id).toBe('string');
      expect(typeof item.question).toBe('string');
      expect(typeof item.answer).toBe('string');
    });
  });

  test('returns items ordered by sort_order ascending', async () => {
    const result = await faqService.getAll();

    if (result.length < 2) {
      return;
    }

    const rawItems = await import('../../../db').then(({ prisma }) =>
      prisma.faqItem.findMany({ orderBy: { sortOrder: 'asc' } }),
    );

    expect(result.map((item) => item.id)).toEqual(rawItems.map((item) => item.id));
  });
});
