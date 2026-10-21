<?php

declare(strict_types=1);

namespace SignOps;

final class Push
{
    public function __construct(private SignOps $s) {}

    /**
     * To one of user_id, user_ids (up to 1,000), topic or all => true. send_at is an ISO time with a zone, up to 30 days ahead.
     *
     * @param array<string, mixed> $push
     * @return array<string, mixed>
     */
    public function send(array $push, ?string $idempotencyKey = null): array
    {
        return $this->s->request('POST', '/v1/push', $push, $idempotencyKey);
    }

    /** @return array<string, mixed> */
    public function get(string $id): array
    {
        return $this->s->request('GET', '/v1/push/' . rawurlencode($id));
    }

    /** Only a scheduled push can be cancelled. @return array<string, mixed> */
    public function cancel(string $id): array
    {
        return $this->s->request('DELETE', '/v1/push/' . rawurlencode($id));
    }
}
