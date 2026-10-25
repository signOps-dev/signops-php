# signops/signops-php

SignOps for PHP: push notifications to Android, iOS and the web, push approval of logins, passkeys and webhooks, on one API key and your own user ids. Website: https://signops.dev. Docs: https://signops.dev/docs/

```sh
composer require signops/signops-php
```

Until the package is on Packagist, add this repository to your `composer.json` first:

```json
"repositories": [{ "type": "vcs", "url": "https://github.com/signops-dev/signops-php" }]
```

```php
use SignOps\SignOps;

$signops = new SignOps(getenv('SIGNOPS_KEY')); // sk_live_... or sk_test_...

$signops->devices->add(['user_id' => 'usr_8f21c4', 'token' => $fcmToken, 'platform' => 'android']);
$signops->push->send(['user_id' => 'usr_8f21c4', 'title' => 'Your order is out for delivery', 'body' => 'Ravi is 10 minutes away.']);

$challenge = $signops->approve->create(['user_id' => 'usr_8f21c4', 'context' => 'Sign in from Chrome on Windows']);
$result = $signops->approve->wait($challenge['id'], 120); // show $challenge['match'] on your login page first

$check = $signops->passkeys->verify($request->input('signops_token'));
$event = $signops->webhooks->verify($request->getContent(), $request->header('SignOps-Signature')); // SIGNOPS_WEBHOOK_SECRET
```

Results are arrays. Errors throw `SignOps\SignOpsException` with `status`, `errorCode` and the message. Every POST carries an `Idempotency-Key`, so retries never send twice. PHP 8.1 or later with curl.
