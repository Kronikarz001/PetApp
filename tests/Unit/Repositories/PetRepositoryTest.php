<?php

namespace Tests\Unit\Repositories;

use App\DTOs\PetDTO;
use App\Exceptions\PetApiException;
use App\Repositories\PetRepository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Summary of PetRepositoryTest
 */
class PetRepositoryTest extends TestCase
{
    private PetRepository $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PetRepository('https://petstore.swagger.io/v2');
    }

    /**
     * @return void
     */
    public function testFindByIdShouldReturnPetDTO(): void
    {
        Http::fake([
            '*/pet/1' => Http::response([
                'id' => 1, 'name' => 'Reksio', 'status' => 'available', 'photoUrls' => [],
            ], 200),
        ]);

        $result = $this->repository->findById(1);

        $this->assertInstanceOf(PetDTO::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('Reksio', $result->name);
    }

    /**
     * @return void
     */
    public function testFindByIdShouldThrowExceptionWhenNotFound(): void
    {
        Http::fake([
            '*/pet/999' => Http::response(['message' => 'Pet not found'], 404),
        ]);

        $this->expectException(PetApiException::class);
        $this->expectExceptionMessage('Nie znaleziono zwierzęcia o podanym ID.');

        $this->repository->findById(999);
    }

    /**
     * @return void
     */
    public function testFindByIdShouldThrowExceptionOnConnectionError(): void
    {
        Http::fake(fn() => throw new ConnectionException('Connection refused'));

        $this->expectException(PetApiException::class);
        $this->expectExceptionMessage('Nie można połączyć się z API.');

        $this->repository->findById(1);
    }

    /**
     * @return void
     */
    public function testFindByStatusShouldReturnArrayOfPetDTOs(): void
    {
        Http::fake([
            '*/pet/findByStatus*' => Http::response([
                ['id' => 1, 'name' => 'Kot',  'status' => 'available', 'photoUrls' => []],
                ['id' => 2, 'name' => 'Pies', 'status' => 'available', 'photoUrls' => []],
            ], 200),
        ]);

        $result = $this->repository->findByStatus('available');

        $this->assertCount(2, $result);
        $this->assertInstanceOf(PetDTO::class, $result[0]);
        $this->assertEquals('Kot', $result[0]->name);
    }

    /**
     * @return void
     */
    public function testCreateShouldReturnCreatedPetDTO(): void
    {
        Http::fake([
            '*/pet' => Http::response([
                'id' => 42, 'name' => 'Burek', 'status' => 'available', 'photoUrls' => [],
            ], 200),
        ]);

        $result = $this->repository->create(
            new PetDTO(id: null, name: 'Burek', status: 'available'),
        );

        $this->assertEquals(42, $result->id);
        $this->assertEquals('Burek', $result->name);
    }

    /**
     * @return void
     */
    public function testUpdateShouldNotThrowOnSuccess(): void
    {
        Http::fake([
            '*/pet' => Http::response([
                'id' => 1, 'name' => 'Zmieniony', 'status' => 'sold', 'photoUrls' => [],
            ], 200),
        ]);

        $this->repository->update(
            new PetDTO(id: 1, name: 'Zmieniony', status: 'sold'),
        );

        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testUpdateShouldThrowExceptionOnApiError(): void
    {
        Http::fake([
            '*/pet' => Http::response(['message' => 'Invalid input'], 400),
        ]);

        $this->expectException(PetApiException::class);
        $this->expectExceptionMessage('Niepoprawne dane żądania.');

        $this->repository->update(
            new PetDTO(id: 1, name: 'Zmieniony', status: 'sold'),
        );
    }

    /**
     * @return void
     */
    public function testDeleteShouldNotThrowOnSuccess(): void
    {
        Http::fake([
            '*/pet/1' => Http::response([], 200),
        ]);

        $this->repository->delete(1);

        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testDeleteShouldNotThrowWhenPetAlreadyNotFound(): void
    {
        Http::fake([
            '*/pet/999' => Http::response([], 404),
        ]);

        $this->repository->delete(999);

        $this->assertTrue(true);
    }
}
