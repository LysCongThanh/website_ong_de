<?php

namespace App\Http\Controllers;

use App\Core\Abstracts\BaseController;
use App\Http\Requests\Ticket\TicketIndexRequest;
use App\Http\Resources\Ticket\TicketCollection;
use App\Http\Resources\Ticket\TicketResource;
use App\Services\DataServices\TicketDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends BaseController
{
    public function __construct(
        private readonly TicketDataService $ticketDataService
    ) {}

    public function index(TicketIndexRequest $request): JsonResponse
    {
        $locale = $request->validated('locale');
        $limit = $request->validated('limit', 15);
        $paginate = $request->validated('paginate', false);

        $tickets = $this->ticketDataService->getAllTickets(
            locale: 'zh',
            limit: $limit,
            paginate: true
        );

        return $this->respondWithCollection(
            new TicketCollection($tickets),
            'Tickets retrieved successfully.'
        );
    }
}
