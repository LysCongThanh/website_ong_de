<?php

namespace App\Constants;

class OpenAIModel
{
    // Completion/Chat Models
    public const GPT_3_5_TURBO = 'gpt-3.5-turbo';
    public const GPT_3_5_TURBO_16K = 'gpt-3.5-turbo-16k';
    public const GPT_4 = 'gpt-4';
    public const GPT_4_32K = 'gpt-4-32k';
    public const GPT_4_TURBO = 'gpt-4-turbo';
    public const GPT_4_TURBO_PREVIEW = 'gpt-4-turbo-preview';
    public const GPT_4O = 'gpt-4o';
    public const GPT_4O_MINI = 'gpt-4o-mini';
    public const GPT_O3_MINI = 'o3-mini-2025-01-31';
    public const GPT_4_1_MINI = 'gpt-4.1-mini';
    public const GPT_4_1_NANO = 'gpt-4.1-nano';
    public const GPT_4_1_DETERMINE_INTENT_FINE_TUNE = 'ft:gpt-4.1-mini-2025-04-14:ong-de:intent-detection:BSgtevnE';
    public const GPT_4_1_MINI_CHATBOT_CONSULTING = 'ft:gpt-4.1-mini-2025-04-14:ong-de:chatbot-consulting:BTpsmhuK';

    // Embedding Models
    public const TEXT_EMBEDDING_ADA_002 = 'text-embedding-ada-002';
    public const TEXT_EMBEDDING_3_SMALL = 'text-embedding-3-small';
    public const TEXT_EMBEDDING_3_LARGE = 'text-embedding-3-large';

    // Vision Models
    public const GPT_4_VISION = 'gpt-4-vision-preview';

    // Base Models
    public const DAVINCI = 'davinci';
    public const BABBAGE = 'babbage';
    public const CURIE = 'curie';
    public const ADA = 'ada';

    // Function Parameters
    public const DEFAULT_TEMPERATURE = 0.7;
    public const DEFAULT_MAX_TOKENS = 150;
    public const HIGH_ACCURACY_TEMPERATURE = 0.2;
    public const DEFAULT_TOP_P = 1.0;

    // Embedding Dimensions
    public const EMBEDDING_ADA_DIMENSIONS = 1536;
    public const EMBEDDING_3_SMALL_DIMENSIONS = 1536;
    public const EMBEDDING_3_LARGE_DIMENSIONS = 3072;

    /**
     * Get all available chat models
     *
     * @return array
     */
    public static function getChatModels(): array
    {
        return [
            self::GPT_O3_MINI,
            self::GPT_3_5_TURBO,
            self::GPT_3_5_TURBO_16K,
            self::GPT_4,
            self::GPT_4_32K,
            self::GPT_4_TURBO,
            self::GPT_4_TURBO_PREVIEW,
            self::GPT_4O,
            self::GPT_4O_MINI,
            self::GPT_4_1_DETERMINE_INTENT_FINE_TUNE
        ];
    }

    /**
     * Get all available embedding models
     *
     * @return array
     */
    public static function getEmbeddingModels(): array
    {
        return [
            self::TEXT_EMBEDDING_ADA_002,
            self::TEXT_EMBEDDING_3_SMALL,
            self::TEXT_EMBEDDING_3_LARGE,
        ];
    }

    /**
     * Get dimension size for a specific embedding model
     *
     * @param string $model
     * @return int|null
     */
    public static function getEmbeddingDimension(string $model): ?int
    {
        return match ($model) {
            self::TEXT_EMBEDDING_ADA_002 => self::EMBEDDING_ADA_DIMENSIONS,
            self::TEXT_EMBEDDING_3_SMALL => self::EMBEDDING_3_SMALL_DIMENSIONS,
            self::TEXT_EMBEDDING_3_LARGE => self::EMBEDDING_3_LARGE_DIMENSIONS,
            default => null,
        };
    }
}