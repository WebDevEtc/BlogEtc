<?php

namespace WebDevEtc\BlogEtc\Middleware;

use Closure;

/**
 * Class UserCanManageBlogPosts
 * @package WebDevEtc\BlogEtc\Middleware
 */
class UserCanManageBlogPosts
{

    /**
     * Show 401 error if \Auth::user()->canManageBlogEtcPosts() == false
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!\Auth::check() ||  !\Auth::user()->canManageBlogEtcPosts()) {
            abort(401,"User not authorised to manage blog posts");
        }
        return $next($request);
    }
}
