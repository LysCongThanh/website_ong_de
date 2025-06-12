<?php

namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TicketCategoryCollection extends ResourceCollection
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
            'ticket-categories' => $this->collection->map(function ($ticketCategory) {
                return new TicketCategoryResource($ticketCategory, $this->locale);
            }),
        ];
    }
}
