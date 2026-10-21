<?php

declare(strict_types=1);

namespace SignOps;

final class Approve
{
    public function __construct(private SignOps $s) {}

    /** A one-time token your app passes to the SDK to trust this phone for the signed-in user. Good for 10 minutes. @return array<string, mixed> */
    public function enrollToken(string $userId): array
    {
        return $this->s->request('POST', '/v1/approve/enroll-token', ['user_id' => $userId]);
    }

    /**
     * Pushes to the user's trusted phones. Show $result['match'] on your login page.
     *
     * @param array{user_id: string, context: string, city?: string, browser?: string, ip?: string} $request
     * @return array<string, mixed>
     */
    public function create(array $request): array
    {
        return $this->s->request('POST', '/v1/approve', $request);
    }

    /** @return array<string, mixed> */
    public function get(string $id): array
    {
        return $this->s->request('GET', '/v1/approve/' . rawurlencode($id));
    }

    /** Blocks until the user answers or the request expires, up to $timeout seconds (at most 120). @return array<string, mixed> */
    public function wait(string $id, int $timeout = 120): array
    {
        $until = time() + min($timeout, 120);
        while (true) {
            $left = max(0, $until - time());
            $a = $this->s->request('GET', '/v1/approve/' . rawurlencode($id) . '?wait=' . min($left, 25), null, null, 40);
            if ($a['status'] !== 'pending' || time() >= $until) {
                return $a;
            }
        }
    }

    /** @return array<string, mixed> */
    public function cancel(string $id): array
    {
        return $this->s->request('POST', '/v1/approve/' . rawurlencode($id) . '/cancel', []);
    }

    /** Stops a phone being trusted. It still gets pushes. @return array<string, mixed> */
    public function untrust(string $deviceId): array
    {
        return $this->s->request('POST', '/v1/approve/devices/' . rawurlencode($deviceId) . '/remove', []);
    }
}
