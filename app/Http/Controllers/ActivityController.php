<?php

namespace App\Http\Controllers;

use App\Core\Abstracts\BaseController;
use App\Http\Resources\Activity\ActivityCollection;
use App\Http\Resources\Activity\ActivityResource;
use App\Services\DataServices\ActivityDataService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActivityController extends BaseController
{
    public function __construct(
        private readonly ActivityDataService $activityDataService
    ) {}

    /**
     * Display a listing of the activities.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $request->get('locale', 'vi');
        $limit = (int) $request->get('limit', 15);

        try {
            $activities = $this->activityDataService->getAllActivities($locale, $limit);

            return $this->respondWithCollection(
                new ActivityCollection($activities),
                'Activities retrieved successfully.'
            );
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    /**
     * Display the specified activity.
     *
     * @param int|string $idOrSlug
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int|string $idOrSlug, Request $request): JsonResponse
    {
        $locale = $request->get('locale', 'vi');

        try {
            $activity = $this->activityDataService->getActivityDetail($idOrSlug, $locale);
            return $this->responseWithResource(new ActivityResource($activity), 'Activity retrieved successfully.');
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
