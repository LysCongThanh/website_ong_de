<?php

namespace App\Repositories;

use App\Core\Abstracts\BaseRepository;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;

class TicketRepository extends BaseRepository
{

    public function model(): string
    {
        return Ticket::class;
    }

    public function getAll(): Collection {
        return $this->all(['name', 'id']);
    }
}