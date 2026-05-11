<?php

namespace App\Repositories;

use App\DTOs\PetDTO;
use App\Exceptions\PetApiException;
use App\Services\HttpService;
use Illuminate\Http\Client\ConnectionException;

/**
 * Summary of PetRepository
 */
readonly class PetRepository implements PetRepositoryInterface
{
    /**
     * @param HttpService $httpService
     */
    public function __construct(
        private HttpService $httpService,
    ) {}

    /**
     * @param string $id
     * @return PetDTO
     * @throws ConnectionException
     * @throws PetApiException
     */
    public function findById(string $id): PetDTO
    {
        $response = $this->httpService->request('GET', "/pet/{$id}");

        return PetDTO::fromArray($response->json());
    }

    /**
     * @param string $status
     * @return array|PetDTO[]
     * @throws ConnectionException
     * @throws PetApiException
     */
    public function findByStatus(string $status): array
    {
        $response = $this->httpService->request('GET', '/pet/findByStatus', ['status' => $status]);

        return collect($response->json())
            ->map(fn(array $pet) => PetDTO::fromArray($pet))
            ->toArray();
    }

    /**
     * @param PetDTO $petDTO
     * @return PetDTO
     * @throws ConnectionException
     * @throws PetApiException
     */
    public function create(PetDTO $petDTO): PetDTO
    {
        $response = $this->httpService->request('POST', '/pet', $petDTO->toArray());

        return PetDTO::fromArray($response->json());
    }

    /**
     * @param PetDTO $petDTO
     * @return void
     * @throws ConnectionException
     * @throws PetApiException
     */
    public function update(PetDTO $petDTO): void
    {
        $this->httpService->request('PUT', '/pet', $petDTO->toArray());
    }

    /**
     * @param string $id
     * @return void
     * @throws ConnectionException
     * @throws PetApiException
     */
    public function delete(string $id): void
    {
        $this->httpService->request('DELETE', "/pet/{$id}");
    }
}
