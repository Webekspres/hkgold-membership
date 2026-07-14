import { Elysia } from 'elysia';
import { addressService } from '../services/address.service';
import { requireActiveUser } from '../../../middleware/auth.middleware';
import { CreateAddressRequest, UpdateAddressRequest } from '../types/address.types';

// Map pesan error service ke HTTP status yang sesuai
const statusFromMessage = (message: string): number => {
  if (message.includes('tidak ditemukan')) return 404;
  return 400;
};

export const addressRoutes = new Elysia({ prefix: '/api/address' })
  .use(requireActiveUser)
  .get('/:id', async ({ params, set }) => {
    const address = await addressService.getById(params.id);

    if (!address) {
      set.status = 404;
      return {
        success: false,
        message: 'Address tidak ditemukan'
      };
    }

    return {
      success: true,
      message: 'Address berhasil diambil',
      data: address
    };
  })
  .post('/', async ({ body, set }) => {
    try {
      const data = body as CreateAddressRequest;
      const result = await addressService.create(data);

      set.status = 201;
      return {
        success: true,
        message: 'Address berhasil dibuat',
        data: result
      };
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Gagal membuat address';
      set.status = statusFromMessage(message);
      return {
        success: false,
        message
      };
    }
  })
  .patch('/:id', async ({ params, body, set }) => {
    try {
      const data = body as UpdateAddressRequest;
      const result = await addressService.update(params.id, data);

      return {
        success: true,
        message: 'Address berhasil diperbarui',
        data: result
      };
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Gagal memperbarui address';
      set.status = statusFromMessage(message);
      return {
        success: false,
        message
      };
    }
  });
