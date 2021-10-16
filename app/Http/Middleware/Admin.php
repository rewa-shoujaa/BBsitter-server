<?php

namespace App\Http\Middleware;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use Illuminate\Http\Request;
use Auth;

class Admin extends Middleware
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

    protected function redirectTo($request)
    {
        //, Closure $next
        $user = Auth::user();
        echo($user);
        if (!$request->expectsJson() or $user->user_type != 1) {
            return 'api/login';
        //return $next($request);

        }
    //abort(404);
    //return redirect('home');
    // return ("in middleware");
    }
}
