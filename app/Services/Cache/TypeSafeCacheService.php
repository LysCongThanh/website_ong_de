<?php

namespace App\Services\Cache;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;

class TypeSafeCacheService
{
    protected RedisService $redisService;

    public function __construct(RedisService $redisService)
    {
        $this->redisService = $redisService;
    }

    public function remember(string $key, \Closure $callback, int $ttl = 3600)
    {
        $cached = $this->redisService->get($key);

        if ($cached !== null) {
            return $this->deserialize($cached);
        }

        $fresh = $callback();

        if ($fresh !== null && $fresh !== false) {
            $serialized = $this->serialize($fresh);
            $this->redisService->set($key, $serialized, $ttl);
        }

        return $fresh;
    }

    public function forget(string $key): bool
    {
        return $this->redisService->delete($key);
    }

    public function forgetByPattern(string $pattern): int
    {
        return $this->redisService->deleteByPattern($pattern);
    }

    protected function serialize($data): string
    {
        $metadata = [
            'original_type' => $this->getDataType($data),
            'cached_at' => now()->toISOString(),
        ];

        switch ($metadata['original_type']) {
            case 'eloquent_collection':
                $metadata['model_class'] = $data->isNotEmpty() ? get_class($data->first()) : null;
                $metadata['data'] = $this->serializeEloquentCollection($data);
                break;

            case 'paginator':
                $collection = $data->getCollection();
                $metadata['model_class'] = $collection->isNotEmpty() ? get_class($collection->first()) : null;
                $metadata['data'] = $this->serializeEloquentCollection($collection);
                $metadata['pagination'] = [
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem(),
                    'path' => $data->path(),
                ];
                break;

            case 'support_collection':
                $metadata['data'] = $data->toArray();
                break;

            case 'eloquent_model':
                $metadata['model_class'] = get_class($data);
                $metadata['data'] = $this->serializeEloquentModel($data);
                break;

            default:
                $metadata['data'] = $data;
        }

        return json_encode($metadata, JSON_PRESERVE_ZERO_FRACTION);
    }

    protected function deserialize(string $cached)
    {
        $metadata = json_decode($cached, true);

        if (!is_array($metadata) || !isset($metadata['original_type'])) {
            return $metadata;
        }

        switch ($metadata['original_type']) {
            case 'eloquent_collection':
                return $this->deserializeEloquentCollection($metadata);

            case 'paginator':
                return $this->deserializePaginator($metadata);

            case 'support_collection':
                return collect($metadata['data']);

            case 'eloquent_model':
                return $this->deserializeEloquentModel($metadata);

            default:
                return $metadata['data'];
        }
    }

    protected function serializeEloquentCollection(Collection $collection): array
    {
        return $collection->map(function ($model) {
            return $this->serializeEloquentModel($model);
        })->toArray();
    }

    protected function serializeEloquentModel(Model $model): array
    {
        $data = [
            'attributes' => $model->getAttributes(),
            'relations' => [],
            'exists' => $model->exists,
            'was_recently_created' => $model->wasRecentlyCreated,
        ];

        // Safely get original attributes
        try {
            $data['original'] = $model->getOriginal();
        } catch (\Exception $e) {
            // If getting original fails, skip it
            $data['original'] = [];
        }

        // Serialize relationships
        foreach ($model->getRelations() as $relationName => $relationValue) {
            if ($relationValue instanceof Collection) {
                $data['relations'][$relationName] = [
                    'type' => 'collection',
                    'model_class' => $relationValue->isNotEmpty() ? get_class($relationValue->first()) : null,
                    'data' => $this->serializeEloquentCollection($relationValue),
                ];
            } elseif ($relationValue instanceof Model) {
                $data['relations'][$relationName] = [
                    'type' => 'model',
                    'model_class' => get_class($relationValue),
                    'data' => $this->serializeEloquentModel($relationValue),
                ];
            } elseif ($relationValue instanceof SupportCollection) {
                $data['relations'][$relationName] = [
                    'type' => 'support_collection',
                    'data' => $relationValue->toArray(),
                ];
            } else {
                $data['relations'][$relationName] = [
                    'type' => 'raw',
                    'data' => $relationValue,
                ];
            }
        }

        return $data;
    }

    protected function deserializeEloquentCollection(array $metadata): Collection
    {
        if (!$metadata['model_class'] || !class_exists($metadata['model_class'])) {
            return collect($metadata['data']);
        }

        $items = collect($metadata['data'])->map(function ($itemData) use ($metadata) {
            return $this->reconstructEloquentModel($metadata['model_class'], $itemData);
        });

        return new Collection($items);
    }

    protected function deserializePaginator(array $metadata): LengthAwarePaginator
    {
        $items = collect($metadata['data']);

        // Reconstruct Eloquent models if model class exists
        if ($metadata['model_class'] && class_exists($metadata['model_class'])) {
            $items = $items->map(function ($itemData) use ($metadata) {
                return $this->reconstructEloquentModel($metadata['model_class'], $itemData);
            });
        }

        return new LengthAwarePaginator(
            $items,
            $metadata['pagination']['total'],
            $metadata['pagination']['per_page'],
            $metadata['pagination']['current_page'],
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    protected function deserializeEloquentModel(array $metadata): ?Model
    {
        if (!$metadata['model_class'] || !class_exists($metadata['model_class'])) {
            return null;
        }

        return $this->reconstructEloquentModel($metadata['model_class'], $metadata['data']);
    }

    protected function reconstructEloquentModel(string $modelClass, array $data): Model
    {
        // Create new model instance
        $model = new $modelClass;

        // Set attributes safely
        try {
            $model->setRawAttributes($data['attributes'], true);
        } catch (\Exception $e) {
            // If setting raw attributes fails, try setting them one by one
            foreach ($data['attributes'] as $key => $value) {
                try {
                    $model->setAttribute($key, $value);
                } catch (\Exception $e) {
                    // Skip problematic attributes (like dynamic pivot attributes)
                    continue;
                }
            }
        }

        // Set original attributes safely
        if (!empty($data['original'])) {
            try {
                $reflection = new \ReflectionClass($model);
                if ($reflection->hasProperty('original')) {
                    $originalProperty = $reflection->getProperty('original');
                    $originalProperty->setValue($model, $data['original']);
                }
            } catch (\Exception $e) {
                // If reflection fails, skip original attributes
            }
        }

        // Set exists and was_recently_created flags
        $model->exists = $data['exists'] ?? false;
        if (isset($data['was_recently_created'])) {
            $model->wasRecentlyCreated = $data['was_recently_created'];
        }

        // Reconstruct relationships
        if (isset($data['relations'])) {
            foreach ($data['relations'] as $relationName => $relationData) {
                try {
                    $relationValue = $this->reconstructRelation($relationData);
                    $model->setRelation($relationName, $relationValue);
                } catch (\Exception $e) {
                    // Skip problematic relations
                    continue;
                }
            }
        }

        return $model;
    }

    protected function reconstructRelation(array $relationData)
    {
        switch ($relationData['type']) {
            case 'collection':
                if ($relationData['model_class'] && class_exists($relationData['model_class'])) {
                    $items = collect($relationData['data'])->map(function ($itemData) use ($relationData) {
                        return $this->reconstructEloquentModel($relationData['model_class'], $itemData);
                    });
                    return new Collection($items);
                }
                return collect($relationData['data']);

            case 'model':
                if ($relationData['model_class'] && class_exists($relationData['model_class'])) {
                    return $this->reconstructEloquentModel($relationData['model_class'], $relationData['data']);
                }
                return null;

            case 'support_collection':
                return collect($relationData['data']);

            default:
                return $relationData['data'];
        }
    }

    protected function getDataType($data): string
    {
        if ($data instanceof Collection) {
            return 'eloquent_collection';
        }

        if ($data instanceof LengthAwarePaginator) {
            return 'paginator';
        }

        if ($data instanceof Model) {
            return 'eloquent_model';
        }

        if ($data instanceof SupportCollection) {
            return 'support_collection';
        }

        return 'raw';
    }
}