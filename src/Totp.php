<?php

declare(strict_types=1);

namespace SignOps;

/** Authenticator app codes. Wrong codes count toward the user's lock: a locked user throws SignOpsException with code user_locked. */
final class Totp
{
    public function __construct(private SignOps $s) {}

    /** Starts enrolment. Show qr_svg (or secret) to the user; nothing works until confirm. @param array{user_id: string, label?: string, issuer?: string} $request @return array<string, mixed> */
    public function enroll(array $request): array
    {
        return $this->s->request('POST', '/v1/totp/enroll', $request);
    }

    /** The first code from the app. Returns the 10 backup codes, shown to the user once. @return array<string, mixed> */
    public function confirm(string $userId, string $code): array
    {
        return $this->s->request('POST', '/v1/totp/confirm', ['user_id' => $userId, 'code' => $code]);
    }

    /** A 6-digit code or a backup code. Each works once. @return array<string, mixed> */
    public function verify(string $userId, string $code): array
    {
        return $this->s->request('POST', '/v1/totp/verify', ['user_id' => $userId, 'code' => $code]);
    }

    /** New backup codes; the old ones stop working. @return array<string, mixed> */
    public function backupCodes(string $userId): array
    {
        return $this->s->request('POST', '/v1/totp/backup-codes', ['user_id' => $userId]);
    }

    /** @return array<string, mixed> */
    public function status(string $userId): array
    {
        return $this->s->request('GET', '/v1/users/' . rawurlencode($userId) . '/totp');
    }

    /** @return array<string, mixed> */
    public function remove(string $userId): array
    {
        return $this->s->request('DELETE', '/v1/users/' . rawurlencode($userId) . '/totp');
    }
}
