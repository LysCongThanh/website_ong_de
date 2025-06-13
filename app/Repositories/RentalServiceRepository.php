<?php

namespace App\Repositories;
use App\Core\Abstracts\BaseRepository;
use App\Models\RentalService;
use App\Traits\HasRelationScopes;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RentalServiceRepository extends BaseRepository
{
    use HasRelationScopes;
    
    public function model(): string
    {
        return RentalService::class;
    }

    /**
     * Get a rental service by ID with related data.
     *
     * @param int $id
     * @param string|null $locale
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getRenltalServiceDetail(int|string $idOrSlug, ?string $locale = 'vi')
    {
        $query = $this->buildRentalServiceQuery($locale)
            ->where('is_active', true);

        if (is_numeric($idOrSlug)) {
            $query->where('id', (int) $idOrSlug);
        } else {
            $query->where('slug', $idOrSlug);
        }

        $rentalService = $query->first();

        if (!$rentalService) {
            throw new ModelNotFoundException("Rental Service not found.");
        }

        return $rentalService;
    }


    public function getAllRentalServices(
        ?string $locale = 'vi',
        int     $limit = 15,
    ) {
        $query = $this->buildRentalServiceQuery($locale)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc');

        return $query->paginate($limit);
    }

    /**
     * Build the base query for RentalServices with relations and filters.
     *
     * @param string|null $locale
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildRentalServiceQuery(?string $locale = 'vi')
    {
        $query = $this->model()::query()
            ->select([
                'id',
                'name',
                'slug',
                'short_description',
                'long_description',
                'conditions',
                'is_active',
            ])
            ->with($this->getRenltalServiceRelations($locale));

        return $query;
    }

    /**
     * Define the relations to be loaded with the rentalService query.
     *
     * @param string|null $locale
     * @return array
     */
    protected function getRenltalServiceRelations(?string $locale = 'vi')
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