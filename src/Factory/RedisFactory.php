<?php

declare(strict_types=1);

namespace App\Factory;

use Redis;

class RedisFactory
{
    public static function create(): Redis
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379); // Cambia si usas otro host o puerto
        return $redis;
    }
}
