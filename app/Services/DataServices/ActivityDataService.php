<?php

namespace App\Services\DataServices;
use App\Core\Abstracts\BaseService;
use App\Repositories\ActivityRepository;
use App\Services\Cache\RedisService;
use App\Traits\CacheableRepository;

class ActivityDataService extends BaseService
{
    use CacheableRepository;

    public function __construct(ActivityRepository $repository, RedisService $redisService)
    {
        parent::__construct($repository, $redisService);
    }

    public function getActivityDetail(int|string $idOrSlug, ?string $locale = 'vi')
    {
        return $this->getWithCache(
            method: 'getActivityDetail',
            params: [$idOrSlug, $locale],
            cacheKeySuffix: "{$idOrSlug}_{$locale}"
        );
    }

    public function getAllActivities(?string $locale = 'vi', int $limit = 15)
    {
        return $this->getWithCache(
            method: 'getAllActivities',
            params: [$locale, $limit],
            cacheKeySuffix: "{$locale}_{$limit}"
        );
    }

    protected function getCachePrefix(): string
    {
        return 'activities';
    }

    protected function getCacheTtl(): int
    {
        return 84600; // 24 hours
    }
}