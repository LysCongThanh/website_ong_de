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

class Activity extends Model  implements HasMedia
{
    use HasFactory, SoftDeletes, HasSlug, Trackable, Translatable, Policyable, InteractsWithMedia, HasFlexiblePricing;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'long_description',
        'conditions',
        'location_area',
        'min_participants',
        'max_participants',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_participants' => 'integer',
        'max_participants' => 'integer',
    ];

    public function translatedAttributes(): array
    {
        return [
            'name',
            'short_description',
            'long_description',
            'conditions',
            'location_area'
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main_image')->singleFile();
        $this->addMediaCollection('gallery');
    }

    protected static function booted(): void
    {
        static::deleting(function ($activity) {
            $activity->clearMediaCollection('main_image');
            $activity->clearMediaCollection('gallery');
        });
    }
}
