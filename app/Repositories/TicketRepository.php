<?php

namespace App\Repositories;

use App\Core\Abstracts\BaseRepository;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;

class TicketRepository extends BaseRepository
{

    public function model(): string
    {
        return Ticket::class;
    }

    public function getAll(): Collection
    {
        return $this->all(['name', 'id']);
    }

    public function getAllTickets(
        ?string $locale = null,
        int     $limit = 15,
        bool    $paginate = true
    )
    {
        $locale = $locale ?? 'zh'; // Default to 'zh' if null

        $query = $this->model()::query()
            ->select([
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->with([
                'translations' => fn($q) => $q->select(['id', 'entity_id', 'entity_type', 'locale', 'translations'])
                    ->where('locale', $locale),
                'categories' => fn($q) => $q->select([
                    'ticket_categories.id', // Explicitly specify ticket_categories.id
                    'ticket_categories.name',
                    'ticket_categories.slug',
                    'ticket_categories.description',
                ])
                    ->with([
                        'translations' => fn($tq) => $tq->select(['id', 'entity_id', 'entity_type', 'locale', 'translations'])
                            ->where('locale', $locale),
                    ]),
                'basePrices' => fn($q) => $q->select(['id', 'priceable_id', 'priceable_type', 'price_type_id', 'price', 'is_active'])
                    ->with([
                        'priceType' => fn($ptq) => $ptq->select(['id', 'name', 'description'])
                            ->with([
                                'translations' => fn($tq) => $tq->select(['id', 'entity_id', 'entity_type', 'locale', 'translations'])
                                    ->where('locale', $locale),
                            ]),
                    ]),
                'capacityPrices' => fn($q) => $q->select(['id', 'priceable_id', 'priceable_type', 'price_type_id', 'customer_segment_id', 'price', 'is_active'])
                    ->with([
                        'priceType' => fn($ptq) => $ptq->select(['id', 'name', 'description'])
                            ->with([
                                'translations' => fn($tq) => $tq->select(['id', 'entity_id', 'entity_type', 'locale', 'translations'])
                                    ->where('locale', $locale),
                            ]),
                        'customerSegment' => fn($csq) => $csq->select(['id', 'name'])
                            ->with([
                                'translations' => fn($tq) => $tq->select(['id', 'entity_id', 'entity_type', 'locale', 'translations'])
                                    ->where('locale', $locale),
                            ]),
                    ]),
                'segmentPrices' => fn($q) => $q->select(['id', 'priceable_id', 'priceable_type', 'price_type_id', 'customer_segment_id', 'price', 'is_active'])
                    ->with([
                        'priceType' => fn($ptq) => $ptq->select(['id', 'name', 'description'])
                            ->with([
                                'translations' => fn($tq) => $tq->select(['id', 'entity_id', 'entity_type', 'locale', 'translations'])
                                    ->where('locale', $locale),
                            ]),
                        'customerSegment' => fn($csq) => $csq->select(['id', 'name'])
                            ->with([
                                'translations' => fn($tq) => $tq->select(['id', 'entity_id', 'entity_type', 'locale', 'translations'])
                                    ->where('locale', $locale),
                            ]),
                    ]),
            ])
            ->where('is_active', true) // Only fetch active tickets
            ->orderBy('created_at', 'desc');

        return $paginate
            ? $query->paginate($limit)
            : $query->limit($limit)->get();
    }
}