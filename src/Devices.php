<?php

declare(strict_types=1);

namespace SignOps;

final class Devices
{
    public function __construct(private SignOps $s) {}

    /**
     * token is the FCM or APNs token, or for web the PushSubscription your page got. platform is android, ios or web.
     *
     * @param array{user_id: string, token: string|array<string, mixed>, platform: string, name?: string} $device
     * @return array<string, mixed>
     */
    public function add(array $device): array
    {
        return $this->s->request('POST', '/v1/devices', $device);
    }

    /** @return array<string, mixed> */
    public function remove(string $id): array
    {
        return $this->s->request('DELETE', '/v1/devices/' . rawurlencode($id));
    }

    /** @return list<array<string, mixed>> */
    public function list(string $userId): array
    {
        return $this->s->request('GET', '/v1/devices?user_id=' . rawurlencode($userId))['data'];
    }
}
