<?php

namespace App\Repositories;

use App\DTOs\PetDTO;

/**
 * Summary of PetRepositoryInterface
 */
interface PetRepositoryInterface
{
    /**
     * @param int $id
     * @return PetDTO
     */
    public function findById(int $id): PetDTO;

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
     * @return PetDTO
     */
    public function update(PetDTO $petDTO): PetDTO;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
