<?php

declare(strict_types=1);

namespace SignOps;

final class Templates
{
    public function __construct(private SignOps $s) {}

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return $this->s->request('GET', '/v1/templates')['data'];
    }

    /** @return array<string, mixed> */
    public function get(string $name): array
    {
        return $this->s->request('GET', '/v1/templates/' . rawurlencode($name));
    }

    /**
     * Creates or replaces a template by name. Write {{field}} or {{field|default}} where values go.
     *
     * @param array{name: string, title?: string, body?: string, description?: string, image?: string, link?: string, data?: array<string, string>} $template
     * @return array<string, mixed>
     */
    public function save(array $template): array
    {
        return $this->s->request('POST', '/v1/templates', $template);
    }

    /** @return array<string, mixed> */
    public function remove(string $name): array
    {
        return $this->s->request('DELETE', '/v1/templates/' . rawurlencode($name));
    }
}
