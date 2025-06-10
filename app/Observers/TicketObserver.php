<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Services\DataServices\TicketDataService;

class TicketObserver
{
    protected TicketDataService $service;

    public function __construct()
    {
        $this->service = app(TicketDataService::class);
    }

    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        $this->service->clearCache('tickets:*');
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        $this->service->clearCache('tickets:*');
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        $this->service->clearCache('tickets:*');
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        $this->service->clearCache('tickets:*');
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        $this->service->clearCache('tickets:*');
    }
}
