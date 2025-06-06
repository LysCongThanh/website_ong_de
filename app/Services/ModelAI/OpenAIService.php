<?php

namespace App\Services\ModelAI;

use App\Constants\OpenAIModel;
use App\Services\Chatbot\ConversationRedisService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use JetBrains\PhpStorm\ArrayShape;
use OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use Psr\Log\LoggerInterface;

class OpenAIService
{
    private OpenAI\Client $client;
    private const EMBEDDING_PREFIX = 'embedding:';
    private const RELEVANCE_SCORE = .4;

    private int $embeddingCacheTTL = 864000;
    private int $moderationCacheTTL = 864000;

    public function __construct(
//        private readonly ConversationRedisService $conversationService,
    )
    {
        $this->client = OpenAI::client(env('OPENAI_API_KEY'));
    }

    /**
     * @throws GuzzleException
     */
    public function callChat(array $messages, string $model = 'gpt-3.5-turbo', float $temperature = 0.7, ?int $maxTokens = 150, $functions = [], ?string $reasoningEffort = null): ?CreateResponse
    {
        $configs = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'response_format' => [
                'type' => 'text'
            ],
        ];

        if ($maxTokens !== null) {
            $configs['max_tokens'] = $maxTokens;
        }

        if ($model === OpenAIModel::GPT_O3_MINI) {
            $reasoning = $reasoningEffort ?? 'medium';
            $configs['reasoning_effort'] = $reasoning;
        }

        try {
            return $this->client->chat()->create($configs);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }

        return null;
    }

    /**@return array{status: string, function_call: mixed|null, function_name: mixed|null, arguments: mixed|null, content: mixed|null, raw_response: CreateResponse}
     * @throws Exception
     */
    #[ArrayShape(shape: [
        'status' => "string",
        'function_call' => "mixed|null",
        'function_name' => "mixed|null",
        'arguments' => "mixed|null",
        'content' => "mixed|null",
        'raw_response' => "\OpenAI\Responses\Chat\CreateResponse"
    ])]
    public function executeFunctionCalling(
        string  $query,
        array   $functions,
        ?string $systemPrompt = null,
        ?string $forceFunctionName = null,
        string  $model = OpenAIModel::GPT_O3_MINI,
        array   $conversationHistory = [],
        ?string $reasoningEffort = null,
        ?float  $temperature = null,
        ?int    $maxTokens = null,
    ): array
    {
        try {
            $messages = [];

            if (!empty($systemPrompt)) {
                $messages[] = [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ];
            }

            if (!empty($conversationHistory)) {
                $messages = array_merge($messages, $conversationHistory);
            }

            $messages[] = [
                'role' => 'user',
                'content' => $query,
            ];

            $parameters = [
                'model' => $model,
                'messages' => $messages,
                'functions' => $functions,
            ];

            if ($reasoningEffort !== null) {
                $parameters['reasoning_effort'] = $reasoningEffort;
            }

            if ($forceFunctionName !== null) {
                $parameters['function_call'] = ['name' => $forceFunctionName];
            }

            if ($maxTokens !== null) {
                $parameters['max_completion_tokens'] = $maxTokens;
            }

            if ($temperature !== null) {
                $parameters['temperature'] = $temperature;
            }

            $response = $this->client->chat()->create($parameters);

            $responseData = $this->processFunctionCallingResponse($response);

            return [
                'status' => 'success',
                'function_call' => $responseData['function_call'] ?? null,
                'function_name' => $responseData['function_name'] ?? null,
                'arguments' => $responseData['arguments'] ?? null,
                'content' => $responseData['content'] ?? null,
                'raw_response' => $response,
            ];
        } catch (Exception $e) {
            Log::error('Function calling error: ' . $e->getMessage(), [
                'query' => $query,
                'functions' => array_map(fn($f) => $f['name'], $functions),
                'forceFunctionName' => $forceFunctionName,
            ]);

            throw new Exception('Failed to execute function calling: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Process the raw response from OpenAI's function calling API
     *
     * @param object $response The raw API response
     * @return array Processed data
     */
    private function processFunctionCallingResponse(object $response): array
    {
        $result = [];

        // Lấy message từ response
        $message = $response->choices[0]->message;

        // Kiểm tra nếu có function_call (định dạng đang được sử dụng bởi mô hình fine-tuned của bạn)
        if (isset($message->functionCall) || isset($message->function_call)) {
            // Xử lý tùy thuộc vào định dạng property name (functionCall hoặc function_call)
            $functionCall = $message->functionCall ?? $message->function_call;

            $result['function_call'] = true;
            $result['function_name'] = $functionCall->name;

            // Parse arguments từ JSON
            try {
                $result['arguments'] = json_decode($functionCall->arguments, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $result['arguments'] = [];
                Log::warning('Failed to parse function arguments: ' . $e->getMessage(), [
                    'arguments' => $functionCall->arguments ?? 'null'
                ]);
            }
        }
        // Kiểm tra nếu có tool_calls (định dạng mới, nhưng mô hình của bạn hiện không sử dụng)
        else if (isset($message->toolCalls) && !empty($message->toolCalls)) {
            $toolCall = $message->toolCalls[0]; // Lấy tool call đầu tiên

            if ($toolCall->type === 'function') {
                $functionCall = $toolCall->function;
                $result['function_call'] = true;
                $result['function_name'] = $functionCall->name;

                // Parse arguments
                try {
                    $result['arguments'] = json_decode($functionCall->arguments, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    $result['arguments'] = [];
                    Log::warning('Failed to parse function arguments: ' . $e->getMessage(), [
                        'arguments' => $functionCall->arguments ?? 'null'
                    ]);
                }
            }
        }
        // Nếu không có function call hoặc tool call
        else {
            $result['function_call'] = false;
            $result['content'] = $message->content;
        }

        return $result;
    }

    public function embedding($text, string $model = 'text-embedding-3-small'): ?array
    {
        try {
            $redisKey = $this->getEmbeddingKey($text, $model);

            $cachedEmbedding = Redis::get($redisKey);
            if ($cachedEmbedding) {
                return json_decode($cachedEmbedding, true);
            }

            $response = $this->client->embeddings()->create([
                'model' => $model,
                'input' => $text,
            ]);

            $embedding = $response->embeddings[0]->embedding;

            Redis::setex($redisKey, $this->embeddingCacheTTL, json_encode($embedding));

            Log::info('Embedding created and cached', [
                'text_preview' => substr($text, 0, 30) . '...',
                'model' => $model,
                'vector_size' => count($embedding)
            ]);

            return $embedding;
        } catch (OpenAI\Exceptions\ErrorException $e) {
            Log::error("Lỗi khi tạo embedding: " . $e->getMessage(), [
                'text_length' => strlen($text),
                'model' => $model
            ]);
        }

        return null;
    }

    public function embeddingBatch(array $texts, string $model = 'text-embedding-3-small'): ?array
    {
        try {
            if (empty($texts)) {
                return [];
            }

            $embeddings = [];
            $textsToProcess = [];
            $textIndices = [];

            foreach ($texts as $index => $text) {
                $redisKey = $this->getEmbeddingKey($text, $model);
                $cachedEmbedding = Redis::get($redisKey);

                if ($cachedEmbedding) {
                    $embeddings[$index] = json_decode($cachedEmbedding, true);
                } else {
                    $textsToProcess[] = $text;
                    $textIndices[] = $index;
                }
            }

            if (!empty($textsToProcess)) {
                $response = $this->client->embeddings()->create([
                    'model' => $model,
                    'input' => $textsToProcess,
                ]);

                foreach ($response->embeddings as $i => $embeddingData) {
                    $originalIndex = $textIndices[$i];
                    $embedding = $embeddingData->embedding;
                    $text = $textsToProcess[$i];

                    $redisKey = $this->getEmbeddingKey($text, $model);
                    Redis::setex($redisKey, $this->embeddingCacheTTL, json_encode($embedding));

                    $embeddings[$originalIndex] = $embedding;
                }

                Log::info('Batch embeddings created and cached', [
                    'total_texts' => count($texts),
                    'processed_texts' => count($textsToProcess),
                    'cached_texts' => count($texts) - count($textsToProcess),
                    'model' => $model,
                    'vector_size' => count($embeddings[0] ?? [])
                ]);
            }

            ksort($embeddings);
            return array_values($embeddings);

        } catch (OpenAI\Exceptions\ErrorException $e) {
            Log::error("Lỗi khi tạo batch embeddings: " . $e->getMessage(), [
                'text_count' => count($texts),
                'model' => $model
            ]);
        }

        return null;
    }

    private function getEmbeddingKey(string $text, string $model): string
    {
        return self::EMBEDDING_PREFIX . md5($text . '_' . $model);
    }

    public function setEmbeddingCacheTTL(int $seconds): void
    {
        $this->embeddingCacheTTL = $seconds;
    }

    public function checkContentModeration(string $content, string $model = 'omni-moderation-latest'): array
    {
        $cacheKey = 'moderation:' . md5($content . $model);

        if (Redis::exists($cacheKey)) {
            $cachedResult = Redis::get($cacheKey);
            return json_decode($cachedResult, true);
        }

        try {
            $response = $this->client->moderations()->create([
                'input' => $content,
                'model' => $model
            ]);

            $result = $response->toArray();

            if (isset($result['results'][0]['flagged']) && $result['results'][0]['flagged'] === true) {
                Log::info('Content flagged as inappropriate', ['content' => $content, 'result' => $result]);
            }

            Redis::set($cacheKey, json_encode($result), 'EX', $this->moderationCacheTTL);

            return $result;
        } catch (Exception $e) {
            Log::error("Moderation error: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}