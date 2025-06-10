<?php

namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @method perPage()
 * @method currentPage()
 * @method lastPage()
 * @method nextPageUrl()
 * @method previousPageUrl()
 * @method url(int $int)
 * @method firstItem()
 * @method lastItem()
 */
class TicketCollection extends ResourceCollection
{
    protected string $locale;

    public function __construct($resource, ?string $locale = 'vi')
    {
        parent::__construct($resource);
        $this->locale = $locale ?? 'vi';
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'tickets' => $this->collection->map(function ($ticket) {
                return new TicketResource($ticket, $this->locale);
            }),
            'pagination' => [
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
                'next_page_url' => $this->nextPageUrl(),
                'prev_page_url' => $this->previousPageUrl(),
                'first_page_url' => $this->url(1),
                'last_page_url' => $this->url($this->lastPage()),
                'from' => $this->firstItem(),
                'to' => $this->lastItem()
            ],
        ];
    }
}
