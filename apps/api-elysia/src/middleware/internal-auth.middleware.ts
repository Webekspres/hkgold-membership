import { Elysia } from 'elysia';

/**
 * Service-to-service auth: Filament → api-elysia.
 * Header `X-Internal-Secret` must equal `MOBILE_API_INTERNAL_SECRET`.
 */
export const requireInternalSecret = new Elysia({ name: 'internal-auth' })
  .onBeforeHandle({ as: 'scoped' }, ({ headers, set }) => {
    const expected = process.env.MOBILE_API_INTERNAL_SECRET;
    const provided =
      headers['x-internal-secret'] ?? headers['X-Internal-Secret'];

    if (!expected || !provided || provided !== expected) {
      set.status = 401;
      return {
        success: false,
        message: 'Unauthorized - Invalid internal secret',
      };
    }
  })
  .as('scoped');
