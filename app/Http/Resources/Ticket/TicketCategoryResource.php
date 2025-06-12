<?php

namespace App\Http\Resources\Ticket;

use App\Traits\HasResourceFormat;
use Illuminate\Http\Resources\Json\JsonResource;


class TicketCategoryResource extends JsonResource
{
    use HasResourceFormat;

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslatedAttribute('name'),
            'slug' => $this->slug,
            'description' => $this->getTranslatedAttribute('description'),
        ];
    }
}
