<?php

namespace App\Exceptions;

use App\Http\Resources\ErrorHandlerResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Summary of PetApiException
 */
class PetApiException extends Exception
{
    /**
     * @param string $message
     * @param int $statusCode
     * @param string|null $responseBody
     */
    public function __construct(
        string                   $message,
        private readonly int     $statusCode = 500,
        private readonly ?string $responseBody = null,
    ) {
        parent::__construct($message, $statusCode);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string|null
     */
    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function render(Request $request): JsonResponse
    {
        return ErrorHandlerResource::make($this)->response()->setStatusCode($this->statusCode);
    }
}
