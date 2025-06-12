<?php

namespace App\Repositories;

use App\Core\Abstracts\BaseRepository;
use App\Models\TicketCategory;

class TicketCategoryRepository extends BaseRepository
{

    public function model(): string
    {
        return TicketCategory::class;
    }

    public function getCategories(?string $locale = 'vi', int $limit = 15)
    {
        $query = $this->model()::query()->select([
            'id',
            'name',
            'slug',
            'description',
        ])->with([
            'translations' => function ($query) use ($locale) {
                $query->where('locale', $locale);
            }
        ])->orderBy('created_at', 'desc');

        return $query->get($limit);
    }
}
