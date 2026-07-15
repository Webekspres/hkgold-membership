import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { Elysia } from 'elysia';
import { requireInternalSecret } from '../internal-auth.middleware';

describe('requireInternalSecret middleware', () => {
  const original = process.env.MOBILE_API_INTERNAL_SECRET;
  let app: Elysia;

  beforeAll(() => {
    process.env.MOBILE_API_INTERNAL_SECRET = 'test-internal-secret-fase7';
    app = new Elysia()
      .use(requireInternalSecret)
      .post('/internal/ping', () => ({
        success: true,
        message: 'ok',
      }));
  });

  afterAll(() => {
    if (original === undefined) delete process.env.MOBILE_API_INTERNAL_SECRET;
    else process.env.MOBILE_API_INTERNAL_SECRET = original;
  });

  test('missing X-Internal-Secret → 401', async () => {
    const res = await app.handle(
      new Request('http://localhost/internal/ping', { method: 'POST' }),
    );
    expect(res.status).toBe(401);
    const body = (await res.json()) as { success: boolean };
    expect(body.success).toBe(false);
  });

  test('wrong secret → 401', async () => {
    const res = await app.handle(
      new Request('http://localhost/internal/ping', {
        method: 'POST',
        headers: { 'X-Internal-Secret': 'wrong-secret' },
      }),
    );
    expect(res.status).toBe(401);
  });

  test('correct secret → 200', async () => {
    const res = await app.handle(
      new Request('http://localhost/internal/ping', {
        method: 'POST',
        headers: { 'X-Internal-Secret': 'test-internal-secret-fase7' },
      }),
    );
    expect(res.status).toBe(200);
    const body = (await res.json()) as { success: boolean; message: string };
    expect(body.success).toBe(true);
    expect(body.message).toBe('ok');
  });
});
