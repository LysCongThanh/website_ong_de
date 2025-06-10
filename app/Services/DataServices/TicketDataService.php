<?php

namespace App\Services\DataServices;

use App\Core\Abstracts\BaseService;
use App\Repositories\TicketRepository;
use App\Services\Cache\RedisService;
use App\Traits\CacheableRepository;

class TicketDataService extends BaseService
{
    use CacheableRepository;

    public function __construct(TicketRepository $repository, RedisService $redisService)
    {
        parent::__construct($repository, $redisService);
    }

    public function getAllTickets(?string $locale = null, int $limit = 15, bool $paginate = true)
    {
        $suffix = implode('_', array_filter([
            $locale ?? 'no_locale',
            $limit,
            $paginate ? 'paginated' : 'not_paginated'
        ]));

        return $this->getWithCache(
            method: 'getAllTickets',
            params: [$locale, $limit, $paginate],
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
