<?php

declare(strict_types=1);

namespace App\Services\ChangePhone;

use App\Exceptions\ChangePhone\ChangePhoneApprovalException;
use Illuminate\Support\Facades\Http;

class ChangePhoneApiClient
{
    public function approve(string $approvalId, int $staffId, ?string $actionNotes = null): void
    {
        $payload = [
            'approvalId' => $approvalId,
            'staffId' => $staffId,
        ];

        if ($actionNotes !== null && $actionNotes !== '') {
            $payload['actionNotes'] = $actionNotes;
        }

        $response = Http::withHeaders([
            'X-Internal-Secret' => (string) config('redeem.mobile_api.internal_secret'),
        ])->post($this->endpoint('/internal/change-phone/approve'), $payload);

        if (! $response->successful() || $response->json('success') !== true) {
            throw ChangePhoneApprovalException::failed(
                (string) ($response->json('message') ?? 'Gagal menyetujui permintaan ganti nomor'),
            );
        }
    }

    public function reject(string $approvalId, int $staffId, string $actionNotes): void
    {
        $response = Http::withHeaders([
            'X-Internal-Secret' => (string) config('redeem.mobile_api.internal_secret'),
        ])->post($this->endpoint('/internal/change-phone/reject'), [
            'approvalId' => $approvalId,
            'staffId' => $staffId,
            'actionNotes' => $actionNotes,
        ]);

        if (! $response->successful() || $response->json('success') !== true) {
            throw ChangePhoneApprovalException::failed(
                (string) ($response->json('message') ?? 'Gagal menolak permintaan ganti nomor'),
            );
        }
    }

    private function endpoint(string $path): string
    {
        $base = rtrim((string) config('redeem.mobile_api.url'), '/');

        return $base.$path;
    }
}
