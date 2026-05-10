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
     * @param int $pet
     * @return PetDto
     */
    public function getPet(int $pet): PetDto;

    /**
     * @param array $data
     * @return PetDto
     */
    public function createPet(array $data): PetDto;

    /**
     * @param int $pet
     * @param array $data
     * @return void
     */
    public function updatePet(int $pet, array $data): void;

    /**
     * @param int $pet
     * @return void
     */
    public function deletePet(int $pet): void;


}
