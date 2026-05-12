<?php

namespace App\Http\Controllers;

use App\Http\Requests\PetCreateRequest;
use App\Http\Requests\PetUpdateRequest;
use App\Http\Requests\PetUploadFileRequest;
use App\Http\Resources\PetResource;
use App\Services\PetServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Summary of PetController
 */
final class PetController extends Controller
{
    /**
     * @param PetServiceInterface $petService
     */
    public function __construct(
        private readonly PetServiceInterface $petService,
    ) {}

    /**
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function index(Request $request): LengthAwarePaginator
    {
        return $this->petService->getPets(
            $request->input('status', 'available'),
            $request->integer('perPage', 15),
        );
    }

    /**
     * @param string $pet
     * @return PetResource
     */
    public function show(string $pet): PetResource
    {
        return new PetResource(
            $this->petService->getPet($pet)
        );
    }

    /**
     * @param PetCreateRequest $request
     * @return JsonResponse
     */
    public function store(PetCreateRequest $request): JsonResponse
    {
        return new PetResource(
            $this->petService->createPet($request->validated())
        )->response()->setStatusCode(201);
    }

    /**
     * @param PetUpdateRequest $request
     * @param string $pet
     * @return JsonResponse
     */
    public function update(PetUpdateRequest $request, string $pet): JsonResponse
    {
        $this->petService->updatePet($pet, $request->validated());

        return response()->json([], 204);
    }

    /**
     * @param string $pet
     * @return JsonResponse
     */
    public function destroy(string $pet): JsonResponse
    {
        $this->petService->deletePet($pet);

        return response()->json([], 204);
    }

    /**
     * @param PetUploadFileRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function uploadFile(PetUploadFileRequest $request, string $id): JsonResponse
    {
        return response()->json(
            $this->petService->uploadFile(
                $id,
                $request->file('file'),
                $request->input('additionalMetadata')
            )
        );
    }
}
