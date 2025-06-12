<?php

namespace App\Services\DataServices;

use App\Core\Abstracts\BaseService;
use App\Repositories\TicketRepository;
use App\Services\Cache\RedisService;
use App\Traits\CacheableRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TicketDataService extends BaseService
{
    use CacheableRepository;

    public function __construct(TicketRepository $repository, RedisService $redisService)
    {
        parent::__construct($repository, $redisService);
    }

    /**
     * Get a ticket by ID with related data.
     *
     * @param int $id
     * @param string|null $locale
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getTicketById(int $id, ?string $locale = 'vi')
    {
        return $this->getWithCache(
            method: 'getTicketById',
            params: [$id, $locale],
            cacheKeySuffix: "{$id}_{$locale}"
        );
    }

    public function getAllTickets(?string $locale = null, int $limit = 15, ?int $categoryId = null)
    {
        $suffix = implode('_', array_filter([
            $locale ?? 'vi',
            $limit,
            $categoryId
        ]));

        return $this->getWithCache(
            method: 'getAllTickets',
            params: [$locale, $limit, $categoryId],
            cacheKeySuffix: $suffix
        );
    }

    protected function getCachePrefix(): string
    {
        return 'tickets';
    }

    protected function getCacheTtl(): int
    {
        return 84600;
    }
}
