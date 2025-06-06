<?php

namespace App\Services\ModelAI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $geminiApiKey;

    protected string $geminiApiUrl;

    protected Client $httpClient;

    public function __construct()
    {
        $this->geminiApiKey = env('GEMINI_API_KEY');

        $this->geminiApiUrl = env('GEMINI_API_ENDPOINT');

        $this->httpClient = new Client;
    }

    /**
     * @throws GuzzleException
     */
    public function callGeminiAPI(string $prompt, float $temperature = .7, int $maxOutputToken = 200, int $timeOut = 3): ?string
    {
        try {
            $response = $this->httpClient->post($this->geminiApiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $this->geminiApiKey,
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [['text' => $prompt]],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => $temperature,
                        'maxOutputTokens' => $maxOutputToken,
                        'stopSequences' => ["\n\n"],
                    ],
                ],
                'timeout' => $timeOut,
            ]);

            $body = json_decode($response->getBody(), true);

            return $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

        } catch (\Exception $e) {
            Log::error('Lỗi khi gọi Gemini API: ' . $e->getMessage());
            return null;
        }
    }


    /**
     * @throws GuzzleException
     */
    public function generateTextEmbedding(string $text): ?array
    {
        try {
            $response = $this->httpClient->post("https://generativelanguage.googleapis.com/v1/models/text-embedding-004:embedContent?key={$this->geminiApiKey}", [
                'json' => [
                    "model" => "models/text-embedding-004",
                    "content" => [
                        "parts" => [["text" => $text]]
                    ],
                    "taskType" => "RETRIEVAL_DOCUMENT"
                ],
                'headers' => ['Content-Type' => 'application/json']
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['embedding']['values'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}