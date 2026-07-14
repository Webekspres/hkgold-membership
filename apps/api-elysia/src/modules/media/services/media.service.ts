import { PutObjectCommand, S3Client } from '@aws-sdk/client-s3';
import { prisma } from '../../../db';
import { IMediaService } from '../interfaces/media.interface';
import {
  MediaData,
  UploadMediaRequest,
  ALLOWED_IMAGE_TYPES,
  MAX_FILE_SIZE
} from '../types/media.types';

// S3 Configuration from environment (MinIO-compatible)
interface S3Config {
  endpoint: string;
  region: string;
  accessKeyId: string;
  secretAccessKey: string;
  bucket: string;
  publicUrl: string;
}

// Helper untuk parse file dari multipart form-data
const parseFileFromRequest = async (data: UploadMediaRequest) => {
  // In Elysia, file dari multipart form-data berupa File | Blob
  // Kita handle keduanya
  const file = data.file;

  // Validasi basic
  if (!file) {
    throw new Error('File tidak ditemukan di request');
  }

  // Validasi size
  if (file.size > MAX_FILE_SIZE) {
    throw new Error(`File terlalu besar. Maksimal ${MAX_FILE_SIZE / (1024 * 1024)}MB`);
  }

  // Validasi type untuk gambar (profile picture)
  if (!ALLOWED_IMAGE_TYPES.includes(file.type as any)) {
    throw new Error(`Tipe file tidak diizinkan. Hanya: ${ALLOWED_IMAGE_TYPES.join(', ')}`);
  }

  return file;
};

// S3 client lazy-initialization
let s3Client: S3Client | null = null;

const getS3Config = (): S3Config => {
  return {
    endpoint: process.env.CLOUDFLARE_R2_ENDPOINT || 'http://127.0.0.1:9002',
    region: process.env.CLOUDFLARE_R2_REGION || 'us-east-1',
    accessKeyId: process.env.CLOUDFLARE_R2_ACCESS_KEY_ID || 'hkgold_minio',
    secretAccessKey: process.env.CLOUDFLARE_R2_SECRET_ACCESS_KEY || 'hkgold_minio_secret',
    bucket: process.env.CLOUDFLARE_R2_BUCKET || 'mala-emas-media',
    publicUrl: process.env.CLOUDFLARE_R2_PUBLIC_URL || 'http://127.0.0.1:9002/mala-emas-media'
  };
};

const getS3Client = (): S3Client => {
  if (s3Client) return s3Client;

  const config = getS3Config();

  s3Client = new S3Client({
    endpoint: config.endpoint,
    region: config.region,
    credentials: {
      accessKeyId: config.accessKeyId,
      secretAccessKey: config.secretAccessKey
    },
    forcePathStyle: true // Required for MinIO
  });

  return s3Client;
};

// Helper untuk generate unique filename
const generateFileName = (originalName: string): string => {
  const timestamp = Date.now();
  const random = Math.random().toString(36).substring(2, 10);
  const extension = originalName.split('.').pop() || '';
  return `${timestamp}_${random}.${extension}`;
};

// Prisma data to MediaData
const toMediaData = (media: any): MediaData => ({
  id: media.id,
  caption: media.caption,
  fileName: media.fileName,
  fileType: media.fileType,
  fileUrl: media.fileUrl,
  fileSize: media.fileSize,
  createdAt: media.createdAt,
  updatedAt: media.updatedAt
});

export class MediaService implements IMediaService {
  async upload(data: UploadMediaRequest): Promise<MediaData> {
    // 1. Parse & validate file
    const file = await parseFileFromRequest(data);

    // 2. Generate unique filename
    const fileName = generateFileName(file.name);

    // 3. Convert File to Buffer (Bun runtime)
    const buffer = Buffer.from(await file.arrayBuffer());

    // 4. Upload to S3/MinIO
    const config = getS3Config();
    const client = getS3Client();

    const uploadCommand = new PutObjectCommand({
      Bucket: config.bucket,
      Key: fileName,
      Body: buffer,
      ContentType: file.type,
      ContentLength: file.size
    });

    try {
      await client.send(uploadCommand);
    } catch (error: any) {
      throw new Error(`Gagal upload ke S3: ${error.message}`);
    }

    // 5. Construct public URL
    const fileUrl = `${config.publicUrl}/${fileName}`;

    // 6. Save metadata to database
    const media = await prisma.media.create({
      data: {
        caption: data.caption || null,
        fileName,
        fileType: file.type,
        fileUrl,
        fileSize: file.size
      }
    });

    return toMediaData(media);
  }

  async getById(id: string): Promise<MediaData | null> {
    if (!id) return null;

    const media = await prisma.media.findUnique({
      where: { id }
    });

    if (!media) return null;

    return toMediaData(media);
  }

  async delete(id: string): Promise<void> {
    if (!id) {
      throw new Error('ID media wajib diisi');
    }

    const media = await prisma.media.findUnique({
      where: { id }
    });

    if (!media) {
      throw new Error('Media tidak ditemukan');
    }

    // Delete from database (S3 cleanup opsional, bisa pakai cron job)
    await prisma.media.delete({
      where: { id }
    });

    // ponytail: S3 file deletion skipped. Add S3 DeleteObjectCommand when needed for cleanup.
  }
}

export const mediaService = new MediaService();
