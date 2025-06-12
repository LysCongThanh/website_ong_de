<?php

namespace App\Http\Resources\Activity;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\Activity\ActivityResource;
use Illuminate\Http\Request;

class ActivityCollection extends ResourceCollection
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
            'activities' => $this->collection->map(function ($activities) {
                return new ActivityResource($activities, $this->locale);
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
