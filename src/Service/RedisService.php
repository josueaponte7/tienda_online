<?php

namespace App\Service;

use Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class RedisService
{
    private Redis $redis;

    public function __construct()
    {
        $this->redis = RedisAdapter::createConnection($_ENV['REDIS_URL']);
    }

    public function storeToken(string $userId, string $token): void
    {
        $this->redis->set('user_token_' . $userId, $token);
    }

    public function getToken(string $userId): ?string
    {
        return $this->redis->get('user_token_' . $userId);
    }

    public function incrementLoginAttempts(string $email): int
    {
        $key = 'login_attempts_' . $email;
        return $this->redis->incr($key); // Incrementa y devuelve el nuevo valor
    }

    public function getLoginAttempts(string $email): int
    {
        return $this->redis->get('login_attempts_' . $email) ?? 0;
    }
}
