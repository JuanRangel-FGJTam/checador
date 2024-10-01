<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use App\Data\CatalogMenu;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {

        // * load the menus of the user
        if(!is_null($request->user())){
            $request->user()->load("menus");
        }

        // * create a list of menus based on the menus assigned to the user, replacing the original route name with the local route name
        $menus = array();
        if( isset($request->user()->menus)){
            $menus = array_map( function($element) {
                return [
                    "id" => $element->id,
                    "name" => $element->name,
                    "route" => CatalogMenu::$menus[ $element->route ] /* replacing the route names*/,
                ];
            }, $request->user()->menus->all() );
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'menus' => $menus
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
