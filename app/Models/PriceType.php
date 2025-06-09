<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static where(string $string, string $string1, $id)
 */
class PriceType extends Model
{
    use HasFactory, SoftDeletes, HasSlug, Translatable;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'priority',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($priceType) {
            if ($priceType->is_default) {
                static::where('id', '!=', $priceType->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function basePrices(): HasMany
    {
        return $this->hasMany(BasePrice::class);
    }

    public function customerSegmentPrices(): HasMany
    {
        return $this->hasMany(CustomerSegmentPrice::class);
    }

    public function customerCapacityPrices(): HasMany
    {
        return $this->hasMany(CustomerCapacityPrice::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function translatedAttributes(): array
    {
        return ['name', 'description'];
    }
}
