<?php

namespace App\Traits;

trait HasResourceFormat
{
    protected string $locale = 'vi';

    /**
     * Set the locale for translations
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Get translated attribute for the current resource
     */
    protected function getTranslatedAttribute(string $attribute)
    {
        return $this->getTranslatedAttributeForModel($this->resource, $attribute);
    }

    /**
     * Get translated attribute for any model
     */
    protected function getTranslatedAttributeForModel($model, string $attribute)
    {
        if (!$model) {
            return null;
        }

        if ($model->translations && $model->translations->isNotEmpty()) {

            $translation = $model->translations->firstWhere('locale', $this->locale);
            

            if (!$translation) {
                $translation = $model->translations->first();
            }
            
            if ($translation && isset($translation->translations[$attribute])) {
                return $translation->translations[$attribute];
            }
        }

        return $model->{$attribute} ?? null;
    }

    /**
     * Get multiple translated attributes for a model
     */
    protected function getTranslatedAttributes($model, array $attributes): array
    {
        $result = [];
        foreach ($attributes as $attribute) {
            $result[$attribute] = $this->getTranslatedAttributeForModel($model, $attribute);
        }
        return $result;
    }

    /**
     * Format categories collection
     */
    protected function formatCategories($categories = null)
    {
        $categories = $categories ?? $this->categories;
        
        return $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $this->getTranslatedAttributeForModel($category, 'name'),
                'slug' => $category->slug,
                'description' => $this->getTranslatedAttributeForModel($category, 'description'),
            ];
        });
    }

    /**
     * Format policies collection
     */
    protected function formatPolicies($policies = null): \Illuminate\Support\Collection
    {
        $policies = $policies ?? $this->policies;
        
        if (!$policies) {
            return collect([]);
        }
        
        return $policies->map(function ($policy) {
            return [
                'id' => $policy->id,
                'name' => $this->getTranslatedAttributeForModel($policy, 'name'),
                'content' => $this->getTranslatedAttributeForModel($policy, 'content'),
                'description' => $this->getTranslatedAttributeForModel($policy, 'description'),
                'type' => $this->getTranslatedAttributeForModel($policy, 'type'),

            ];
        });
    }
    
    /**
     * Format base prices collection
     */
    protected function formatBasePrices($basePrices = null): \Illuminate\Support\Collection
    {
        $basePrices = $basePrices ?? $this->basePrices;
        
        if (!$basePrices) {
            return collect([]);
        }
        
        return $basePrices->map(function ($price) {
            return [
                'price' => (float) $price->price,
                'price_type' => $this->formatPriceType($price->priceType),
            ];
        });
    }

    /**
     * Format capacity prices collection
     */
    protected function formatCapacityPrices($capacityPrices = null): \Illuminate\Support\Collection
    {
        $capacityPrices = $capacityPrices ?? $this->capacityPrices;
        
        if (!$capacityPrices) {
            return collect([]);
        }
        
        return $capacityPrices->map(function ($price) {
            return [
                'price' => (float) $price->price,
                'min_person' => $price->min_person,
                'max_person' => $price->max_person,
                'price_type' => $this->formatPriceType($price->priceType),
                'customer_segment' => $this->formatCustomerSegment($price->customerSegment),
            ];
        });
    }

    /**
     * Format segment prices collection
     */
    protected function formatSegmentPrices($segmentPrices = null): \Illuminate\Support\Collection
    {
        $segmentPrices = $segmentPrices ?? $this->segmentPrices;
        
        if (!$segmentPrices) {
            return collect([]);
        }
        
        return $segmentPrices->map(function ($price) {
            return [
                'price' => (float) $price->price,
                'price_type' => $this->formatPriceType($price->priceType),
                'customer_segment' => $this->formatCustomerSegment($price->customerSegment),
            ];
        });
    }

    /**
     * Format price type information
     */
    protected function formatPriceType($priceType): array
    {
        if (!$priceType) {
            return [];
        }

        return [
            'id' => $priceType->id ?? null,
            'name' => $this->getTranslatedAttributeForModel($priceType, 'name'),
            'description' => $this->getTranslatedAttributeForModel($priceType, 'description'),
        ];
    }

    /**
     * Format customer segment information
     */
    protected function formatCustomerSegment($customerSegment): array
    {
        if (!$customerSegment) {
            return [];
        }

        return [
            'id' => $customerSegment->id ?? null,
            'name' => $this->getTranslatedAttributeForModel($customerSegment, 'name'),
            'description' => $this->getTranslatedAttributeForModel($customerSegment, 'description'),
        ];
    }

    /**
     * Format generic price with flexible structure
     */
    protected function formatPrice($price, array $includes = []): array
    {
        $result = [
            'price' => (float) $price->price,
        ];

        if (in_array('price_type', $includes) && isset($price->priceType)) {
            $result['price_type'] = $this->formatPriceType($price->priceType);
        }

        if (in_array('customer_segment', $includes) && isset($price->customerSegment)) {
            $result['customer_segment'] = $this->formatCustomerSegment($price->customerSegment);
        }

        return $result;
    }
}