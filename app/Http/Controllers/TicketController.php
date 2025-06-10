<?php

namespace App\Http\Controllers;

use App\Core\Abstracts\BaseController;
use App\Http\Resources\Ticket\TicketCollection;
use App\Services\DataServices\TicketDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends BaseController
{
    public function __construct(
        private readonly TicketDataService $ticketDataService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $locale = $request->get('locale');
        $limit = $request->get('limit', 15);

        $tickets = $this->ticketDataService->getAllTickets(
            locale: $locale,
            limit: $limit,
        );

        return $this->respondWithCollection(
            new TicketCollection($tickets),
            'Tickets retrieved successfully.'
        );
    }
}
