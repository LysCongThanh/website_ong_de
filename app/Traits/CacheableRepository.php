<?php

namespace App\Traits;

trait CacheableRepository
{
    protected function getCacheKey(string $method, array $params = []): string
    {
        $cacheKeySuffix = implode('_', array_filter($params, fn($p) => !is_null($p) && !is_array($p) && !is_object($p)));

        return $this->redisService->generateKey(
            $this->cachePrefix . ':' . $method,
            $cacheKeySuffix
        );
    }

    protected function getWithCache(string $method, array $params = [], string $cacheKeySuffix = '', int $ttl = null)
    {
        $cacheKey = $cacheKeySuffix
            ? $this->redisService->generateKey($this->cachePrefix . ':' . $method, $cacheKeySuffix)
            : $this->getCacheKey($method, $params);

        return $this->typeSafeCache->remember($cacheKey, function () use ($method, $params) {
            return call_user_func_array([$this->repository, $method], $params);
        }, $ttl ?? $this->cacheTtl);
    }

    protected function invalidateCache(string $pattern = null): int
    {
        $pattern = $pattern ?? ($this->cachePrefix . ':*');
        return $this->typeSafeCache->forgetByPattern($pattern);
    }

    protected function invalidateCacheForMethod(string $method): int
    {
        $pattern = $this->cachePrefix . ':' . $method . ':*';
        return $this->typeSafeCache->forgetByPattern($pattern);
    }
}