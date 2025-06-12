<?php

namespace App\Repositories;

use App\Core\Abstracts\BaseRepository;
use App\Models\Activity;
use App\Traits\HasRelationScopes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActivityRepository extends BaseRepository
{
    use HasRelationScopes;

    public function model(): string
    {
        return Activity::class;
    }

    /**
     * Get a activity by ID with related data.
     *
     * @param int $id
     * @param string|null $locale
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getActivityDetail(int|string $idOrSlug, ?string $locale = 'vi')
    {
        $query = $this->buildActivityQuery($locale)
            ->where('is_active', true);

        if (is_numeric($idOrSlug)) {
            $query->where('id', (int) $idOrSlug);
        } else {
            $query->where('slug', $idOrSlug);
        }

        $activity = $query->first();

        if (!$activity) {
            throw new ModelNotFoundException("Activity not found.");
        }

        return $activity;
    }


    public function getAllActivities(
        ?string $locale = 'vi',
        int     $limit = 15,
    ) {
        $query = $this->buildActivityQuery($locale)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc');

        return $query->paginate($limit);
    }

    /**
     * Build the base query for activities with relations and filters.
     *
     * @param string|null $locale
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildActivityQuery(?string $locale = 'vi')
    {
        $query = $this->model()::query()
            ->select([
                'id',
                'name',
                'slug',
                'short_description',
                'long_description',
                'conditions',
                'location_area',
                'min_participants',
                'max_participants',
                'is_active',
            ])
            ->with($this->getActivityRelations($locale));

        return $query;
    }

    /**
     * Define the relations to be loaded with the activity query.
     *
     * @param string|null $locale
     * @return array
     */
    protected function getActivityRelations(?string $locale = 'vi')
    {
        return [
            'translations' => self::translationScope($locale),
            'policies' => self::policyRelations($locale),
            'basePrices' => self::basePriceRelations($locale),
            'capacityPrices' => self::capacityPriceRelations($locale),
            'segmentPrices' => self::segmentPriceRelations($locale),
        ];
    }
}
