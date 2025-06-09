<?php

namespace App\Traits;

use App\Models\EmbeddingVector;
use App\Services\ModelAI\OpenAIService;
use Illuminate\Support\Facades\Log;

/**
 * @method morphOne(string $class, string $string)
 * @method static deleted(\Closure $param)
 * @method static updated(\Closure $param)
 * @method static created(\Closure $param)
 */
trait Embeddable
{
    public static function bootEmbeddable(): void
    {
        static::created(function ($model) {
            $model->createEmbedding();
        });

        static::updated(function ($model) {
            $model->updateEmbedding();
        });

        static::forceDeleted(function ($model) {
            $model->deleteEmbedding();
        });
    }

    public function embedding() {
        return $this->morphOne(EmbeddingVector::class, 'embeddable');
    }

    public function createEmbedding(): void
    {
        Log::info('Create embeddings events');
        $text = $this->getTextForEmbedding();
        $vector = $this->generateEmbeddingVector($text);

        $this->embedding()->create([
            'vector' => json_encode($vector)
        ]);
    }

    public function updateEmbedding(): void
    {
        Log::info('update embeddings events');
        $text = $this->getTextForEmbedding();
        $vector = $this->generateEmbeddingVector($text);

        if ($this->embedding) {
            $this->embedding->update([
                'vector' => json_encode($vector)
            ]);
        } else {
            $this->createEmbedding();
        }
    }

    public function deleteEmbedding(): void
    {
        if ($this->embedding) {
            $this->embedding->delete();
        }
    }

    protected function generateEmbeddingVector($text)
    {
        $openAIService = app(OpenAIService::class);
        return $openAIService->embedding($text);
    }

    abstract public function getTextForEmbedding(): string;
}