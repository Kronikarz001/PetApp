<?php

namespace Tests\Unit\Repositories;

use App\DTOs\PetDTO;
use App\Exceptions\PetApiException;
use App\Repositories\PetRepository;
use App\Services\HttpService;
use Illuminate\Http\Client\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Summary of PetRepositoryTest
 */
class PetRepositoryTest extends TestCase
{
    private PetRepository $repository;

    /** @var HttpService&MockObject */
    private HttpService $httpService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpService  = $this->createMock(HttpService::class);
        $this->repository   = new PetRepository($this->httpService);
    }

    /**
     * @return Response&MockObject
     */
    private function mockResponse(array $data, int $status = 200): Response
    {
        $mock = $this->createMock(Response::class);
        $mock->method('json')->willReturn($data);
        $mock->method('status')->willReturn($status);
        $mock->method('successful')->willReturn($status >= 200 && $status < 300);

        return $mock;
    }

    /**
     * @return void
     */
    public function testFindByIdShouldReturnPetDTO(): void
    {
        $this->httpService
            ->expects($this->once())
            ->method('request')
            ->with('GET', '/pet/1')
            ->willReturn($this->mockResponse([
                'id' => 1, 'name' => 'Reksio', 'status' => 'available', 'photoUrls' => [],
            ]));

        $result = $this->repository->findById('1');

        $this->assertInstanceOf(PetDTO::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('Reksio', $result->name);
    }

    /**
     * @return void
     */
    public function testFindByIdShouldThrowExceptionWhenNotFound(): void
    {
        $this->httpService
            ->method('request')
            ->willThrowException(new PetApiException('Nie znaleziono zwierzęcia o podanym ID.', 404));

        $this->expectException(PetApiException::class);
        $this->expectExceptionMessage('Nie znaleziono zwierzęcia o podanym ID.');

        $this->repository->findById('999');
    }

    /**
     * @return void
     */
    public function testFindByIdShouldThrowExceptionOnConnectionError(): void
    {
        $this->httpService
            ->method('request')
            ->willThrowException(new PetApiException('Nie można połączyć się z API.'));

        $this->expectException(PetApiException::class);
        $this->expectExceptionMessage('Nie można połączyć się z API.');

        $this->repository->findById('1');
    }

    /**
     * @return void
     */
    public function testFindByStatusShouldReturnArrayOfPetDTOs(): void
    {
        $this->httpService
            ->expects($this->once())
            ->method('request')
            ->with('GET', '/pet/findByStatus', ['status' => 'available'])
            ->willReturn($this->mockResponse([
                ['id' => 1, 'name' => 'Kot',  'status' => 'available', 'photoUrls' => []],
                ['id' => 2, 'name' => 'Pies', 'status' => 'available', 'photoUrls' => []],
            ]));

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
        $this->httpService
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/pet')
            ->willReturn($this->mockResponse([
                'id' => 42, 'name' => 'Burek', 'status' => 'available', 'photoUrls' => [],
            ]));

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
        $this->httpService
            ->expects($this->once())
            ->method('request')
            ->with('PUT', '/pet')
            ->willReturn($this->mockResponse([
                'id' => 1, 'name' => 'Zmieniony', 'status' => 'sold', 'photoUrls' => [],
            ]));

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
        $this->httpService
            ->method('request')
            ->willThrowException(new PetApiException('Niepoprawne dane żądania.', 400));

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
        $this->httpService
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', '/pet/1')
            ->willReturn($this->mockResponse([]));

        $this->repository->delete('1');

        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testDeleteShouldNotThrowWhenPetAlreadyNotFound(): void
    {
        $this->httpService
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', '/pet/999')
            ->willReturn($this->mockResponse([]));

        $this->repository->delete('999');

        $this->assertTrue(true);
    }
}
