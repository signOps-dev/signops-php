<?php

declare(strict_types=1);

namespace SignOps;

final class Topics
{
    public function __construct(private SignOps $s) {}

    /** @return array<string, mixed> */
    public function subscribe(string $topic, string $userId): array
    {
        return $this->s->request('POST', '/v1/topics/' . rawurlencode($topic) . '/subscribe', ['user_id' => $userId]);
    }

    /** @return array<string, mixed> */
    public function unsubscribe(string $topic, string $userId): array
    {
        return $this->s->request('POST', '/v1/topics/' . rawurlencode($topic) . '/unsubscribe', ['user_id' => $userId]);
    }
}
