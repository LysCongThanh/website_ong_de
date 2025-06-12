<?php

namespace App\Http\Controllers;

use App\Core\Abstracts\BaseController;
use App\Http\Resources\Ticket\TicketCategoryCollection;
use App\Http\Resources\Ticket\TicketCategoryResource;
use App\Http\Resources\Ticket\TicketCollection;
use App\Http\Resources\Ticket\TicketResource;
use App\Services\DataServices\TicketCategoryDataService;
use App\Services\DataServices\TicketDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends BaseController
{
    public function __construct(
        private readonly TicketDataService $ticketDataService,
        private readonly TicketCategoryDataService $ticketCategoryDataService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $locale = $request->get('locale');
        $limit = $request->get('limit', 15);
        $categoryId = $request->get('category_id');

        $tickets = $this->ticketDataService->getAllTickets(
            locale: $locale,
            limit: $limit,
            categoryId: $categoryId

        );

        return $this->respondWithCollection(
            new TicketCollection($tickets),
            'Tickets retrieved successfully.'
        );
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $locale = $request->get('locale');

        try {
            $ticket = $this->ticketDataService->getTicketById($id, $locale);
            return $this->responseWithResource(new TicketResource($ticket), 'Ticket retrieved successfully.');
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getCategories(Request $request): JsonResponse
    {
        $locale = $request->get('locale');
        $limit = $request->get('limit', 15);

        try {
            $categories = $this->ticketCategoryDataService->getCategories($locale, $limit);
            return $this->respondWithCollection(
                new TicketCategoryCollection($categories),
                'Ticket categories retrieved successfully.'
            );
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
