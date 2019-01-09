<?php

namespace App\Providers;

use App\Repositories\CustomUserRepository as UserRepo;
use Illuminate\Support\ServiceProvider;
use \Auth0\Login\Contract\Auth0UserRepository as Auth0Contract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->bind(
            Auth0Contract::class,
            UserRepo::class);

    }
}
