<?php

namespace App\Core\Abstracts;

use App\Services\Cache\RedisService;
use App\Services\Cache\TypeSafeCacheService;
use Illuminate\Support\Facades\DB;

abstract class BaseService
{
    /** @var BaseRepository */
    protected BaseRepository $repository;
    protected RedisService $redisService;
    protected TypeSafeCacheService $typeSafeCache;
    protected string $cachePrefix;
    protected int $cacheTtl;

    public function __construct($repository, RedisService $redisService) {
        $this->repository = $repository;
        $this->redisService = $redisService;
        $this->typeSafeCache = new TypeSafeCacheService($redisService);
        $this->cachePrefix = $this->getCachePrefix();
        $this->cacheTtl = $this->getCacheTtl();
    }

    abstract protected function getCachePrefix(): string;

    protected function getCacheTtl(): int
    {
        return 3600;
    }

    protected function getWithCache(string $method, array $params = [], string $cacheKeySuffix = '')
    {
        $cacheKey = $this->redisService->generateKey(
            $this->cachePrefix . ':' . $method,
            $cacheKeySuffix ?: implode('_', array_filter($params, fn($p) => !is_null($p)))
        );

        return $this->typeSafeCache->remember($cacheKey, function () use ($method, $params) {
            return call_user_func_array([$this->repository, $method], $params);
        }, $this->cacheTtl);
    }

    /**
     * Get data with custom cache key and TTL
     */
    protected function getWithCustomCache(string $method, array $params = [], string $cacheKey = '', int $ttl = null)
    {
        if (empty($cacheKey)) {
            $cacheKey = $this->redisService->generateKey(
                $this->cachePrefix . ':' . $method,
                implode('_', array_filter($params, fn($p) => !is_null($p)))
            );
        }

        return $this->typeSafeCache->remember($cacheKey, function () use ($method, $params) {
            return call_user_func_array([$this->repository, $method], $params);
        }, $ttl ?? $this->cacheTtl);
    }

    /**
     * Clear cache by pattern
     */
    protected function clearCache(string $pattern = null): int
    {
        $pattern = $pattern ?: $this->cachePrefix . ':*';
        return $this->typeSafeCache->forgetByPattern($pattern);
    }

    /**
     * Clear specific cache key
     */
    protected function forgetCache(string $method, array $params = [], string $cacheKeySuffix = ''): bool
    {
        $cacheKey = $this->redisService->generateKey(
            $this->cachePrefix . ':' . $method,
            $cacheKeySuffix ?: implode('_', array_filter($params, fn($p) => !is_null($p)))
        );

        return $this->typeSafeCache->forget($cacheKey);
    }

    /**
     * Execute callback within transaction with cache invalidation
     * @throws \Exception
     */
    protected function executeInTransaction(\Closure $callback, array $cachePatterns = [])
    {
        DB::beginTransaction();

        try {
            $result = $callback();

            // Clear related caches
            foreach ($cachePatterns as $pattern) {
                $this->clearCache($pattern);
            }

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function beginTransaction(): void
    {
        DB::beginTransaction();
    }

    protected function commit(): void {
        DB::commit();
    }

    protected function rollback(): void {
        DB::rollBack();
    }

}
