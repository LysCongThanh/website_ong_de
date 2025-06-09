<?php

namespace App\Models;

use App\Traits\Embeddable;
use App\Traits\Trackable;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed $question
 * @property mixed $embedding
 */
class Faq extends Model
{
    use HasFactory, SoftDeletes, Translatable, Trackable, Embeddable;

    protected $fillable = [
        'question',
        'answer_plain',
        'answer_html',
        'example_questions',
        'is_active',
        'show_on_website',
        'use_for_ai',
        'created_by',
        'last_updated_by',
    ];

    protected $casts = [
        'example_questions' => 'array',
        'is_active' => 'boolean',
        'show_on_website' => 'boolean',
        'use_for_ai' => 'boolean'
    ];

    public function translatedAttributes(): array
    {
        return [
            'question',
            'answer_plain',
            'answer_html',
        ];
    }

    public function getTextForEmbedding(): string
    {
        return "{$this->question}";
    }
}
