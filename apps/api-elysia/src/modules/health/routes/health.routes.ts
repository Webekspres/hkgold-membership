import { Elysia } from 'elysia';
import { healthService } from '../services/health.service';
import { ApiResponse } from '../../../shared/types/response.types';
import { HealthCheckResponse } from '../types/health.types';

export const healthRoutes = new Elysia({ prefix: '/api' })
  .get('/health', async (): Promise<ApiResponse<HealthCheckResponse>> => {
    const healthCheck = await healthService.checkDatabase();

    if (healthCheck.status === 'error') {
      return {
        success: false,
        message: 'Database connection failed',
        data: healthCheck
      };
    }

    return {
      success: true,
      message: 'System healthy',
      data: healthCheck
    };
  });
