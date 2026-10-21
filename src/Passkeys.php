<?php

declare(strict_types=1);

namespace SignOps;

final class Passkeys
{
    public function __construct(private SignOps $s) {}

    /** @param array{user_id: string, user_name?: string, display_name?: string, rp_id?: string} $request @return array<string, mixed> */
    public function registrationToken(array $request): array
    {
        return $this->s->request('POST', '/v1/passkeys/registration-token', $request);
    }

    /** Confirms the signops_token from the browser's passkeys.signIn. Single use. @return array<string, mixed> */
    public function verify(string $signopsToken): array
    {
        return $this->s->request('POST', '/v1/passkeys/verify', ['signops_token' => $signopsToken]);
    }

    /** @return list<array<string, mixed>> */
    public function list(string $userId): array
    {
        return $this->s->request('GET', '/v1/passkeys?user_id=' . rawurlencode($userId))['data'];
    }

    /** @return array<string, mixed> */
    public function remove(string $id): array
    {
        return $this->s->request('DELETE', '/v1/passkeys/' . rawurlencode($id));
    }
}
