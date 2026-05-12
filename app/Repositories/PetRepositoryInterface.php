<?php

namespace App\Repositories;

use App\DTOs\PetDTO;
use Illuminate\Http\UploadedFile;

/**
 * Summary of PetRepositoryInterface
 */
interface PetRepositoryInterface
{
    /**
     * @param string $id
     * @return PetDTO
     */
    public function findById(string $id): PetDTO;

    /**
     * @param string $status
     * @return PetDTO[]
     */
    public function findByStatus(string $status): array;

    /**
     * @param PetDTO $petDTO
     * @return PetDTO
     */
    public function create(PetDTO $petDTO): PetDTO;

    /**
     * @param PetDTO $petDTO
     * @return void
     */
    public function update(PetDTO $petDTO): void;

    /**
     * @param string $id
     * @return void
     */
    public function delete(string $id): void;

    /**
     * @param string $id
     * @param UploadedFile $file
     * @param string|null $additionalMetadata
     * @return array
     */
    public function uploadFile(string $id, UploadedFile $file, ?string $additionalMetadata = null): array;
}
