<?php

namespace App\Models;

use App\Services\DataServices\TicketDataService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Application as ApplicationAlias;

/**
 * @method map(\Closure $param)
 * @property Application|ApplicationAlias|mixed $service
 */
class BasePrice extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot(): void {
        parent::boot();

        static::updated(function ($price) {
            if ($price->priceable && $price->priceable instanceof \App\Models\Ticket) {
                $service = app(TicketDataService::class);
                $service->clearCache('tickets:*');
            }

            if($price->priceable && $price->priceable instanceof \App\Models\Activity) {
                dd('activity');
            }
        });
    }

    protected $fillable = [
        'priceable_id',
        'priceable_type',
        'price_type_id',
        'price',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function priceable(): MorphTo
    {
        return $this->morphTo('priceable');
    }

    public function priceType(): BelongsTo
    {
        return $this->belongsTo(PriceType::class);
    }
}
