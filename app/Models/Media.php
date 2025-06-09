<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_url',
        'file_type',
        'mime_type',
        'model_id',
        'model_type',
        'alt_text',
        'is_primary',
        'file_size',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
