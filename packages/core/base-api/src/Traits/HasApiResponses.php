<?php

namespace Eduardoks98\BaseApi\Traits;

use Illuminate\Http\JsonResponse;

trait HasApiResponses
{
    /**
     * Return a success response.
     *
     * @param mixed $data
     * @param int $code
     * @return JsonResponse
     */
    protected function success($data, int $code = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'code' => $code,
        ], $code);
    }

    /**
     * Return a created response (201).
     *
     * @param mixed $data
     * @return JsonResponse
     */
    protected function created($data): JsonResponse
    {
        return $this->success($data, 201);
    }

    /**
     * Return a no content response (204).
     *
     * @return JsonResponse
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return an error response.
     *
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function error(string $message, int $code = 400): JsonResponse
    {
        return response()->json([
            'error' => $message,
            'code' => $code,
        ], $code);
    }

    /**
     * Return a RFC 7807 Problem Details response.
     *
     * @param string $type
     * @param string $title
     * @param int $status
     * @param string $detail
     * @param string|null $instance
     * @param array $extensions
     * @return JsonResponse
     */
    protected function problemDetails(
        string $type,
        string $title,
        int $status,
        string $detail,
        ?string $instance = null,
        array $extensions = []
    ): JsonResponse {
        return problemDetails($type, $title, $status, $detail, $instance, $extensions);
    }
}
