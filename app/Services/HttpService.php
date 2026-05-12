<?php

namespace App\Services;

use App\Exceptions\PetApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

/**
 * Summary of HttpService
 */
readonly class HttpService
{
    /**
     * @param string $baseUrl
     */
    public function __construct(
        private string $baseUrl,
    ) {}

    /**
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @return Response
     * @throws PetApiException
     * @throws ConnectionException
     */
    public function request(string $method, string $endpoint, array $data = []): Response
    {
        $http = Http::timeout(10)->asJson();

        $response = match ($method) {
            'GET'    => $http->get("{$this->baseUrl}{$endpoint}", $data),
            'POST'   => $http->post("{$this->baseUrl}{$endpoint}", $data),
            'PUT'    => $http->put("{$this->baseUrl}{$endpoint}", $data),
            'DELETE' => $http->delete("{$this->baseUrl}{$endpoint}"),
            default  => throw new PetApiException("Unsupported HTTP method: {$method}"),
        };

        $this->handleErrorResponse($response);

        return $response;
    }

    /**
     * @param Response $response
     * @return void
     * @throws PetApiException
     */
    private function handleErrorResponse(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        $statusCode = $response->status();

        $message = match ($statusCode) {
            400     => 'Niepoprawne dane żądania.',
            404     => 'Nie znaleziono zwierzęcia o podanym ID.',
            405     => 'Metoda HTTP niedozwolona.',
            500,
            503     => 'Wewnętrzny błąd serwera API.',
            default => "Nieoczekiwany błąd API (HTTP {$statusCode}).",
        };

        throw new PetApiException($message, $statusCode, $response->body());
    }

    /**
     * @param string $endpoint
     * @param UploadedFile $file
     * @param string|null $additionalMetadata
     * @return Response
     * @throws PetApiException
     * @throws ConnectionException
     */
    public function uploadFile(string $endpoint, UploadedFile $file, ?string $additionalMetadata = null): Response
    {
        $http = Http::timeout(10)
            ->attach(
                'file',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            );

        if ($additionalMetadata !== null) {
            $http = $http->attach('additionalMetadata', $additionalMetadata);
        }

        $response = $http->post("{$this->baseUrl}{$endpoint}");

        $this->handleErrorResponse($response);

        return $response;
    }
}
