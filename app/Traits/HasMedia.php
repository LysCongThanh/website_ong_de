<?php

namespace App\Traits;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMedia
{
    protected static function bootHasMedia(): void
    {
        static::deleting(function ($model) {
            $model->media()->delete();
        });
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }

    /**
     * @return Model|MorphMany|object|null
     */
    public function primaryMedia()
    {
        return $this->media()->where('is_primary', true)->first();
    }
}