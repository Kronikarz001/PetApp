<?php

namespace App\Providers;

use App\Providers\AppServiceProvider\PetServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->register(PetServiceProvider::class);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');
    }
}
