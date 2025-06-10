<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TicketCategory extends Model
{
    use HasFactory, Translatable, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_category_relations');
    }

    public function translatedAttributes(): array
    {
        return [
            'name',
            'description',
        ];
    }
}
