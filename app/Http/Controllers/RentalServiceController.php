<?php

namespace App\Http\Controllers;

use App\Core\Abstracts\BaseController;
use App\Http\Resources\RentalService\RentalServiceCollection;
use App\Http\Resources\RentalService\RentalServiceResource;
use App\Services\DataServices\RentalServiceDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RentalServiceController extends BaseController
{
    public function __construct(
        private readonly RentalServiceDataService $rentalServiceDataService
    ) {}

    /**
     * Display a listing of the rental services.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $request->get('locale', 'vi');
        $limit = (int) $request->get('limit', 15);

        try {
            $rentalServices = $this->rentalServiceDataService->getAllRentalServices($locale, $limit);

            return $this->respondWithCollection(
                new RentalServiceCollection($rentalServices),
                'Rental Services retrieved successfully.'
            );
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    /**
     * Display the specified RentalService.
     *
     * @param int|string $idOrSlug
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int|string $idOrSlug, Request $request): JsonResponse
    {
        $locale = $request->get('locale', 'vi');

        try {
            $rentalService = $this->rentalServiceDataService->getRentalServiceDetail($idOrSlug, $locale);
            return $this->responseWithResource(new RentalServiceResource($rentalService), 'RentalService retrieved successfully.');
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
