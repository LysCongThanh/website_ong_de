<?php

namespace App\Http\Resources\Ticket;

use App\Models\BasePrice;
use App\Models\CustomerCapacityPrice;
use App\Models\CustomerSegmentPrice;
use App\Models\TicketCategory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property bool $is_active
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property TicketCategory $categories
 * @property int $id
 * @property BasePrice $basePrices
 * @property CustomerCapacityPrice $capacityPrices
 * @property CustomerSegmentPrice $segmentPrices
 */
class TicketResource extends JsonResource
{
    protected string $locale;

    public function __construct($resource, ?string $locale = 'vi')
    {
        parent::__construct($resource);
        $this->locale = $locale ?? 'vi';
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslatedAttribute('name'),
            'description' => $this->getTranslatedAttribute('description'),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'categories' => $this->formatCategories(),
            'base_prices' => $this->formatBasePrices(),
            'capacity_prices' => $this->formatCapacityPrices(),
            'segment_prices' => $this->formatSegmentPrices(),
        ];
    }

    protected function formatCategories()
    {
        return $this->categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $this->getTranslatedAttributeForModel($category, 'name'),
                'slug' => $category->slug,
                'description' => $this->getTranslatedAttributeForModel($category, 'description'),
            ];
        });
    }

    protected function formatBasePrices()
    {
        return $this->basePrices->map(function ($price) {
            return [
                'id' => $price->id,
                'price' => (float) $price->price,
                'is_active' => $price->is_active,
                'price_type' => [
                    'id' => $price->priceType->id,
                    'name' => $this->getTranslatedAttributeForModel($price->priceType, 'name'),
                    'description' => $this->getTranslatedAttributeForModel($price->priceType, 'description'),
                ],
            ];
        });
    }

    protected function formatCapacityPrices()
    {
        return $this->capacityPrices->map(function ($price) {
            return [
                'id' => $price->id,
                'price' => (float) $price->price,
                'is_active' => $price->is_active,
                'price_type' => [
                    'id' => $price->priceType->id,
                    'name' => $this->getTranslatedAttributeForModel($price->priceType, 'name'),
                    'description' => $this->getTranslatedAttributeForModel($price->priceType, 'description'),
                ],
                'customer_segment' => [
                    'id' => $price->customerSegment->id,
                    'name' => $this->getTranslatedAttributeForModel($price->customerSegment, 'name'),
                ],
            ];
        });
    }

    protected function formatSegmentPrices()
    {
        return $this->segmentPrices->map(function ($price) {
            return [
                'id' => $price->id,
                'price' => (float) $price->price,
                'is_active' => $price->is_active,
                'price_type' => [
                    'id' => $price->priceType->id,
                    'name' => $this->getTranslatedAttributeForModel($price->priceType, 'name'),
                    'description' => $this->getTranslatedAttributeForModel($price->priceType, 'description'),
                ],
                'customer_segment' => [
                    'id' => $price->customerSegment->id,
                    'name' => $this->getTranslatedAttributeForModel($price->customerSegment, 'name'),
                ],
            ];
        });
    }

    protected function getTranslatedAttribute($attribute)
    {
        return $this->getTranslatedAttributeForModel($this->resource, $attribute);
    }

    protected function getTranslatedAttributeForModel($model, $attribute)
    {
        if ($model->translations && $model->translations->isNotEmpty()) {
            $translation = $model->translations->first();
            if ($translation && isset($translation->translations[$attribute])) {
                return $translation->translations[$attribute];
            }
        }
        return $model->{$attribute} ?? null;
    }
}
