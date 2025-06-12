<?php

namespace App\Repositories;

use App\Core\Abstracts\BaseRepository;
use App\Models\Ticket;
use App\Traits\HasRelationScopes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TicketRepository extends BaseRepository
{

    use HasRelationScopes;

    public function model(): string
    {
        return Ticket::class;
    }

    /**
     * Get a ticket by ID with related data.
     *
     * @param int $id
     * @param string|null $locale
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getTicketById(int $id, ?string $locale = 'vi')
    {
        $query = $this->buildTicketQuery($locale)
            ->where('is_active', true)
            ->where('id', $id);

        $ticket = $query->first();

        if (!$ticket) {
            throw new ModelNotFoundException("Ticket not found.");
        }

        return $ticket;
    }

    public function getAllTickets(
        ?string $locale = 'vi',
        int     $limit = 15,
        ?int    $categoryId = null
    ) {
        $query = $this->buildTicketQuery($locale, $categoryId)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc');

        return $query->paginate($limit);
    }

    /**
     * Build the base query for tickets with relations and filters.
     *
     * @param string|null $locale
     * @param int|null $categoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildTicketQuery(?string $locale = 'vi', ?int $categoryId = null)
    {
        $query = $this->model()::query()
            ->select([
                'id',
                'name',
                'description',
                'includes'
            ])
            ->with($this->getTicketRelations($locale));

        if ($categoryId !== null) {
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('ticket_categories.id', $categoryId);
            });
        }

        return $query;
    }

    /**
     * Define the relations to be loaded with the ticket query.
     *
     * @param string|null $locale
     * @return array
     */
    protected function getTicketRelations(?string $locale = 'vi')
    {
        return [
            'translations' => self::translationScope($locale),
            'categories' => fn($q) => $q->select([
                'ticket_categories.id', 'ticket_categories.name',
                'ticket_categories.slug', 'ticket_categories.description',
            ])->with([
                'translations' => self::translationScope($locale),
            ]),
            'policies' => self::policyRelations($locale),
            'basePrices' => self::basePriceRelations($locale),
            'capacityPrices' => self::capacityPriceRelations($locale),
            'segmentPrices' => self::segmentPriceRelations($locale),
        ];
    }
}
