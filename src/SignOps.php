<?php

declare(strict_types=1);

namespace SignOps;

/**
 * SignOps for PHP: push notifications to Android, iOS and the web, push approval of logins, passkeys and webhooks.
 *
 *     $signops = new \SignOps\SignOps(getenv('SIGNOPS_KEY'));
 *     $signops->push->send(['user_id' => 'usr_8f21c4', 'title' => 'Your order is out for delivery', 'body' => 'Ravi is 10 minutes away.']);
 *
 * Docs: https://signops.dev/docs/. Needs PHP 8.1 and the curl and json extensions.
 */
final class SignOps
{
    public const VERSION = '0.1.0';

    public readonly Push $push;
    public readonly Devices $devices;
    public readonly Topics $topics;
    public readonly Templates $templates;
    public readonly Approve $approve;
    public readonly Passkeys $passkeys;
    public readonly Totp $totp;
    public readonly Users $users;
    public readonly Otp $otp;
    public readonly MagicLinks $magicLinks;
    public readonly Webhooks $webhooks;

    private string $key;
    private string $base;

    /**
     * @param array{base_url?: string, webhook_secret?: string, timeout?: int, max_retries?: int} $options
     */
    public function __construct(?string $key = null, private array $options = [])
    {
        $key = $key ?: (getenv('SIGNOPS_KEY') ?: null);
        if (!$key) {
            throw new \InvalidArgumentException('SignOps: pass your secret key, or set SIGNOPS_KEY.');
        }
        if (str_starts_with($key, 'pk_')) {
            throw new \InvalidArgumentException('SignOps: this is a publishable key. On your server use the secret key, sk_live_... or sk_test_....');
        }
        $this->key = $key;
        $this->base = rtrim($options['base_url'] ?? 'https://api.signops.dev', '/');
        $this->push = new Push($this);
        $this->devices = new Devices($this);
        $this->topics = new Topics($this);
        $this->templates = new Templates($this);
        $this->approve = new Approve($this);
        $this->passkeys = new Passkeys($this);
        $this->totp = new Totp($this);
        $this->users = new Users($this);
        $this->otp = new Otp($this);
        $this->magicLinks = new MagicLinks($this);
        $this->webhooks = new Webhooks($options['webhook_secret'] ?? (getenv('SIGNOPS_WEBHOOK_SECRET') ?: null));
    }

    /**
     * Any API call. Every POST carries an Idempotency-Key, so retries on 429, 5xx and network errors never send twice.
     *
     * @param array<string, mixed>|null $body
     * @return array<string, mixed>
     */
    public function request(string $method, string $path, ?array $body = null, ?string $idempotencyKey = null, ?int $timeout = null): array
    {
        $headers = ['Authorization: Bearer ' . $this->key, 'Accept: application/json', 'User-Agent: signops-php/' . self::VERSION];
        $payload = null;
        if ($body !== null) {
            $payload = json_encode(array_filter($body, static fn ($v) => $v !== null), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $headers[] = 'Content-Type: application/json';
        }
        if ($method === 'POST') {
            $headers[] = 'Idempotency-Key: ' . ($idempotencyKey ?? bin2hex(random_bytes(16)));
        }
        $retries = $this->options['max_retries'] ?? 2;
        for ($attempt = 0; ; $attempt++) {
            $ch = curl_init($this->base . $path);
            $responseHeaders = [];
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout ?? ($this->options['timeout'] ?? 30),
                CURLOPT_HEADERFUNCTION => static function ($ch, string $line) use (&$responseHeaders): int {
                    $p = explode(':', $line, 2);
                    if (count($p) === 2) {
                        $responseHeaders[strtolower(trim($p[0]))] = trim($p[1]);
                    }
                    return strlen($line);
                },
            ]);
            if ($payload !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            }
            $raw = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $err = curl_error($ch);
            unset($ch);
            if ($raw === false) {
                if ($attempt < $retries) {
                    usleep((int) (500000 * 2 ** $attempt));
                    continue;
                }
                throw new SignOpsException(0, 'network_error', 'Could not reach SignOps: ' . $err);
            }
            $json = json_decode((string) $raw, true);
            $json = is_array($json) ? $json : [];
            if ($status >= 200 && $status < 300) {
                return $json;
            }
            // A locked user stays locked for minutes: retrying would only wait, so it is thrown at once.
            if (($status === 429 || $status >= 500) && ($json['error']['code'] ?? '') !== 'user_locked' && $attempt < $retries) {
                $wait = (float) ($responseHeaders['retry-after'] ?? 0) ?: 0.5 * 2 ** $attempt;
                usleep((int) (min($wait, 10) * 1000000));
                continue;
            }
            throw new SignOpsException($status, $json['error']['code'] ?? 'error', $json['error']['message'] ?? "SignOps answered $status");
        }
    }
}
