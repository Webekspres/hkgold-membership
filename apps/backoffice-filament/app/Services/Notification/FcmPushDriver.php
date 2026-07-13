<?php

declare(strict_types=1);

namespace App\Services\Notification;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;
use Throwable;

class FcmPushDriver implements PushDriverInterface
{
    private const int MULTICAST_CHUNK_SIZE = 500;

    public function isConfigured(): bool
    {
        $credentials = config('firebase.projects.app.credentials');

        if (! filled($credentials)) {
            return false;
        }

        if (is_string($credentials) && ! is_file($credentials)) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<string, string>  $data
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): PushDeliveryResult
    {
        if (! $this->isConfigured()) {
            return PushDeliveryResult::failed('FCM credential tidak dikonfigurasi.');
        }

        try {
            $messaging = $this->messaging();
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(FcmNotification::create($title, $body))
                ->withData($this->normalizeData($data));

            $messaging->send($message);

            return PushDeliveryResult::success();
        } catch (MessagingException|FirebaseException $exception) {
            Log::warning('FcmPushDriver: gagal kirim ke token.', [
                'message' => $exception->getMessage(),
            ]);

            return PushDeliveryResult::failed($exception->getMessage());
        } catch (Throwable $exception) {
            Log::warning('FcmPushDriver: error tidak terduga.', [
                'message' => $exception->getMessage(),
            ]);

            return PushDeliveryResult::failed($exception->getMessage());
        }
    }

    /**
     * @param  array<int, string>  $tokens
     * @param  array<string, string>  $data
     */
    public function sendMulticast(array $tokens, string $title, string $body, array $data = []): PushDeliveryResult
    {
        if (! $this->isConfigured()) {
            return PushDeliveryResult::failed('FCM credential tidak dikonfigurasi.');
        }

        if ($tokens === []) {
            return PushDeliveryResult::failed('Tidak ada token push untuk dikirim.');
        }

        $totalSuccess = 0;
        $totalFailure = 0;
        $lastError = null;

        try {
            $messaging = $this->messaging();
            $message = CloudMessage::new()
                ->withNotification(FcmNotification::create($title, $body))
                ->withData($this->normalizeData($data));

            foreach (array_chunk($tokens, self::MULTICAST_CHUNK_SIZE) as $chunk) {
                $report = $messaging->sendMulticast($message, $chunk);
                $totalSuccess += $report->successes()->count();
                $totalFailure += $report->failures()->count();

                if ($report->hasFailures()) {
                    foreach ($report->failures()->getItems() as $failure) {
                        $lastError = $failure->error()->getMessage();

                        break;
                    }
                }
            }

            return PushDeliveryResult::multicast($totalSuccess, $totalFailure, $lastError);
        } catch (MessagingException|FirebaseException $exception) {
            Log::warning('FcmPushDriver: gagal multicast.', [
                'message' => $exception->getMessage(),
            ]);

            return PushDeliveryResult::failed($exception->getMessage(), count($tokens));
        } catch (Throwable $exception) {
            Log::warning('FcmPushDriver: error multicast tidak terduga.', [
                'message' => $exception->getMessage(),
            ]);

            return PushDeliveryResult::failed($exception->getMessage(), count($tokens));
        }
    }

    private function messaging(): Messaging
    {
        $credentials = config('firebase.projects.app.credentials');

        return (new Factory)
            ->withServiceAccount($credentials)
            ->createMessaging();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function normalizeData(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $normalized[(string) $key] = (string) $value;
            }
        }

        return $normalized;
    }
}
