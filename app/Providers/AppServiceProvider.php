<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Routing\UrlGenerator;
use App\Mail\CustomTransport;
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
    public function boot(UrlGenerator $url): void
    {
        // * Check if the request is secure and force the scheme to https
        $isSecure = false;

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $isSecure = true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && 
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || 
            !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && 
            $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'
        ) {
            $isSecure = true;
        }

        if ($isSecure) {
            $url->forceScheme('https');
        }

        Gate::define('validate-user-menu', function (User $user, string $routeName) {
            // * Convert to camel case if '-' or '_' is found
            if (preg_match('/[-_]/', $routeName)) {
                $routeName = lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $routeName))));
            }

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

        // Register the custom mail transport
        Mail::extend('dgtitAPI', function () {
            return new CustomTransport();
        });
    }
}
