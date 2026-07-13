export interface CursorPaginationParams {
  cursor?: string;
  limit?: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  pagination: {
    nextCursor: string | null;
    hasMore: boolean;
    limit: number;
  };
}

// Helper functions untuk cursor encoding/decoding
export function encodeCursor(data: object): string {
  return Buffer.from(JSON.stringify(data)).toString('base64');
}

export function decodeCursor(cursor: string): any | null {
  try {
    return JSON.parse(Buffer.from(cursor, 'base64').toString());
  } catch {
    return null;
  }
}
