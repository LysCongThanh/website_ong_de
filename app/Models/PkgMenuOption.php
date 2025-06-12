<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PkgMenuOption extends Model
{
    use HasFactory, Translatable;

    protected $fillable = [
        'package_menu_id',
        'name',
        'description',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(PkgMenu::class, 'package_menu_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PkgMenuOptionItem::class);
    }

    public function translatedAttributes(): array
    {
        return ['name', 'description'];
    }
}
