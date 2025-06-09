<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerCapacityPrice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'priceable_id',
        'priceable_type',
        'price_type_id',
        'customer_segment_id',
        'min_person',
        'max_person',
        'price',
        'is_active',
    ];

    protected $casts = [
        'min_person' => 'integer',
        'max_person' => 'integer',
        'is_active' => 'boolean',
    ];

    public function priceable(): MorphTo
    {
        return $this->morphTo('priceable');
    }

    public function priceType(): BelongsTo
    {
        return $this->belongsTo(PriceType::class, 'price_type_id');
    }

    public function customerSegment(): BelongsTo {
        return $this->belongsTo(CustomerSegment::class, 'customer_segment_id');
    }
}
