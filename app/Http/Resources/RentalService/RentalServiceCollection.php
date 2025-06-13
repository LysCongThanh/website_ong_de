<?php

namespace App\Http\Resources\RentalService;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\RentalService\RentalServiceResource;
use Illuminate\Http\Request;

class RentalServiceCollection extends ResourceCollection
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
            'rental-services' => $this->collection->map(function ($rentalService) {
                return new RentalServiceResource($rentalService, $this->locale);
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
