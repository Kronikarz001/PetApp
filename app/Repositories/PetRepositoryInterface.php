<?php

namespace App\Repositories;

use App\DTOs\PetDTO;

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
}
