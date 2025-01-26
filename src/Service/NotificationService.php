<?php

declare(strict_types=1);

namespace App\Service;

use Redis;

class NotificationService
{
    private Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function sendNotification(string $channel, string $message): void
    {
        $this->redis->publish($channel, $message);
    }
}
