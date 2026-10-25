<?php

declare(strict_types=1);

namespace SignOps;

final class Users
{
    public function __construct(private SignOps $s) {}

    /** Erases everything held under the user id (DPDP Act). @return array<string, mixed> */
    public function delete(string $userId): array
    {
        return $this->s->request('DELETE', '/v1/users/' . rawurlencode($userId));
    }

    /** Everything SignOps holds on the user, as one document. @return array<string, mixed> */
    public function export(string $userId): array
    {
        return $this->s->request('GET', '/v1/users/' . rawurlencode($userId) . '/export');
    }

    /** @param array{purpose: string, granted: bool, source?: string, text?: string} $consent @return array<string, mixed> */
    public function recordConsent(string $userId, array $consent): array
    {
        return $this->s->request('POST', '/v1/users/' . rawurlencode($userId) . '/consent', $consent);
    }

    /** @return array<string, mixed> */
    public function consent(string $userId): array
    {
        return $this->s->request('GET', '/v1/users/' . rawurlencode($userId) . '/consent');
    }

    /** Whether wrong codes have locked the user. @return array<string, mixed> */
    public function lock(string $userId): array
    {
        return $this->s->request('GET', '/v1/users/' . rawurlencode($userId) . '/lock');
    }

    /** @return array<string, mixed> */
    public function unlock(string $userId): array
    {
        return $this->s->request('DELETE', '/v1/users/' . rawurlencode($userId) . '/lock');
    }
}
