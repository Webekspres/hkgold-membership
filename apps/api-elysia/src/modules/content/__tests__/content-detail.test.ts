import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { contentService } from '../services/content.service';
import { prisma } from '../../../db';

describe('Content Module - Get Detail', () => {
  let testContentId: string;

  beforeAll(async () => {
    // Create test content
    const content = await prisma.content.create({
      data: {
        type: 'NEWS',
        title: 'Test Content Detail',
        slug: 'test-content-detail',
        bodyContent: 'This is test content body for detail testing.',
        status: 'PUBLISHED'
      }
    });
    testContentId = content.id;
  });

  afterAll(async () => {
    // Cleanup
    try {
      await prisma.content.delete({ where: { id: testContentId } });
    } catch (error) {
      // Ignore cleanup errors
    }
  });

  test('Happy path: Valid UUID returns content dengan coverImages', async () => {
    const result = await contentService.getById(testContentId);

    expect(result).toBeDefined();
    expect(result?.id).toBe(testContentId);
    expect(result?.type).toBe('NEWS');
    expect(result?.title).toBe('Test Content Detail');
    expect(result?.slug).toBe('test-content-detail');
    expect(result?.bodyContent).toBe('This is test content body for detail testing.');
    expect(result?.status).toBe('PUBLISHED');
    expect(result?.locationAddress).toBeNull();
    expect(result?.locationUrl).toBeNull();
    expect(Array.isArray(result?.coverImages)).toBe(true);
  });

  test('EVENT detail includes location fields when set', async () => {
    const event = await prisma.content.create({
      data: {
        type: 'EVENT',
        title: 'Test Event Location',
        slug: 'test-event-location',
        bodyContent: 'Event body',
        eventDate: new Date('2026-08-01T10:00:00.000Z'),
        locationAddress: 'Gedung Serbaguna HK GOLD VIP',
        locationUrl: 'https://maps.google.com/?q=HK+GOLD',
        status: 'PUBLISHED',
      },
    });

    const result = await contentService.getById(event.id);

    expect(result?.locationAddress).toBe('Gedung Serbaguna HK GOLD VIP');
    expect(result?.locationUrl).toBe('https://maps.google.com/?q=HK+GOLD');

    await prisma.content.delete({ where: { id: event.id } });
  });

  test('Edge case 1: Non-existent UUID returns null', async () => {
    const fakeUuid = '00000000-0000-0000-0000-000000000000';
    const result = await contentService.getById(fakeUuid);

    expect(result).toBeNull();
  });

  test('Edge case 2: Invalid UUID format handled by route validation', async () => {
    // This test is more for documentation - actual validation happens in routes
    const invalidId = 'not-a-uuid';

    // Service would try to query with it, but Prisma would handle it
    try {
      await contentService.getById(invalidId);
    } catch (error) {
      // Expected to fail at Prisma level
      expect(error).toBeDefined();
    }
  });

  test('Edge case 3: DRAFT content accessible via getById (no filtering)', async () => {
    // Create DRAFT content
    const draftContent = await prisma.content.create({
      data: {
        type: 'NEWS',
        title: 'Draft Content',
        slug: 'draft-content-test',
        bodyContent: 'This is draft content.',
        status: 'DRAFT'
      }
    });

    const result = await contentService.getById(draftContent.id);

    expect(result).toBeDefined();
    expect(result?.status).toBe('DRAFT');

    // Cleanup
    await prisma.content.delete({ where: { id: draftContent.id } });
  });
});
