<?php

namespace App\Core\Abstracts;

use App\Core\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

abstract class BaseController extends Controller
{
    use ApiResponse;

    protected function responseWithResource($resource, $message, $code = Response::HTTP_OK): JsonResponse
    {
        return $this->successResponse($resource, $message, $code);
    }

    protected function respondWithCollection($collection, $message = '', $code = Response::HTTP_OK): JsonResponse
    {
        return $this->successResponse($collection, $message, $code);
    }

    protected function respondError($message, $code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return $this->errorResponse($message, $code);
    }

    protected function respondNotFound($message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_NOT_FOUND);
    }

    protected function respondUnauthorized($message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_UNAUTHORIZED);
    }

    protected function respondForbidden($message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_FORBIDDEN);
    }

    protected function respondValidationErrors($errors): JsonResponse
    {
        return $this->errorResponse(
            'Validation errors',
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $errors
        );
    }
}
