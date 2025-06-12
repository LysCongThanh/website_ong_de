<?php

namespace App\Http\Resources\Activity;

use App\Models\BasePrice;
use App\Models\CustomerCapacityPrice;
use App\Models\CustomerSegmentPrice;
use App\Traits\HasResourceFormat;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $updated_at
 * @property int $id
 * @property BasePrice $basePrices
 * @property CustomerCapacityPrice $capacityPrices
 * @property CustomerSegmentPrice $segmentPrices
 */
class ActivityResource extends JsonResource
{
    use HasResourceFormat;

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslatedAttribute('name'),
            'slug' => $this->slug,
            'short_description' => $this->getTranslatedAttribute('short_description'),
            'long_description' => $this->getTranslatedAttribute('long_description'),
            'main_image' => $this->resource->getFirstMediaUrl('main_image'),
            'images' => $this->resource->getMedia('gallery')->map(function ($media) {
                return $media->getUrl();
            }),
            'conditions' => $this->getTranslatedAttribute('conditions'),
            'location_area' => $this->getTranslatedAttribute('location_area'),
            'min_participants' => $this->min_participants,
            'max_participants' => $this->max_participants,
            'base_prices' => $this->formatBasePrices(),
            'capacity_prices' => $this->formatCapacityPrices(),
            'segment_prices' => $this->formatSegmentPrices(),
            'policies' => $this->formatPolicies(),
        ];
    }
}
