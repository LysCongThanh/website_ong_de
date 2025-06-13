<?php

namespace App\Models;

use App\Traits\HasFlexiblePricing;
use App\Traits\Policyable;
use App\Traits\Trackable;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class RentalService extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, HasSlug, Trackable, Translatable, Policyable, HasFlexiblePricing, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'long_description',
        'conditions',
        'is_active',
        'created_by',
        'last_updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function translatedAttributes(): array
    {
        return [
            'name',
            'short_description',
            'long_description',
            'conditions',
        ];
    }
}
