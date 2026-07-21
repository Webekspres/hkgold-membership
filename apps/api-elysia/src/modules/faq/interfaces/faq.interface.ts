import type { FaqData } from '../types/faq.types';

export interface IFaqService {
  getAll(): Promise<FaqData[]>;
}
