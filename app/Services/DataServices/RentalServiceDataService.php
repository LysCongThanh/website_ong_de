<?php

namespace App\Services\DataServices;
use App\Core\Abstracts\BaseService;
use App\Repositories\RentalServiceRepository;
use App\Services\Cache\RedisService;
use App\Traits\CacheableRepository;

class RentalServiceDataService extends BaseService
{
    use CacheableRepository;

    public function __construct(RentalServiceRepository $repository, RedisService $redisService)
    {
        parent::__construct($repository, $redisService);
    }

    public function getRentalServiceDetail(int|string $idOrSlug, ?string $locale = 'vi')
    {
        return $this->getWithCache(
            method: 'getRenltalServiceDetail',
            params: [$idOrSlug, $locale],
            cacheKeySuffix: "{$idOrSlug}_{$locale}"
        );
    }

    public function getAllRentalServices(?string $locale = 'vi', int $limit = 15)
    {
        return $this->getWithCache(
            method: 'getAllRentalServices',
            params: [$locale, $limit],
            cacheKeySuffix: "{$locale}_{$limit}"
        );
    }

    protected function getCachePrefix(): string
    {
        return 'rental_services';
    }

    protected function getCacheTtl(): int
    {
        return 84600;
    }
}