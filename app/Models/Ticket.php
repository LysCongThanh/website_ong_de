<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'includes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'includes' => 'array',
    ];

    public function categories(): BelongsToMany {
        return $this->belongsToMany(TicketCategory::class, 'ticket_category_relations');
    }
}
