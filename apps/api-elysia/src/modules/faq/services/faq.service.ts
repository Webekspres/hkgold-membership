import { prisma } from '../../../db';
import type { IFaqService } from '../interfaces/faq.interface';
import type { FaqData } from '../types/faq.types';

export class FaqService implements IFaqService {
  async getAll(): Promise<FaqData[]> {
    const items = await prisma.faqItem.findMany({
      orderBy: { sortOrder: 'asc' },
    });

    return items.map((item) => ({
      id: item.id,
      question: item.question,
      answer: item.answer,
    }));
  }
}

export const faqService = new FaqService();
