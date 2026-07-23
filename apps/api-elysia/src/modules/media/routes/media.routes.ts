import { Elysia, t } from 'elysia';
import { mediaService } from '../services/media.service';
import { authMiddleware } from '../../../middleware/auth.middleware';

export const mediaRoutes = new Elysia({ prefix: '/api/media' })
  .use(authMiddleware)
  .post(
    '/upload',
    async ({ body, store }) => {
      // Auth: hanya member yang bisa upload
      const user = store.user;
      if (!user) {
        throw new Error('Unauthorized');
      }

      // File dari multipart form-data
      const file = body.file;
      if (!file) {
        throw new Error('File wajib diisi');
      }

      const result = await mediaService.upload({
        file,
        caption: body.caption
      });

      return {
        success: true,
        message: 'File berhasil diupload',
        data: result
      };
    },
    {
      body: t.Object({
        file: t.File(), // Elysia multipart file
        caption: t.Optional(t.String())
      }),
      detail: {
        summary: 'Upload media (profile picture)',
        tags: ['Media']
      }
    }
  )
  .get(
    '/:id',
    async ({ params }) => {
      const result = await mediaService.getById(params.id);

      if (!result) {
        throw new Error('Media tidak ditemukan');
      }

      return {
        success: true,
        message: 'Data media berhasil diambil',
        data: result
      };
    },
    {
      params: t.Object({
        id: t.String()
      }),
      detail: {
        summary: 'Ambil media by ID',
        tags: ['Media']
      }
    }
  );
