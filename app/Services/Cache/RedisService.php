<?php

namespace App\Services\Cache;

use Predis\Client;
use Illuminate\Support\Facades\Log;
use Exception;

class RedisService
{
    protected Client $redis;
    protected int $defaultTtl;

    public function __construct()
    {
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host'   => config('database.redis.default.host', '127.0.0.1'),
            'port'   => config('database.redis.default.port', 6379),
            'password' => config('database.redis.default.password'),
            'database' => config('database.redis.default.database', 0),
        ]);

        $this->defaultTtl = config('cache.default_ttl', 86400);
    }

    /**
     * Get data from cache
     */
    public function get(string $key)
    {
        try {
            $data = $this->redis->get($key);
            return $data ? json_decode($data, true) : null;
        } catch (Exception $e) {
            Log::error('Redis get error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Set data to cache
     */
    public function set(string $key, $data, int $ttl = null): bool
    {
        try {
            $ttl = $ttl ?? $this->defaultTtl;
            $serializedData = json_encode($data);

            if ($ttl > 0) {
                return $this->redis->setex($key, $ttl, $serializedData) == 'OK';
            } else {
                return $this->redis->set($key, $serializedData) === 'OK';
            }
        } catch (Exception $e) {
            Log::error('Redis set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete cache by key
     */
    public function delete(string $key): bool
    {
        try {
            return $this->redis->del($key) > 0;
        } catch (Exception $e) {
            Log::error('Redis delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete cache by pattern
     */
    public function deleteByPattern(string $pattern): int
    {
        try {
            $keys = $this->redis->keys($pattern);
            if (empty($keys)) {
                return 0;
            }
            return $this->redis->del($keys);
        } catch (Exception $e) {
            Log::error('Redis delete by pattern error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if key exists
     */
    public function exists(string $key): bool
    {
        try {
            return $this->redis->exists($key) > 0;
        } catch (Exception $e) {
            Log::error('Redis exists error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get TTL of key
     */
    public function ttl(string $key): int
    {
        try {
            return $this->redis->ttl($key);
        } catch (Exception $e) {
            Log::error('Redis TTL error: ' . $e->getMessage());
            return -1;
        }
    }

    /**
     * Generate cache key
     */
    public function generateKey(string $prefix, ...$params): string
    {
        $key = $prefix;
        foreach ($params as $param) {
            $key .= ':' . (is_array($param) ? md5(serialize($param)) : $param);
        }
        return $key;
    }
}