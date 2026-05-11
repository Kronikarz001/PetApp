<?php

namespace App\Services;

use App\Dtos\PetDto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Summary of PetServiceInterface
 */
interface PetServiceInterface
{
    /**
     * @param string $status
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPets(string $status, int $perPage = 15): LengthAwarePaginator;

    /**
     * @param string $pet
     * @return PetDto
     */
    public function getPet(string $pet): PetDto;

    /**
     * @param array $data
     * @return PetDto
     */
    public function createPet(array $data): PetDto;

    /**
     * @param string $pet
     * @param array $data
     * @return void
     */
    public function updatePet(string $pet, array $data): void;

    /**
     * @param string $pet
     * @return void
     */
    public function deletePet(string $pet): void;


}
