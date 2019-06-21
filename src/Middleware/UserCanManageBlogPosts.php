<?php

namespace WebDevEtc\BlogEtc\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Response;

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
        if (!Auth::check()) {
            // user not logged in
            return abort(
                Response::HTTP_FORBIDDEN,
                'User not authorised to manage blog posts: You are not logged in'
            );
        }
        if (!Auth::user()->canManageBlogEtcPosts()) {
            // user lacking correct permission
            return abort(
                Response::HTTP_FORBIDDEN,
                'User not authorised to manage blog posts: Your account is not authorised to edit blog posts'
            );
        }

        // continue!
        return $next($request);
    }
}
