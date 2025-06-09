<?php

namespace App\Http\Controllers;

use App\Core\Abstracts\BaseController;
use App\Services\DataServices\TicketDataService;
use Illuminate\Http\Request;

class TicketController extends BaseController
{
    public function __construct(
        private readonly TicketDataService $ticketDataService
    ) {}

    public function index() {
        return $this->ticketDataService->getAllTickets();
    }
}
