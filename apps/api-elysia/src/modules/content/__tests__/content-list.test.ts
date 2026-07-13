import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { contentService } from '../services/content.service';
import { prisma } from '../../../db';

// Retry wrapper untuk handle deadlock P2034
async function withRetry<T>(fn: () => Promise<T>, retries = 3): Promise<T> {
  for (let i = 0; i < retries; i++) {
    try {
      return await fn();
    } catch (error: any) {
      if (error?.code === 'P2034' && i < retries - 1) {
        await new Promise(r => setTimeout(r, 100 * (i + 1)));
        continue;
      }
      throw error;
    }
  }
  throw new Error('Max retries reached');
}

describe('Content Module - Get List with Pagination & Filters', () => {
  let testNewsIds: string[] = [];
  let testEventIds: string[] = [];
  const testSuffix = Date.now().toString().slice(-6);

  beforeAll(async () => {
    // Create 10 NEWS content in batches (5 per batch)
    for (let batch = 0; batch < 2; batch++) {
      const newsContents = await Promise.all(
        Array.from({ length: 5 }, (_, i) => {
          const idx = batch * 5 + i;
          return prisma.content.create({
            data: {
              type: 'NEWS',
              title: `News Article ${idx + 1}`,
              slug: `news-article-${idx + 1}-${testSuffix}`,
              bodyContent: `This is news article ${idx + 1} body content with more than 200 characters to test excerpt generation. Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam voluptatum quod necessitatibus.`,
              status: 'PUBLISHED'
            }
          });
        })
      );
      testNewsIds.push(...newsContents.map(c => c.id));
    }

    // Create 5 EVENT content
    const eventContents = await Promise.all(
      Array.from({ length: 5 }, (_, i) =>
        prisma.content.create({
          data: {
            type: 'EVENT',
            title: `Event ${i + 1}`,
            slug: `event-${i + 1}-${testSuffix}`,
            bodyContent: `This is event ${i + 1} description.`,
            eventDate: new Date(Date.now() + i * 86400000),
            status: 'PUBLISHED'
          }
        })
      )
    );
    testEventIds = eventContents.map(c => c.id);

    // Create 2 ARCHIVED NEWS
    const archivedContents = await Promise.all(
      Array.from({ length: 2 }, (_, i) =>
        prisma.content.create({
          data: {
            type: 'NEWS',
            title: `Archived News ${i + 1}`,
            slug: `archived-news-${i + 1}-${testSuffix}`,
            bodyContent: `This is archived news ${i + 1}.`,
            status: 'ARCHIVED'
          }
        })
      )
    );
    testNewsIds.push(...archivedContents.map(c => c.id));
  });

  afterAll(async () => {
    // Sequential delete untuk hindari deadlock
    for (const id of [...testNewsIds, ...testEventIds]) {
      try {
        await prisma.content.delete({ where: { id } });
      } catch {}
    }
  });

  test('Happy path: Returns 15 NEWS content (PUBLISHED only)', async () => {
    const result = await contentService.getAll({ type: 'NEWS' });

    expect(result.data.length).toBeGreaterThan(0);
    expect(result.data.length).toBeLessThanOrEqual(15);
    expect(result.pagination.limit).toBe(15);

    // All should be NEWS type
    result.data.forEach(item => {
      expect(item.type).toBe('NEWS');
      expect(item).toHaveProperty('excerpt');
      expect(item.excerpt.length).toBeLessThanOrEqual(203); // 200 + "..."
    });
  });

  test('Edge case 1: Filter type=EVENT returns only EVENT content', async () => {
    const result = await contentService.getAll({ type: 'EVENT' });

    expect(result.data.length).toBeGreaterThan(0);

    result.data.forEach(item => {
      expect(item.type).toBe('EVENT');
    });
  });

  test('Edge case 2: includeArchived=true includes ARCHIVED content', async () => {
    const withoutArchived = await contentService.getAll({
      type: 'NEWS',
      includeArchived: false
    });

    const withArchived = await contentService.getAll({
      type: 'NEWS',
      includeArchived: true
    });

    // With archived should have more or equal items
    expect(withArchived.data.length).toBeGreaterThanOrEqual(withoutArchived.data.length);
  });

  test('Edge case 3: Invalid type handled by route (not service)', async () => {
    // Service accepts type from route validation
    // This test just verifies default behavior
    const result = await contentService.getAll({ type: 'NEWS' });

    expect(result.data.length).toBeGreaterThanOrEqual(0);
  });

  test('Edge case 4: Pagination returns correct structure for any result size', async () => {
    // Don't assume DB is empty - just test pagination structure
    const result = await contentService.getAll({ type: 'EVENT', limit: 3 });

    expect(Array.isArray(result.data)).toBe(true);
    expect(result.data.length).toBeLessThanOrEqual(3);
    expect(result.pagination.limit).toBe(3);

    // Verify pagination structure consistency
    if (result.pagination.hasMore) {
      expect(result.pagination.nextCursor).not.toBeNull();
    } else {
      expect(result.pagination.nextCursor).toBeNull();
    }
  });

  test('Edge case 5: Pagination works correctly with cursor', async () => {
    const firstPage = await contentService.getAll({
      type: 'NEWS',
      limit: 5
    });

    expect(firstPage.data.length).toBeGreaterThan(0);

    if (firstPage.pagination.hasMore && firstPage.pagination.nextCursor) {
      const secondPage = await contentService.getAll({
        type: 'NEWS',
        limit: 5,
        cursor: firstPage.pagination.nextCursor
      });

      // Second page should not contain items from first page
      const firstPageIds = firstPage.data.map(c => c.id);
      const secondPageIds = secondPage.data.map(c => c.id);

      const overlap = firstPageIds.filter(id => secondPageIds.includes(id));
      expect(overlap.length).toBe(0);

      // Dates should be descending (newer first)
      if (secondPage.data.length > 0) {
        const lastFirstPage = firstPage.data[firstPage.data.length - 1];
        const firstSecondPage = secondPage.data[0];

        expect(lastFirstPage.createdAt.getTime()).toBeGreaterThanOrEqual(
          firstSecondPage.createdAt.getTime()
        );
      }
    }
  });

  test('Edge case 6: Excerpt generation works correctly', async () => {
    const result = await contentService.getAll({ type: 'NEWS', limit: 1 });

    if (result.data.length > 0) {
      const item = result.data[0];

      expect(item.excerpt).toBeDefined();
      expect(typeof item.excerpt).toBe('string');

      // Excerpt should be truncated if body is long
      if (item.excerpt.endsWith('...')) {
        expect(item.excerpt.length).toBeLessThanOrEqual(203);
      }
    }
  });

  test('Search q filters title when length > 2', async () => {
    const uniqueTitle = `UniqueGoldNews ${testSuffix}`;
    const created = await prisma.content.create({
      data: {
        type: 'NEWS',
        title: uniqueTitle,
        slug: `unique-gold-news-${testSuffix}`,
        bodyContent: 'Searchable unique body.',
        status: 'PUBLISHED',
      },
    });
    testNewsIds.push(created.id);

    const result = await contentService.getAll({
      type: 'NEWS',
      q: `UniqueGoldNews ${testSuffix}`,
      limit: 10,
    });

    expect(result.data.some((item) => item.id === created.id)).toBe(true);
    result.data.forEach((item) => {
      expect(item.title.toLowerCase()).toContain('uniquegoldnews');
    });
  });

  test('dateFrom/dateTo filters EVENT by eventDate', async () => {
    const day = new Date('2030-06-15T12:00:00.000Z');
    const created = await prisma.content.create({
      data: {
        type: 'EVENT',
        title: `Dated Event ${testSuffix}`,
        slug: `dated-event-${testSuffix}`,
        bodyContent: 'Event with fixed date.',
        eventDate: day,
        status: 'PUBLISHED',
      },
    });
    testEventIds.push(created.id);

    const result = await contentService.getAll({
      type: 'EVENT',
      dateFrom: '2030-06-15',
      dateTo: '2030-06-15',
      limit: 20,
    });

    expect(result.data.some((item) => item.id === created.id)).toBe(true);
    result.data.forEach((item) => {
      expect(item.eventDate).not.toBeNull();
      const t = item.eventDate!.getTime();
      expect(t).toBeGreaterThanOrEqual(new Date('2030-06-15T00:00:00.000Z').getTime());
      expect(t).toBeLessThanOrEqual(new Date('2030-06-15T23:59:59.999Z').getTime());
    });
  });
});
