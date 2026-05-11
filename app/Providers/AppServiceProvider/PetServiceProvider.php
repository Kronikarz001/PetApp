<?php

namespace App\Providers\AppServiceProvider;

use App\Repositories\PetRepository;
use App\Repositories\PetRepositoryInterface;
use App\Services\HttpService;
use App\Services\PetService;
use App\Services\PetServiceInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of PetServiceProvider
 */
class PetServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(HttpService::class, function () {
            return new HttpService(
                baseUrl: Config::get('petstore.petstore_url'),
            );
        });

        $this->app->bind(PetRepositoryInterface::class, PetRepository::class);

        $this->app->bind(PetServiceInterface::class, PetService::class);
    }
}
