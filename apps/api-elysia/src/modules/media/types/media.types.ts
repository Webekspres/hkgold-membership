import { ApiResponse } from '../../../shared/types/response.types';

// Data media setelah upload
export interface MediaData {
  id: string;
  caption: string | null;
  fileName: string;
  fileType: string;
  fileUrl: string;
  fileSize: number;
  createdAt: Date;
  updatedAt: Date;
}

// Request upload media (multipart/form-data)
export interface UploadMediaRequest {
  file: File; // File object dari Elysia
  caption?: string;
  folder?: string; // e.g. "member/photo"
  image?: { maxSize: number; quality: number }; // when set -> resize+webp
}

// Response upload media
export type UploadMediaResponse = ApiResponse<MediaData>;

// Allowed MIME types untuk profile picture
export const ALLOWED_IMAGE_TYPES = [
  'image/jpeg',
  'image/jpg',
  'image/png',
  'image/webp'
] as const;

// Max file size: 5MB
export const MAX_FILE_SIZE = 5 * 1024 * 1024;
