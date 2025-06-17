<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Socialite\Facades\Socialite;
use App\Models\
{
    User,
    Route
};

class KeycloakAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('keycloak')->redirect();
    }

    public function callback(Request $request)
    {
        // * retrive keycloack-user info
        try
        {
            $kuser = Socialite::driver('keycloak')->user();
        }
        catch(\Laravel\Socialite\Two\InvalidStateException)
        {
            return redirect('/');
        }
        catch(\GuzzleHttp\Exception\ClientException)
        {
            return redirect('/');
        }

        dd($kuser);

        // * create a local user model
        $user = User::updateOrCreate([
            'email' => $kuser->email,
        ], [
            'name' => $kuser->name,
            'is-admin' => 0,
            'updated_password' => 0,
            'keycloak_access_token' => $kuser->token,
            'keycloak_refresh_token' => $kuser->refreshToken
        ]);


        // * validate if the user has the right access roles and
        // *   attempt to retrive the app roles.
        $app_roles = array();
        if(!$this->validateTokenRoles($user, $app_roles))
        {
            // * if not has the right roles, remove the rokens and abort
            $user->update([
                'keycloak_access_token' => null,
                'keycloak_refresh_token' => null
            ]);

            abort(Response::HTTP_FORBIDDEN, "No tienes los permisos necesarios para acceder a esta aplicacion.");
        }

        // * Update the user-route permission based on the app role, like a profile
        // TODO: Move this logic to another function or class
        if(in_array("default-user", $app_roles))
        {
            $_routes_names = [
                'annoucement.index',
                'aspirants.index',
                'candidates.index',
                'internal_career.index',
                'courses.index',
                'employees.index',
                'advancedsearch.index',
                'persons.index',
                'institutes.index',
                'groups.index',
                'initialFormation.index',
                'performance_eval.index',
                'confidence_test.index',
                'academic_performance_eval.index',
                'employeesTermination.index'
            ];
            $user->routes()->sync(Route::whereIn('name', $_routes_names)->get());
        }

        // * if has the right roles redirect to dashboard
        Auth::login($user);
        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        // Logout of your app.
        Auth::logout();

        // The user will not be redirected back.
        return redirect(Socialite::driver('keycloak')->getLogoutUrl());
    }


    /**
     * Validate the token and ensure has the right roles
     *
     * @param  mixed $user
     * @return bool
     */
    private function validateTokenRoles(User $user, array &$roles)
    {
        // * check the roles from the token
        $userDecoded = \App\Helpers\Token::decode($user->keycloak_access_token, env('KEYCLOAK_REALM_PUBLIC_KEY'), 60);

        // * retrive the roles for this app
        $roles = $userDecoded->resource_access?->dgfspc?->roles ?? [];

        $__roles_availables = config('app.KEYCLOAK_ACCESS_ROLES');
        $diff = array_intersect($__roles_availables, $userDecoded->realm_access->roles);
        return count($diff) > 0;
    }

}