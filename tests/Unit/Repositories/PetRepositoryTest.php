<?php

namespace Tests\Unit\Repositories;

use App\DTOs\PetDTO;
use App\Exceptions\PetApiException;
use App\Repositories\PetRepository;
use App\Services\HttpService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\MockObject\Exception;
use Tests\TestCase;

/**
 * Summary of PetRepositoryTest
 */
class PetRepositoryTest extends TestCase
{
    private PetRepository $repository;
    private HttpService $httpService;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpService  = $this->createMock(HttpService::class);
        $this->repository   = new PetRepository($this->httpService);
    }

    /**
     * @param array $data
     * @param int $status
     * @return Response
     * @throws Exception
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
     * @throws ConnectionException
     * @throws PetApiException
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
     * @throws ConnectionException
     * @throws PetApiException
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
     * @throws ConnectionException
     * @throws PetApiException
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
     * @throws ConnectionException
     * @throws PetApiException
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
     * @throws ConnectionException
     * @throws PetApiException
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
     * @throws ConnectionException
     * @throws PetApiException
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
     * @throws ConnectionException
     * @throws PetApiException
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
     * @throws ConnectionException
     * @throws PetApiException
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
     * @throws ConnectionException
     * @throws PetApiException
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

    /**
     * @return void
     * @throws ConnectionException
     * @throws Exception
     * @throws PetApiException
     */
    public function testUploadFileShouldCallHttpServiceWithCorrectEndpoint(): void
    {
        $file     = UploadedFile::fake()->image('burek.jpg');
        $expected = ['code' => 200, 'message' => 'burek.jpg'];

        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn($expected);

        $this->httpService
            ->expects($this->once())
            ->method('uploadFile')
            ->with('/pet/42/uploadFile', $file, null)
            ->willReturn($response);

        $result = $this->repository->uploadFile('42', $file, null);

        $this->assertSame($expected, $result);
    }

    /**
     * @return void
     * @throws ConnectionException
     * @throws Exception
     * @throws PetApiException
     */
    public function testUploadFileShouldPassAdditionalMetadataToHttpService(): void
    {
        $file     = UploadedFile::fake()->image('burek.jpg');
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn([]);

        $this->httpService
            ->expects($this->once())
            ->method('uploadFile')
            ->with('/pet/42/uploadFile', $file, 'jakies dane')
            ->willReturn($response);

        $this->repository->uploadFile('42', $file, 'jakies dane');

        $this->assertTrue(true);
    }

    /**
     * @return void
     * @throws ConnectionException
     * @throws PetApiException
     * @throws Exception
     */
    public function testUploadFileShouldReturnArrayFromJsonResponse(): void
    {
        $file     = UploadedFile::fake()->image('burek.jpg');
        $expected = ['code' => 200, 'type' => 'unknown', 'message' => 'burek.jpg'];

        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn($expected);

        $this->httpService
            ->method('uploadFile')
            ->willReturn($response);

        $result = $this->repository->uploadFile('42', $file, null);

        $this->assertIsArray($result);
        $this->assertSame($expected, $result);
    }

    /**
     * @return void
     * @throws ConnectionException
     * @throws PetApiException
     */
    public function testUploadFileShouldPropagateExceptionFromHttpService(): void
    {
        $file = UploadedFile::fake()->image('burek.jpg');

        $this->httpService
            ->method('uploadFile')
            ->willThrowException(new PetApiException('Nie znaleziono zwierzęcia o podanym ID.', 404));

        $this->expectException(PetApiException::class);
        $this->expectExceptionMessage('Nie znaleziono zwierzęcia o podanym ID.');

        $this->repository->uploadFile('999', $file, null);
    }
}
