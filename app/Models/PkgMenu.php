<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $menu_structure
 */
class PkgMenu extends Model
{
    use HasFactory, Translatable;

    protected $fillable = [
        'package_id',
        'name',
        'type',
        'description',
        'menu_structure'
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function fixedItems(): HasMany
    {
        return $this->hasMany(PkgMenuFixedItem::class, 'package_menu_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(PkgMenuOption::class, 'package_menu_id');
    }

    public function isFixedMenu(): bool
    {
        return $this->menu_structure === 'fixed';
    }

    public function isOptionsMenu(): bool
    {
        return $this->menu_structure === 'options';
    }

    public function translatedAttributes(): array
    {
        return ['name', 'description', 'type'];
    }
}
