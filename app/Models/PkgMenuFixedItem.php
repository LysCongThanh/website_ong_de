<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PkgMenuFixedItem extends Model
{
    use HasFactory, Translatable;

    protected $fillable = [
        'package_menu_id',
        'name',
        'unit',
        'quantity'
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(PkgMenu::class, 'package_menu_id');
    }

    public function translatedAttributes(): array
    {
        return [
            'name',
            'unit'
        ];
    }
}
