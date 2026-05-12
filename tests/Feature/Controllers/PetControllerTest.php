<?php

namespace Tests\Feature\Controllers;

use App\DTOs\PetDTO;
use App\Exceptions\PetApiException;
use App\Services\PetServiceInterface;
use Illuminate\Http\UploadedFile;
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
     * @param int $id
     * @param string $name
     * @param string $status
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
     * @throws Exception
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
    /**
     * @return void
     */
    public function testUploadFileShouldReturnSuccessResponse(): void
    {
        $mock = $this->mockPetService();
        $mock->method('uploadFile')
            ->willReturn(['code' => 200, 'message' => 'burek.jpg']);

        $this->postJson(route('pets.upload', ['pet' => 1]), [
            'file' => UploadedFile::fake()->image('burek.jpg'),
        ])->assertOk()
            ->assertJsonFragment(['message' => 'burek.jpg']);
    }

    /**
     * @return void
     */
    public function testUploadFileShouldPassAdditionalMetadataToService(): void
    {
        $mock = $this->mockPetService();
        $mock->expects($this->once())
            ->method('uploadFile')
            ->with('1', $this->anything(), 'jakies dane')
            ->willReturn(['code' => 200, 'message' => 'burek.jpg']);

        $this->postJson(route('pets.upload', ['pet' => 1]), [
            'file'               => UploadedFile::fake()->image('burek.jpg'),
            'additionalMetadata' => 'jakies dane',
        ])->assertOk();
    }

    /**
     * @return void
     */
    public function testUploadFileShouldReturnNotFoundWhenPetDoesNotExist(): void
    {
        $mock = $this->mockPetService();
        $mock->method('uploadFile')
            ->willThrowException(new PetApiException('Nie znaleziono zwierzęcia o podanym ID.', 404));

        $this->postJson(route('pets.upload', ['pet' => 999]), [
            'file' => UploadedFile::fake()->image('burek.jpg'),
        ])->assertNotFound()
            ->assertJson(['message' => 'Nie znaleziono zwierzęcia o podanym ID.']);
    }

    /**
     * @return void
     */
    public function testUploadFileShouldReturnUnprocessableWhenFileIsMissing(): void
    {
        $this->postJson(route('pets.upload', ['pet' => 1]), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    /**
     * @return void
     */
    public function testUploadFileShouldReturnUnprocessableWhenFileFormatIsInvalid(): void
    {
        $this->postJson(route('pets.upload', ['pet' => 1]), [
            'file' => UploadedFile::fake()->create('dokument.pdf', 100, 'application/pdf'),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    /**
     * @return void
     */
    public function testUploadFileShouldReturnUnprocessableWhenFileIsTooLarge(): void
    {
        $this->postJson(route('pets.upload', ['pet' => 1]), [
            'file' => UploadedFile::fake()->image('wielki.jpg')->size(10241),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }
}
