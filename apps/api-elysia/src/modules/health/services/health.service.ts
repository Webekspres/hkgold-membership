import { prisma } from '../../../db';
import { IHealthService } from '../interfaces/health.interface';
import { HealthCheckResponse } from '../types/health.types';

export class HealthService implements IHealthService {
  async checkDatabase(): Promise<HealthCheckResponse> {
    try {
      await prisma.$queryRaw`SELECT 1`;
      return {
        status: 'ok',
        database: 'connected',
        timestamp: new Date().toISOString()
      };
    } catch (error) {
      return {
        status: 'error',
        database: 'disconnected',
        timestamp: new Date().toISOString(),
        error: error instanceof Error ? error.message : 'Unknown error'
      };
    }
  }
}

export const healthService = new HealthService();
