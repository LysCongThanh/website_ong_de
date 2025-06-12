<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Audience extends Model
{
    use HasFactory, Translatable;

    protected $fillable = [
        'name',
        'description',
        'icon',
    ];

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_audiences');
    }

    public function translatedAttributes(): array
    {
        return ['name', 'description'];
    }
}
