<?php

namespace App\Services;

use App\DTOs\PetDTO;
use App\Repositories\PetRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

/**
 * Summary of PetService
 */
readonly class PetService implements PetServiceInterface
{
    /**
     * @param PetRepositoryInterface $petRepository
     */
    public function __construct(
        private PetRepositoryInterface $petRepository,
    ) {}

    /**
     * @param string $status
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPets(string $status, int $perPage = 15): LengthAwarePaginator
    {
        $all  = $this->petRepository->findByStatus($status);
        $page = Paginator::resolveCurrentPage();

        $items = collect($all)
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        return new Paginator(
            items:       $items,
            total:       count($all),
            perPage:     $perPage,
            currentPage: $page,
            options:     ['path' => Paginator::resolveCurrentPath()],
        );
    }

    /**
     * @param int $pet
     * @return PetDTO
     */
    public function getPet(int $pet): PetDTO
    {
        return $this->petRepository->findById($pet);
    }

    /**
     * @param array $data
     * @return PetDTO
     */
    public function createPet(array $data): PetDTO
    {
        $petDTO = new PetDTO(
            id:        null,
            name:      $data['name'],
            status:    $data['status'],
            photoUrls: $data['photoUrls'] ?? [],
            category:  isset($data['category']) ? [
                'id'   => $data['category']['id'] ?? 0,
                'name' => $data['category']['name'] ?? '',
            ] : null,
        );

        return $this->petRepository->create($petDTO);
    }

    /**
     * @param int $pet
     * @param array $data
     * @return void
     */
    public function updatePet(int $pet, array $data): void
    {
        $petDTO = new PetDTO(
            id:        $pet,
            name:      $data['name'],
            status:    $data['status'],
            photoUrls: $data['photoUrls'] ?? [],
            category:  isset($data['category']) ? [
                'id'   => $data['category']['id'] ?? 0,
                'name' => $data['category']['name'] ?? '',
            ] : null,
        );

        $this->petRepository->update($petDTO);
    }

    /**
     * @param int $pet
     * @return void
     */
    public function deletePet(int $pet): void
    {
        $this->petRepository->delete($pet);
    }
}
