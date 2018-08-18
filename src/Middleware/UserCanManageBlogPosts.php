<?php

namespace WebDevEtc\BlogEtc\Middleware;

use Closure;

class UserCanManageBlogPosts
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Auth::check() && \Auth::user()->canManageBlogEtcPosts()) {
            return $next($request);
        }
//        dump(\Auth::check());
//        dd("EEE");
        abort(401,"User not authorised to manage blog posts");
    }
}
