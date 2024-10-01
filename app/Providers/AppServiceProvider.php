<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Interfaces\EmployeeIncidentInterface;
use App\Services\EmployeeIncidentService;
use App\Data\CatalogMenu;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EmployeeIncidentInterface::class, EmployeeIncidentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('validate-user-menu', function (User $user, string $routeName) {

            // * get the original route name
            $originalRouteName = false;
            foreach(CatalogMenu::$menus as $key=>$value){
                $_name = explode(".", $value)[0];
                if( $_name == $routeName){
                    $originalRouteName = $key;
                }
            }
            if( $originalRouteName === false){
                return false;
            }

            $user->load("menus");

            // * validate if the user contains the target route name
            $userRoutes = array_map(fn($ele) => $ele['route'], $user->menus->toArray() );
            return in_array($originalRouteName, $userRoutes);
        });
    }
}
