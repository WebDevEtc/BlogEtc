<?php

namespace WebDevEtc\BlogEtc\Middleware;

use Closure;

/**
 * Class UserCanManageBlogPosts.
 */
class UserCanManageBlogPosts
{
    /**
     * Show 401 error if \Auth::user()->canManageBlogEtcPosts() == false.
     *
     * @param $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!\Auth::check()) {
            abort(401, 'User not authorised to manage blog posts: You are not logged in');
        }
        if (!\Auth::user()->canManageBlogEtcPosts()) {
            abort(401, 'User not authorised to manage blog posts: Your account is not authorised to edit blog posts');
        }

        return $next($request);
    }
}
