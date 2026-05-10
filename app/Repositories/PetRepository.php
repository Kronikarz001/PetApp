<?php

namespace App\Repositories;

use App\DTOs\PetDTO;
use App\Exceptions\PetApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Summary of PetRepository
 */
class PetRepository implements PetRepositoryInterface
{
    /**
     * @param string $baseUrl
     */
    public function __construct(
        private readonly string $baseUrl,
    ) {}

    /**
     * @param int $id
     * @return PetDTO
     * @throws PetApiException
     */
    public function findById(int $id): PetDTO
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/pet/{$id}");

            $this->handleErrorResponse($response);

            return PetDTO::fromArray($response->json());
        } catch (ConnectionException $e) {
            throw new PetApiException('Nie można połączyć się z API. Spróbuj ponownie później.');
        }
    }

    /**
     * @param string $status
     * @return PetDTO[]
     * @throws PetApiException
     */
    public function findByStatus(string $status): array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/pet/findByStatus", [
                'status' => $status,
            ]);

            $this->handleErrorResponse($response);

            return collect($response->json())
                ->map(fn(array $pet) => PetDTO::fromArray($pet))
                ->toArray();
        } catch (ConnectionException $e) {
            throw new PetApiException('Nie można połączyć się z API. Spróbuj ponownie później.');
        }
    }

    /**
     * @param PetDTO $petDTO
     * @return PetDTO
     * @throws PetApiException
     */
    public function create(PetDTO $petDTO): PetDTO
    {
        try {
            $response = Http::timeout(10)->asJson()->post("{$this->baseUrl}/pet", $petDTO->toArray());

            $this->handleErrorResponse($response);

            return PetDTO::fromArray($response->json());
        } catch (ConnectionException $e) {
            throw new PetApiException('Nie można połączyć się z API. Spróbuj ponownie później.');
        }
    }

    /**
     * @param PetDTO $petDTO
     * @return PetDTO
     * @throws PetApiException
     */
    public function update(PetDTO $petDTO): PetDTO
    {
        try {
            $response = Http::timeout(10)->asJson()->put("{$this->baseUrl}/pet", $petDTO->toArray());

            $this->handleErrorResponse($response);

            return PetDTO::fromArray($response->json());
        } catch (ConnectionException $e) {
            Log::error('PetStore API unreachable on update', ['id' => $petDTO->id, 'error' => $e->getMessage()]);
            throw new PetApiException('Nie można połączyć się z API. Spróbuj ponownie później.');
        }
    }

    /**
     * @param int $id
     * @return bool
     * @throws PetApiException
     */
    public function delete(int $id): bool
    {
        try {
            $response = Http::timeout(10)->delete("{$this->baseUrl}/pet/{$id}");

            if ($response->status() === 404) {
                return true;
            }

            $this->handleErrorResponse($response);

            return $response->successful();
        } catch (ConnectionException $e) {
            Log::error('PetStore API unreachable on delete', ['id' => $id, 'error' => $e->getMessage()]);
            throw new PetApiException('Nie można połączyć się z API. Spróbuj ponownie później.');
        }
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

        Log::warning('PetStore API error', ['status' => $statusCode, 'body' => $response->body()]);

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
}
