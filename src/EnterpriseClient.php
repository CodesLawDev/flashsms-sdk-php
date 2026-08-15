<?php

declare(strict_types=1);

namespace Flashsms;

final class EnterpriseClient
{
    private string $baseUrl;

    public function __construct(
        private readonly string $apiKey,
        ?string $baseUrl = null,
    ) {
        if (!str_starts_with($apiKey, 'ent_live_')) {
            throw new \InvalidArgumentException('EnterpriseClient requires ent_live_* key');
        }
        $this->baseUrl = rtrim($baseUrl ?? 'https://flashsms.africa', '/') . '/api/enterprise';
    }

    /** @param array<string, mixed> $payload */
    public function sendSms(array $payload): array
    {
        $ch = curl_init($this->baseUrl . '/sms/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_TIMEOUT => 60,
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        if ($raw === false) {
            throw new \RuntimeException('Request failed');
        }
        return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    }
}
