<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Aws\S3\S3Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class R2SignedUrlController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = Validator::make(
            $request->all(),
            [
                'folder' => ['required', 'string', 'in:contents'],
                'mime' => ['required', 'string', 'in:image/webp'],
            ],
        )->validate();

        $bucket = (string) config('filesystems.disks.r2.bucket');
        $endpoint = (string) config('filesystems.disks.r2.endpoint');
        $region = (string) config('filesystems.disks.r2.region');
        $key = (string) config('filesystems.disks.r2.key');
        $secret = (string) config('filesystems.disks.r2.secret');
        $publicUrl = rtrim((string) config('filesystems.disks.r2.url'), '/');

        abort_unless(
            filled($bucket) && filled($endpoint) && filled($region) && filled($key) && filled($secret) && filled($publicUrl),
            500,
            'R2 configuration is incomplete.',
        );

        $objectKey = trim($validated['folder'], '/').'/'.Str::uuid()->toString().'.webp';

        $client = new S3Client([
            'version' => 'latest',
            'region' => $region,
            'endpoint' => $endpoint,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => $key,
                'secret' => $secret,
            ],
        ]);

        $command = $client->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key' => $objectKey,
            'ContentType' => $validated['mime'],
        ]);

        $signedRequest = $client->createPresignedRequest($command, '+15 minutes');

        return response()->json([
            'upload_url' => (string) $signedRequest->getUri(),
            'key' => $objectKey,
            'public_url' => $publicUrl.'/'.$objectKey,
        ]);
    }
}
