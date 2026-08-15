<?php

declare(strict_types=1);

namespace Flashsms;

/**
 * FlashSMS API v2 client — Codeslaw Global Technologies.
 * All send() calls are live SMS billed to your credits.
 */
final class FlashsmsClient
{
    private string $baseUrl;

    public function __construct(
        private readonly string $apiKey,
        ?string $baseUrl = null,
    ) {
        if (!str_starts_with($apiKey, 'bms_live_')) {
            throw new \InvalidArgumentException('FlashsmsClient requires bms_live_* v2 API key');
        }
        $this->baseUrl = rtrim($baseUrl ?? 'https://flashsms.africa', '/') . '/api/v2';
    }

    /** @return array<string, mixed> */
    public function testConnection(): array
    {
        return $this->request('GET', '/balance');
    }

    /** @param array<string, mixed> $payload */
    public function sendMessage(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->request('POST', '/sms/send', $payload, [
            'Idempotency-Key: ' . ($idempotencyKey ?? $this->uuid()),
        ]);
    }

    /** @param array<string, mixed> $payload */
    public function estimate(array $payload): array
    {
        return $this->request('POST', '/sms/estimate', $payload, [
            'Idempotency-Key: ' . $this->uuid(),
        ]);
    }

    /** @return array<string, mixed> */
    public function getMessageStatus(string $messageId): array
    {
        return $this->request('GET', '/sms/status/' . rawurlencode($messageId));
    }

    /** @param array<string, mixed>|null $body */
    private function request(string $method, string $path, ?array $body = null, array $extraHeaders = []): array
    {
        $ch = curl_init($this->baseUrl . $path);
        $headers = array_merge([
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ], $extraHeaders);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 60,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_THROW_ON_ERROR));
        }

        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            throw new \RuntimeException('FlashSMS request failed');
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        if ($code >= 400) {
            $msg = is_array($data['error'] ?? null)
                ? (string) (($data['error']['message'] ?? 'Request failed'))
                : 'Request failed';
            throw new \RuntimeException($msg, $code);
        }

        return $data;
    }

    private function uuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
    }
}
