<?php

namespace App\Traits;

use App\Models\BasePrice;
use App\Models\CustomerCapacityPrice;
use App\Models\CustomerSegmentPrice;
use App\Models\PriceType;
use App\Models\CustomerSegment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFlexiblePricing
{
    /**
     * Get all base prices for this model
     */
    public function basePrices(): MorphMany
    {
        return $this->morphMany(BasePrice::class, 'priceable');
    }

    /**
     * Get all customer capacity prices for this model
     */
    public function capacityPrices(): MorphMany
    {
        return $this->morphMany(CustomerCapacityPrice::class, 'priceable');
    }

    /**
     * Get all customer segment prices for this model
     */
    public function segmentPrices(): MorphMany
    {
        return $this->morphMany(CustomerSegmentPrice::class, 'priceable');
    }

    /**
     * Get base price for a specific price type
     */
    public function getBasePriceForType(?int $priceTypeId = null): ?float
    {
        $query = $this->basePrices()->where('is_active', true);

        if ($priceTypeId) {
            $query->where('price_type_id', $priceTypeId);
        } else {
            // Get default price type
            $defaultPriceType = PriceType::where('is_default', true)
                ->where('is_active', true)
                ->first();

            if ($defaultPriceType) {
                $query->where('price_type_id', $defaultPriceType->id);
            }
        }

        return $query->first()?->price;
    }

    /**
     * Get capacity price for specific person count and price type
     */
    public function getCapacityPrice(int $personCount, ?int $priceTypeId = null): ?float
    {
        $query = $this->capacityPrices()
            ->where('is_active', true)
            ->where('min_person', '<=', $personCount)
            ->where(function ($q) use ($personCount) {
                $q->where('max_person', '>=', $personCount)
                    ->orWhereNull('max_person');
            });

        if ($priceTypeId) {
            $query->where('price_type_id', $priceTypeId);
        }

        return $query->orderBy('min_person', 'desc')->first()?->price;
    }

    /**
     * Get segment price for specific customer segment and price type
     */
    public function getSegmentPrice(int $customerSegmentId, ?int $priceTypeId = null): ?float
    {
        $query = $this->segmentPrices()
            ->where('is_active', true)
            ->where('customer_segment_id', $customerSegmentId);

        if ($priceTypeId) {
            $query->where('price_type_id', $priceTypeId);
        }

        return $query->first()?->price;
    }

    /**
     * Get the best applicable price based on context
     */
    public function getBestPrice(array $context = []): ?float
    {
        $priceTypeId = $context['price_type_id'] ?? null;
        $personCount = $context['person_count'] ?? null;
        $customerSegmentId = $context['customer_segment_id'] ?? null;

        // Priority: Segment Price > Capacity Price > Base Price
        if ($customerSegmentId) {
            $segmentPrice = $this->getSegmentPrice($customerSegmentId, $priceTypeId);
            if ($segmentPrice !== null) {
                return $segmentPrice;
            }
        }

        if ($personCount) {
            $capacityPrice = $this->getCapacityPrice($personCount, $priceTypeId);
            if ($capacityPrice !== null) {
                return $capacityPrice;
            }
        }

        return $this->getBasePriceForType($priceTypeId);
    }

    /**
     * Create or update base price
     */
    public function setBasePrice(float $price, ?int $priceTypeId = null)
    {
        $basePrice = $this->basePrices()
            ->where('price_type_id', $priceTypeId)
            ->first();

        if ($basePrice) {
            $basePrice->update(['price' => $price]);
        } else {
            $basePrice = $this->basePrices()->create([
                'price_type_id' => $priceTypeId,
                'price' => $price,
            ]);
        }

        return $basePrice;
    }

    /**
     * Create or update capacity price
     */
    public function setCapacityPrice(
        float $price,
        int $minPerson,
        ?int $maxPerson = null,
        ?int $priceTypeId = null
    ) {
        $capacityPrice = $this->capacityPrices()
            ->where('min_person', $minPerson)
            ->where('max_person', $maxPerson)
            ->where('price_type_id', $priceTypeId)
            ->first();

        if ($capacityPrice) {
            $capacityPrice->update(['price' => $price]);
        } else {
            $capacityPrice = $this->capacityPrices()->create([
                'min_person' => $minPerson,
                'max_person' => $maxPerson,
                'price_type_id' => $priceTypeId,
                'price' => $price,
            ]);
        }

        return $capacityPrice;
    }

    /**
     * Create or update segment price
     */
    public function setSegmentPrice(
        float $price,
        int $customerSegmentId,
        ?int $priceTypeId = null
    ) {
        $segmentPrice = $this->segmentPrices()
            ->where('customer_segment_id', $customerSegmentId)
            ->where('price_type_id', $priceTypeId)
            ->first();

        if ($segmentPrice) {
            $segmentPrice->update(['price' => $price]);
        } else {
            $segmentPrice = $this->segmentPrices()->create([
                'customer_segment_id' => $customerSegmentId,
                'price_type_id' => $priceTypeId,
                'price' => $price,
            ]);
        }

        return $segmentPrice;
    }
}