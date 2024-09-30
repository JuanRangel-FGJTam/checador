<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class AuthorizedMenu
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = explode("/", $request->path())[0];
        if ($path == 'dashboard' || $path == 'logout' || $path == 'login') {
            return $next($request);
        }
        if( Gate::allows('validate-user-menu', $path)) {
            return $next($request);
        }
        
        return redirect('/dashboard');
    }
}
