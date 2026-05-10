<?php

namespace Tests\Feature\Controllers;

use App\DTOs\PetDTO;
use App\Exceptions\PetApiException;
use App\Services\PetServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\MockObject\Exception;
use Tests\TestCase;

/**
 * Summary of PetControllerTest
 */
class PetControllerTest extends TestCase
{
    /**
     * @return PetServiceInterface
     * @throws Exception
     */
    private function mockPetService(): PetServiceInterface
    {
        $mock = $this->createMock(PetServiceInterface::class);
        $this->app->instance(PetServiceInterface::class, $mock);

        return $mock;
    }

    /**
     * @return PetDTO
     */
    private function makePetDTO(int $id = 1, string $name = 'Reksio', string $status = 'available'): PetDTO
    {
        return new PetDTO(id: $id, name: $name, status: $status);
    }

    /**
     * @return void
     */
    public function testIndexPetsShouldReturnSuccessResponse(): void
    {
        $mock = $this->mockPetService();
        $mock->method('getPets')->willReturn(
            new LengthAwarePaginator(
                items:       collect([$this->makePetDTO()]),
                total:       1,
                perPage:     15,
                currentPage: 1,
            )
        );

        $this->getJson(route('pets.index'))
            ->assertOk();
    }

    /**
     * @return void
     */
    public function testIndexPetsShouldReturnPaginatedStructure(): void
    {
        $mock = $this->mockPetService();
        $mock->method('getPets')->willReturn(
            new LengthAwarePaginator(
                items:       collect([$this->makePetDTO()]),
                total:       1,
                perPage:     15,
                currentPage: 1,
            )
        );

        $this->getJson(route('pets.index'))
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'last_page', 'total']);
    }

    /**
     * @return void
     */
    public function testShowPetShouldReturnSuccessResponse(): void
    {
        $mock = $this->mockPetService();
        $mock->method('getPet')
            ->with(1)
            ->willReturn($this->makePetDTO());

        $this->getJson(route('pets.show', ['pet' => 1]))
            ->assertOk();
    }

    /**
     * @return void
     */
    public function testShowPetShouldReturnNotFoundWhenPetDoesNotExist(): void
    {
        $mock = $this->mockPetService();
        $mock->method('getPet')
            ->willThrowException(new PetApiException('Nie znaleziono zwierzęcia o podanym ID.', 404));

        $this->getJson(route('pets.show', ['pet' => 999]))
            ->assertNotFound()
            ->assertJson(['message' => 'Nie znaleziono zwierzęcia o podanym ID.']);
    }

    /**
     * @return void
     */
    public function testStorePetShouldReturnCreatedResponse(): void
    {
        $mock = $this->mockPetService();
        $mock->method('createPet')
            ->willReturn($this->makePetDTO(id: 42, name: 'Burek'));

        $this->postJson(route('pets.store'), [
            'name'   => 'Burek',
            'status' => 'available',
        ])->assertCreated();
    }

    /**
     * @return void
     */
    public function testStorePetShouldReturnUnprocessableWhenNameMissing(): void
    {
        $this->postJson(route('pets.store'), [
            'status' => 'available',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * @return void
     */
    public function testStorePetShouldReturnUnprocessableWhenStatusInvalid(): void
    {
        $this->postJson(route('pets.store'), [
            'name'   => 'Reksio',
            'status' => 'invalid_status',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /**
     * @return void
     */
    public function testUpdatePetShouldReturnNoContentResponse(): void
    {
        $mock = $this->mockPetService();
        $mock->expects($this->once())
            ->method('updatePet');

        $this->putJson(route('pets.update', ['pet' => 1]), [
            'name'   => 'Zmieniony',
            'status' => 'sold',
        ])->assertNoContent();
    }

    /**
     * @return void
     */
    public function testUpdatePetShouldReturnUnprocessableWhenDataInvalid(): void
    {
        $this->putJson(route('pets.update', ['pet' => 1]), [
            'name'   => '',
            'status' => 'sold',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * @return void
     */
    public function testDestroyPetShouldReturnNoContentResponse(): void
    {
        $mock = $this->mockPetService();
        $mock->expects($this->once())
            ->method('deletePet')
            ->with(1);

        $this->deleteJson(route('pets.destroy', ['pet' => 1]))
            ->assertNoContent();
    }

    /**
     * @return void
     */
    public function testDestroyPetShouldReturnErrorWhenApiFails(): void
    {
        $mock = $this->mockPetService();
        $mock->method('deletePet')
            ->willThrowException(new PetApiException('Błąd przy usuwaniu.', 500));

        $this->deleteJson(route('pets.destroy', ['pet' => 1]))
            ->assertStatus(500)
            ->assertJson(['message' => 'Błąd przy usuwaniu.']);
    }
}
