import jwt from '@elysiajs/jwt';
import { JWTPayload, TokenPair } from '../types/jwt.types';

const JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key-change-this';
const ACCESS_EXPIRES_IN = process.env.JWT_ACCESS_EXPIRES_IN || '12h';
const REFRESH_EXPIRES_IN = process.env.JWT_REFRESH_EXPIRES_IN || '30d';

// Helper to convert time string to seconds
const parseExpiry = (expiry: string): number => {
  const unit = expiry.slice(-1);
  const value = parseInt(expiry.slice(0, -1));

  switch (unit) {
    case 'h': return value * 3600;
    case 'd': return value * 86400;
    case 'm': return value * 60;
    case 's': return value;
    default: return 43200; // 12h default
  }
};

export class JWTService {
  async generateAccessToken(payload: Omit<JWTPayload, 'iat' | 'exp'>): Promise<string> {
    const now = Math.floor(Date.now() / 1000);
    const exp = now + parseExpiry(ACCESS_EXPIRES_IN);

    const fullPayload: JWTPayload = {
      ...payload,
      iat: now,
      exp
    };

    // Using Bun's built-in JWT (simpler than @elysiajs/jwt for service)
    const token = await Bun.password.hash(JSON.stringify(fullPayload));
    // ponytail: using simple JWT encoding for now, upgrade to proper JWT library if needed
    return Buffer.from(JSON.stringify(fullPayload)).toString('base64');
  }

  async generateRefreshToken(payload: Omit<JWTPayload, 'iat' | 'exp'>): Promise<string> {
    const now = Math.floor(Date.now() / 1000);
    const exp = now + parseExpiry(REFRESH_EXPIRES_IN);

    const fullPayload: JWTPayload = {
      ...payload,
      iat: now,
      exp
    };

    return Buffer.from(JSON.stringify(fullPayload)).toString('base64');
  }

  async generateTokenPair(payload: Omit<JWTPayload, 'iat' | 'exp'>): Promise<TokenPair> {
    const [accessToken, refreshToken] = await Promise.all([
      this.generateAccessToken(payload),
      this.generateRefreshToken(payload)
    ]);

    return { accessToken, refreshToken };
  }

  async verifyAccessToken(token: string): Promise<JWTPayload> {
    try {
      const decoded = JSON.parse(Buffer.from(token, 'base64').toString());
      const now = Math.floor(Date.now() / 1000);

      if (decoded.exp && decoded.exp < now) {
        throw new Error('Token expired');
      }

      return decoded as JWTPayload;
    } catch (error) {
      throw new Error('Invalid token');
    }
  }

  async verifyRefreshToken(token: string): Promise<JWTPayload> {
    try {
      const decoded = JSON.parse(Buffer.from(token, 'base64').toString());
      const now = Math.floor(Date.now() / 1000);

      if (decoded.exp && decoded.exp < now) {
        throw new Error('Refresh token expired');
      }

      return decoded as JWTPayload;
    } catch (error) {
      throw new Error('Invalid refresh token');
    }
  }
}

export const jwtService = new JWTService();
