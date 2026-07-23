import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { Elysia } from 'elysia';
import { prisma } from '../../../db';
import { faqRoutes } from '../routes/faq.routes';
import { faqService } from '../services/faq.service';

const app = new Elysia().use(faqRoutes);

describe('Faq Module - Get List', () => {
  const suffix = Date.now().toString().slice(-6);
  const createdIds: string[] = [];

  beforeAll(async () => {
    // Insert out of natural order to prove sortOrder drives response order.
    const items = [
      { question: `FAQ C ${suffix}`, answer: `Answer C ${suffix}`, sortOrder: 2 },
      { question: `FAQ A ${suffix}`, answer: `Answer A ${suffix}`, sortOrder: 0 },
      { question: `FAQ B ${suffix}`, answer: `Answer B ${suffix}`, sortOrder: 1 },
    ];

    for (const item of items) {
      const created = await prisma.faqItem.create({ data: item });
      createdIds.push(created.id);
    }
  });

  afterAll(async () => {
    if (createdIds.length > 0) {
      await prisma.faqItem.deleteMany({ where: { id: { in: createdIds } } });
    }
  });

  test('returns array with expected shape', async () => {
    const result = await faqService.getAll();

    expect(Array.isArray(result)).toBe(true);
    expect(result.length).toBeGreaterThanOrEqual(3);

    result.forEach((item) => {
      expect(typeof item.id).toBe('string');
      expect(typeof item.question).toBe('string');
      expect(typeof item.answer).toBe('string');
      expect(Object.keys(item).sort()).toEqual(['answer', 'id', 'question']);
    });
  });

  test('returns seeded items ordered by sort_order ascending', async () => {
    const result = await faqService.getAll();
    const seeded = result.filter((item) => createdIds.includes(item.id));

    expect(seeded).toHaveLength(3);
    expect(seeded.map((item) => item.question)).toEqual([
      `FAQ A ${suffix}`,
      `FAQ B ${suffix}`,
      `FAQ C ${suffix}`,
    ]);
    expect(seeded.map((item) => item.answer)).toEqual([
      `Answer A ${suffix}`,
      `Answer B ${suffix}`,
      `Answer C ${suffix}`,
    ]);
  });

  test('does not expose sortOrder or timestamps in response items', async () => {
    const result = await faqService.getAll();
    const seeded = result.find((item) => item.id === createdIds[0]);

    expect(seeded).toBeDefined();
    expect(seeded).not.toHaveProperty('sortOrder');
    expect(seeded).not.toHaveProperty('createdAt');
    expect(seeded).not.toHaveProperty('updatedAt');
  });

  test('HTTP GET /api/faq returns public envelope without auth', async () => {
    const res = await app.handle(new Request('http://local/api/faq/'));
    const body = await res.json();

    expect(res.status).toBe(200);
    expect(body.success).toBe(true);
    expect(typeof body.message).toBe('string');
    expect(Array.isArray(body.data)).toBe(true);

    const seeded = body.data.filter((item: { id: string }) => createdIds.includes(item.id));
    expect(seeded).toHaveLength(3);
    expect(seeded.map((item: { question: string }) => item.question)).toEqual([
      `FAQ A ${suffix}`,
      `FAQ B ${suffix}`,
      `FAQ C ${suffix}`,
    ]);
  });

  test('HTTP GET /api/faq without trailing slash also works', async () => {
    const res = await app.handle(new Request('http://local/api/faq'));
    const body = await res.json();

    expect(res.status).toBe(200);
    expect(body.success).toBe(true);
    expect(Array.isArray(body.data)).toBe(true);
  });

  test('persisted FAQ values match service output', async () => {
    const raw = await prisma.faqItem.findMany({
      where: { id: { in: createdIds } },
      orderBy: { sortOrder: 'asc' },
    });
    const result = await faqService.getAll();
    const seeded = result.filter((item) => createdIds.includes(item.id));

    expect(seeded).toEqual(
      raw.map((item) => ({
        id: item.id,
        question: item.question,
        answer: item.answer,
      })),
    );
  });

  test('deleted FAQ no longer appears in list', async () => {
    const doomed = await prisma.faqItem.create({
      data: {
        question: `FAQ doomed ${suffix}`,
        answer: `Answer doomed ${suffix}`,
        sortOrder: 99,
      },
    });

    try {
      const before = await faqService.getAll();
      expect(before.some((item) => item.id === doomed.id)).toBe(true);

      await prisma.faqItem.delete({ where: { id: doomed.id } });

      const after = await faqService.getAll();
      expect(after.some((item) => item.id === doomed.id)).toBe(false);
    } catch (error) {
      await prisma.faqItem.deleteMany({ where: { id: doomed.id } });
      throw error;
    }
  });
});
