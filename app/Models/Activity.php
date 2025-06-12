<?php

namespace App\Models;

use App\Traits\HasFlexiblePricing;
use App\Traits\Policyable;
use App\Traits\Trackable;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    protected static function booted(): void
    {
        static::deleting(function ($activity) {
            $activity->clearMediaCollection('main_image');
            $activity->clearMediaCollection('gallery');
        });
    }

    public function services(): HasMany
    {
        return $this->hasMany(PackageService::class);
    }

    public function audiences(): BelongsToMany
    {
        return $this->belongsToMany(Audience::class, 'package_audiences');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(PkgCategory::class, 'pgk_category_relations', 'package_id', 'category_id');
    }

    public function menus(): HasMany
    {
        return $this->hasMany(PkgMenu::class);
    }

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
            ->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main_image')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function getIsAvailableAttribute(): bool
    {
        $now = now();

        if (!$this->available_start || !$this->available_end) {
            return true;
        }

        return $now->between($this->available_start, $this->available_end);
    }
}
