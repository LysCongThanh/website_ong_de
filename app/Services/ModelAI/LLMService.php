<?php

namespace App\Services\ModelAI;

class LMService
{
    protected string $apiEndpoint;
    protected string $model;
    protected int $cacheTtl = 3600;

    protected array $defaultConfig = [
        'temperature' => 0.1,
        'top_p' => 0.9,
        'top_k' => 40,
        'num_predict' => 50,
        'repeat_penalty' => 1.1,
        'stop' => ["\n\n"]
    ];

    public function __construct()
    {
        $this->apiEndpoint = config('llm.ollama.endpoint');
        $this->model = config('llm.ollama.model', 'gemma:2b');
    }

}