<?php

namespace App\Models;

use App\Traits\Embeddable;
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

/**
 * @property string $title
 */
class Package extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, HasSlug, Translatable, Trackable, HasFlexiblePricing, InteractsWithMedia, Policyable, Embeddable;

    protected $fillable = [
        'title',
        'code',
        'type',
        'summary',
        'content',
        'duration',
        'min_quantity',
        'is_featured',
        'slug',
        'keywords',
        'meta_description',
        'available_start',
        'available_end',
        'conditions',
        'is_active',
        'created_by',
        'last_updated_by',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_featured' => 'boolean',
        'available_start' => 'datetime',
        'available_end' => 'datetime',
        'min_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main_image')->singleFile();
    }

    protected static function booted(): void
    {
        static::deleting(function ($activity) {
            $activity->clearMediaCollection('main_image');
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
        return $this->belongsToMany(PkgCategory::class, 'pkg_category_relations', 'package_id', 'category_id');
    }

    public function menus(): HasMany
    {
        return $this->hasMany(PkgMenu::class);
    }

    public function translatedAttributes(): array
    {
        return [
            'title',
            'summary',
            'content',
            'duration',
            'conditions',
        ];
    }

    public function getTextForEmbedding(): string
    {
        return "{$this->title}";
    }
}
