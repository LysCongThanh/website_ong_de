<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method map(\Closure $param)
 */
class CustomerSegmentPrice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'priceable_id',
        'priceable_type',
        'customer_segment_id',
        'price_type_id',
        'price',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function customerSegment(): BelongsTo
    {
        return $this->belongsTo(CustomerSegment::class);
    }

    public function priceable(): MorphTo
    {
        return $this->morphTo('priceable');
    }

    public function priceType(): BelongsTo
    {
        return $this->belongsTo(PriceType::class);
    }
}
