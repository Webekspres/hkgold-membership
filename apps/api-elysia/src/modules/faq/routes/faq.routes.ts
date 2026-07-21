import { Elysia } from 'elysia';
import { faqService } from '../services/faq.service';

export const faqRoutes = new Elysia({ prefix: '/api/faq' }).get('/', async () => {
  const data = await faqService.getAll();
  return {
    success: true,
    message: 'Daftar FAQ berhasil diambil',
    data,
  };
});
