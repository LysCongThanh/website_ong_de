<?php

namespace App\Core\Abstracts;

use App\Services\Cache\RedisService;
use Illuminate\Support\Facades\DB;

abstract class BaseService
{
    /** @var BaseRepository */
    protected BaseRepository $repository;
    protected RedisService $redisService;
    protected string $cachePrefix;
    protected int $cacheTtl;

    public function __construct($repository, RedisService $redisService) {
        $this->repository = $repository;
        $this->redisService = $redisService;
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
            $cacheKeySuffix ?: implode('_', $params)
        );

        $cachedData = $this->redisService->get($cacheKey);
        if ($cachedData !== null) {
            return $cachedData;
        }

        $data = call_user_func_array([$this->repository, $method], $params);

        if ($data) {
            $this->redisService->set($cacheKey, $data, $this->cacheTtl);
        }

        return $data;
    }

    protected function clearCache(string $pattern = null): int
    {
        $pattern = $pattern ?: $this->cachePrefix . ':*';
        return $this->redisService->deleteByPattern($pattern);
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
