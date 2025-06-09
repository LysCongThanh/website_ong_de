<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Policy extends Model
{
    use HasFactory, Translatable;

    protected $fillable = ['name', 'description', 'content', 'type', 'is_active', 'policyable_id', 'policyable_type'];

    public function translatedAttributes(): array
    {
        return [
            'name', 'description', 'content', 'type'
        ];
    }

    public function policyable(): MorphTo
    {
        return $this->morphTo('policyable');
    }
}
