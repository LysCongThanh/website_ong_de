<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmbeddingVector extends Model
{
    use HasFactory;

    protected $fillable = [
        'embeddable_id',
        'embeddable_type',
        'vector',
    ];

    public function embeddable(): MorphTo {
        return $this->morphTo()->where('status', true);
    }
}
