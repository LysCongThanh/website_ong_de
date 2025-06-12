<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageService extends Model
{
    use HasFactory, Translatable;

    protected $fillable = [
        'package_id',
        'name',
        'description',
        'icon',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function translatedAttributes(): array
    {
        return ['name', 'description'];
    }
}
