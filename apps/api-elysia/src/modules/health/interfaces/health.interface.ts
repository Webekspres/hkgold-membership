import { HealthCheckResponse } from '../types/health.types';

export interface IHealthService {
  checkDatabase(): Promise<HealthCheckResponse>;
}
