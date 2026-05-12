<?php

namespace App\Services;

use App\DTOs\PetDTO;
use App\Repositories\PetRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
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
            ->sortBy('id')
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
     * @param string $pet
     * @return PetDTO
     */
    public function getPet(string $pet): PetDTO
    {
        return $this->petRepository->findById($pet);
    }

    /**
     * @param array $data
     * @return PetDTO
     */
    public function createPet(array $data): PetDTO
    {
        return $this->petRepository->create(
            $this->mapDataToDTO($data)
        );
    }

    /**
     * @param string $pet
     * @param array $data
     * @return void
     */
    public function updatePet(string $pet, array $data): void
    {
        $this->petRepository->update(
            $this->mapDataToDTO($data, $pet)
        );
    }

    /**
     * @param string $pet
     * @return void
     */
    public function deletePet(string $pet): void
    {
        $this->petRepository->delete($pet);
    }

    /**
     * @param array $data
     * @param string|null $id
     * @return PetDTO
     */
    private function mapDataToDTO(array $data, ?string $id = null): PetDTO
    {
        return new PetDTO(
            id:        $id ? (int) $id : null,
            name:      $data['name'],
            status:    $data['status'],
            photoUrls: $data['photoUrls'] ?? [],
            category:  isset($data['category']) ? [
                'id'   => $data['category']['id'] ?? 0,
                'name' => $data['category']['name'] ?? '',
            ] : null,
        );
    }

    /**
     * @param string $id
     * @param UploadedFile $file
     * @param string|null $additionalMetadata
     * @return array
     */
    public function uploadFile(string $id, UploadedFile $file, ?string $additionalMetadata = null): array
    {
        return $this->petRepository->uploadFile($id, $file, $additionalMetadata);
    }
}
