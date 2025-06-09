<?php

namespace App\Models;

use App\Traits\HasMedia;
use App\Traits\Policyable;
use App\Traits\Trackable;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes, Trackable, Translatable, HasMedia, Policyable;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'long_description',
        'conditions',
        'location_area',
        'min_participants',
        'max_participants',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_participants' => 'integer',
        'max_participants' => 'integer',
    ];

    public function translatedAttributes(): array
    {
        return [
            'name',
            'short_description',
            'long_description',
            'conditions',
            'location_area'
        ];
    }
}
