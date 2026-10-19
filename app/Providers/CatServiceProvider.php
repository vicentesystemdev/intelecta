<?php

namespace App\Providers;

use App\Domains\Cat\Contracts\MotorCat;
use App\Domains\Cat\Services\MotorCatHttp;
use Illuminate\Support\ServiceProvider;

class CatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MotorCat::class, MotorCatHttp::class);
    }
}
