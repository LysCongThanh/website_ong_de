<?php

namespace App\Models;

use App\Traits\HasFlexiblePricing;
use App\Traits\Policyable;
use App\Traits\Trackable;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes, Trackable, Translatable, HasFlexiblePricing, Policyable;

    protected $fillable = [
        'name',
        'description',
        'includes',
        'is_active',
        'created_by',
        'last_updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'includes' => 'array',
    ];

    public function categories(): BelongsToMany {
        return $this->belongsToMany(TicketCategory::class, 'ticket_category_relations');
    }

    public function translatedAttributes(): array
    {
        return [
            'name',
            'description',
            'includes',
        ];
    }
}
