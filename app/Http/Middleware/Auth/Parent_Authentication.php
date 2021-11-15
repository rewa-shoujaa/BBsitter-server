<?php

namespace App\Http\Middleware\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use Illuminate\Http\Request;
use Auth;

class Parent_Authentication

{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     * 
     * 
     */

    public function handle($request, Closure $next)
    {
        //, Closure $next
        $user = auth()->user();

        if ($user->user_type != 2) {
            $response['access'] = "denied";
            return response()->json([$response], 403);

        }
        return $next($request);
    // return $user;

    }
}
