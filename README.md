# flashsms/sdk

Official **FlashSMS** API v2 client for PHP by **Codeslaw Global Technologies**.

All `sendMessage` calls are **live SMS** billed to your credits. Use `testConnection()` to verify your key without sending SMS.

## Install

```bash
composer require flashsms/sdk
```

## Quickstart

```php
<?php

use Flashsms\FlashsmsClient;

$client = new FlashsmsClient(getenv('FLASHSMS_API_KEY')); // bms_live_...

// No SMS sent
$balance = $client->testConnection();
echo 'Credits: ' . $balance['data']['total'] . PHP_EOL;

// Live SMS — charges credits
$sent = $client->sendMessage([
    'message' => 'Hello from FlashSMS',
    'phones' => ['0201234567'],
    'senderId' => 'YourBrand',
]);
```

## Requirements

- PHP 8.1+
- ext-curl, ext-json

## Environment

Set `FLASHSMS_API_KEY` to a prepaid v2 key (`bms_live_*`).

## API v1

New v1 keys are no longer issued. Existing v1 keys continue to work on `/api/v1` via REST; this SDK is **v2 only**.

## License

MIT © Codeslaw Global Technologies
