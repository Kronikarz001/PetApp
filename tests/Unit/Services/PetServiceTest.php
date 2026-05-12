<?php

namespace Tests\Unit\Services;

use App\DTOs\PetDTO;
use App\Exceptions\PetApiException;
use App\Repositories\PetRepositoryInterface;
use App\Services\PetService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Summary of PetServiceTest
 */
class PetServiceTest extends TestCase
{
    private PetService $service;

    /** @var PetRepositoryInterface&MockObject */
    private PetRepositoryInterface $repository;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(PetRepositoryInterface::class);
        $this->service    = new PetService($this->repository);
    }

    /**
     * @return void
     */
    public function testGetPetsShouldReturnLengthAwarePaginator(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('findByStatus')
            ->with('available')
            ->willReturn([
                new PetDTO(id: 1, name: 'Kot',  status: 'available'),
                new PetDTO(id: 2, name: 'Pies', status: 'available'),
            ]);

        $result = $this->service->getPets('available');

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(2, $result->total());
    }

    /**
     * @return void
     */
    public function testGetPetsShouldReturnCorrectPerPage(): void
    {
        $this->repository
            ->method('findByStatus')
            ->willReturn([
                new PetDTO(id: 1, name: 'Kot',  status: 'available'),
                new PetDTO(id: 2, name: 'Pies', status: 'available'),
                new PetDTO(id: 3, name: 'Ryba', status: 'available'),
            ]);

        $result = $this->service->getPets('available', 2);

        $this->assertEquals(2, $result->perPage());
        $this->assertEquals(3, $result->total());
        $this->assertEquals(2, $result->count());
    }

    /**
     * @return void
     */
    public function testGetPetsShouldPropagateException(): void
    {
        $this->repository
            ->method('findByStatus')
            ->willThrowException(new PetApiException('Nie można połączyć się z API.'));

        $this->expectException(PetApiException::class);
        $this->expectExceptionMessage('Nie można połączyć się z API.');

        $this->service->getPets('available');
    }

    /**
     * @return void
     */
    public function testGetPetShouldReturnPetDTO(): void
    {
        $expected = new PetDTO(id: 1, name: 'Reksio', status: 'available');

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with('1')
            ->willReturn($expected);

        $result = $this->service->getPet('1');

        $this->assertInstanceOf(PetDTO::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('Reksio', $result->name);
    }

    /**
     * @return void
     */
    public function testGetPetShouldPropagateNotFoundException(): void
    {
        $this->repository
            ->method('findById')
            ->willThrowException(new PetApiException('Nie znaleziono zwierzęcia o podanym ID.', 404));

        $this->expectException(PetApiException::class);
        $this->expectExceptionMessage('Nie znaleziono zwierzęcia o podanym ID.');

        $this->service->getPet('999');
    }

    /**
     * @return void
     */
    public function testCreatePetShouldReturnCreatedPetDTO(): void
    {
        $expected = new PetDTO(id: 42, name: 'Burek', status: 'available');

        $this->repository
            ->expects($this->once())
            ->method('create')
            ->willReturn($expected);

        $result = $this->service->createPet([
            'name'   => 'Burek',
            'status' => 'available',
        ]);

        $this->assertInstanceOf(PetDTO::class, $result);
        $this->assertEquals(42, $result->id);
        $this->assertEquals('Burek', $result->name);
    }

    /**
     * @return void
     */
    public function testCreatePetShouldPassCorrectDTOToRepository(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (PetDTO $dto) {
                return $dto->name === 'Burek'
                    && $dto->status === 'available'
                    && $dto->id === null;
            }))
            ->willReturn(new PetDTO(id: 1, name: 'Burek', status: 'available'));

        $this->service->createPet([
            'name'   => 'Burek',
            'status' => 'available',
        ]);
    }

    /**
     * @return void
     */
    public function testUpdatePetShouldCallRepositoryUpdate(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('update')
            ->with($this->callback(function (PetDTO $dto) {
                return $dto->id === 1
                    && $dto->name === 'Zmieniony'
                    && $dto->status === 'sold';
            }));

        $this->service->updatePet('1', [
            'name'   => 'Zmieniony',
            'status' => 'sold',
        ]);
    }

    /**
     * @return void
     */
    public function testUpdatePetShouldPropagateException(): void
    {
        $this->repository
            ->method('update')
            ->willThrowException(new PetApiException('Nie można połączyć się z API.'));

        $this->expectException(PetApiException::class);

        $this->service->updatePet('1', [
            'name'   => 'Zmieniony',
            'status' => 'sold',
        ]);
    }

    /**
     * @return void
     */
    public function testDeletePetShouldCallRepositoryDelete(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with('1');

        $this->service->deletePet('1');
    }

    /**
     * @return void
     */
    public function testDeletePetShouldPropagateException(): void
    {
        $this->repository
            ->method('delete')
            ->willThrowException(new PetApiException('Nie można połączyć się z API.'));

        $this->expectException(PetApiException::class);

        $this->service->deletePet('1');
    }

    /**
     * @return void
     */
    public function testUploadFileShouldDelegateToRepositoryAndReturnResponse(): void
    {
        $file     = UploadedFile::fake()->image('burek.jpg');
        $expected = ['code' => 200, 'message' => 'burek.jpg'];

        $this->repository
            ->expects($this->once())
            ->method('uploadFile')
            ->with('42', $file, null)
            ->willReturn($expected);

        $result = $this->service->uploadFile('42', $file, null);

        $this->assertSame($expected, $result);
    }

    /**
     * @return void
     */
    public function testUploadFileShouldPassAdditionalMetadataToRepository(): void
    {
        $file = UploadedFile::fake()->image('burek.jpg');

        $this->repository
            ->expects($this->once())
            ->method('uploadFile')
            ->with('42', $file, 'jakies dane')
            ->willReturn(['code' => 200, 'message' => 'burek.jpg']);

        $this->service->uploadFile('42', $file, 'jakies dane');

        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testUploadFileShouldPropagateExceptionFromRepository(): void
    {
        $file = UploadedFile::fake()->image('burek.jpg');

        $this->repository
            ->method('uploadFile')
            ->willThrowException(new PetApiException('Nie znaleziono zwierzęcia o podanym ID.', 404));

        $this->expectException(PetApiException::class);
        $this->expectExceptionMessage('Nie znaleziono zwierzęcia o podanym ID.');

        $this->service->uploadFile('999', $file, null);
    }
}
