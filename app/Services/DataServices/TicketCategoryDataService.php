<?php

namespace App\Services\DataServices;
use App\Core\Abstracts\BaseService;
use App\Repositories\TicketCategoryRepository;
use App\Services\Cache\RedisService;
use App\Traits\CacheableRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TicketCategoryDataService extends BaseService
{
    use CacheableRepository;

    public function __construct(TicketCategoryRepository $repository, RedisService $redisService)
    {
        parent::__construct($repository, $redisService);
    }

    public function getCategories(?string $locale = 'vi', int $limit = 15)
    {
        return $this->getWithCache(
            method: 'getCategories',
            params: [$locale, $limit],
            cacheKeySuffix: "{$locale}_{$limit}"
        );
    }

    protected function getCachePrefix(): string
    {
        return 'ticket_categories';
    }

    protected function getCacheTtl(): int
    {
        return 84600;
    }
}