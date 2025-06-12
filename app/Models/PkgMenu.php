<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PkgMenu extends Model
{
    use HasFactory, Translatable;

    protected $fillable = [
        'package_id',
        'name',
        'type',
        'description',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(PkgMenuOption::class, 'package_menu_id');
    }

    public function translatedAttributes(): array
    {
        return ['name', 'description', 'type'];
    }
}
