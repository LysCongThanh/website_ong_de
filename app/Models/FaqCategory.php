<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqCategory extends Model
{
    use HasFactory, Translatable;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    public function translatedAttributes(): array
    {
        return ['name', 'description'];
    }
}
