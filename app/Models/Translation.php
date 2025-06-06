<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = ['entity_id', 'entity_type', 'locale', 'translations'];

    protected $casts = [
        'translations' => 'array',
    ];
}
