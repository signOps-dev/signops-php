<?php

declare(strict_types=1);

namespace SignOps;

final class Otp
{
    public function __construct(private SignOps $s) {}

    /** @param array{email: string, app_name?: string} $request @return array<string, mixed> */
    public function send(array $request): array
    {
        return $this->s->request('POST', '/v1/otp/send', $request);
    }

    /** @return array<string, mixed> */
    public function verify(string $otpId, string $code): array
    {
        return $this->s->request('POST', '/v1/otp/verify', ['otp_id' => $otpId, 'code' => $code]);
    }
}
