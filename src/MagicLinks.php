<?php

declare(strict_types=1);

namespace SignOps;

/** Sign-in links by email. The link opens redirect_url with ?signops_token= added; pass that token to verify, once. */
final class MagicLinks
{
    public function __construct(private SignOps $s) {}

    /** @param array{email: string, redirect_url: string, app_name?: string, language?: string, ip?: string} $request @return array<string, mixed> */
    public function send(array $request): array
    {
        return $this->s->request('POST', '/v1/magic-links', $request);
    }

    /** @return array<string, mixed> */
    public function verify(string $token): array
    {
        return $this->s->request('POST', '/v1/magic-links/verify', ['token' => $token]);
    }

    /** @return array<string, mixed> */
    public function get(string $linkId): array
    {
        return $this->s->request('GET', '/v1/magic-links/' . rawurlencode($linkId));
    }
}
