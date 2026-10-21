<?php

declare(strict_types=1);

namespace SignOps;

final class Webhooks
{
    public function __construct(private ?string $secret) {}

    /**
     * Checks the SignOps-Signature header against the raw body and returns the event. Throws if it does not match or is
     * more than 5 minutes old. Pass the raw body: file_get_contents('php://input'), or $request->getContent() in Laravel.
     *
     * @return array<string, mixed>
     */
    public function verify(string $payload, ?string $header, ?string $secret = null, int $tolerance = 300): array
    {
        $key = $secret ?? $this->secret;
        if (!$key) {
            throw new \InvalidArgumentException('SignOps: no webhook secret. Pass it, or set SIGNOPS_WEBHOOK_SECRET.');
        }
        $parts = [];
        foreach (explode(',', (string) $header) as $p) {
            $kv = explode('=', trim($p), 2);
            if (count($kv) === 2) {
                $parts[$kv[0]] = $kv[1];
            }
        }
        if (!isset($parts['t'], $parts['v1']) || !ctype_digit($parts['t'])) {
            throw new SignOpsException(400, 'invalid_signature', 'No SignOps-Signature header, or it is not t=...,v1=...');
        }
        if (abs(time() - (int) $parts['t']) > $tolerance) {
            throw new SignOpsException(400, 'invalid_signature', 'The event is older than 5 minutes.');
        }
        $want = hash_hmac('sha256', $parts['t'] . '.' . $payload, $key);
        if (!hash_equals($want, $parts['v1'])) {
            throw new SignOpsException(400, 'invalid_signature', 'The signature does not match. Check the secret and that the body is the raw one.');
        }
        return json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
    }
}
