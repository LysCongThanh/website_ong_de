<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PkgMenuOptionItem extends Model
{
    use HasFactory, Translatable;

    protected $fillable = [
        'menu_template_id',
        'name',
        'unit',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function option(): BelongsTo
    {
        return $this->belongsTo(PkgMenuOption::class);
    }

    public function translatedAttributes(): array
    {
        return ['name', 'unit', 'quantity'];
    }
}
