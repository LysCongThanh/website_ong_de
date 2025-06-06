<?php

namespace App\Models;

use App\Traits\Trackable;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalService extends Model
{
    use HasFactory, SoftDeletes, Trackable, Translatable;

    protected $fillable = [
        'name',
        'short_description',
        'long_description',
        'conditions',
        'is_active',
        'created_by',
        'last_updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    public function translatedAttributes(): array
    {
        return [
            'name',
            'short_description',
            'long_description',
            'conditions',
        ];
    }
}
