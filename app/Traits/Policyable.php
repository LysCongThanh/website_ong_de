<?php

namespace App\Traits;

use App\Models\Policy;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Policyable
{
    public function policies(): MorphMany
    {
        return $this->morphMany(Policy::class, 'policyable', 'policyable_type', 'policyable_id');
    }
}