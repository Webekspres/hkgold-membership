import { MediaData, UploadMediaRequest } from '../types/media.types';

export interface IMediaService {
  // Upload file ke S3/MinIO dan simpan metadata ke database
  upload(data: UploadMediaRequest): Promise<MediaData>;

  // Ambil media by ID
  getById(id: string): Promise<MediaData | null>;

  // Hapus media by ID (opsional, untuk cleanup)
  delete?(id: string): Promise<void>;
}
