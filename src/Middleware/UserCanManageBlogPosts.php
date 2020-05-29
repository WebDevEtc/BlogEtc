<?php

namespace WebDevEtc\BlogEtc\Middleware;

use Auth;
use Closure;
use WebDevEtc\BlogEtc\Gates\GateTypes;
use WebDevEtc\BlogEtc\Helpers;

/**
 * Class UserCanManageBlogPosts.
 */
class UserCanManageBlogPosts
{
    public function handle($request, Closure $next)
    {
        if(!Helpers::hasAccess(GateTypes::MANAGE_ADMIN)) {
              abort(401, 'User not authorised to manage blog posts: Your account is not authorised to edit blog posts');
        }

        return $next($request);
    }
}
