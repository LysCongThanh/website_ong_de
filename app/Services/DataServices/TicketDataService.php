<?php

namespace App\Services\DataServices;

use App\Core\Abstracts\BaseService;
use App\Repositories\TicketRepository;
use App\Services\Cache\RedisService;

class TicketDataService extends BaseService
{
    public function __construct(TicketRepository $repository, RedisService $redisService)
    {
        parent::__construct($repository, $redisService);
    }

    public function getAllTickets() {
        return $this->getWithCache(
            method: 'getAll',
            params: [],
            cacheKeySuffix: ''
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