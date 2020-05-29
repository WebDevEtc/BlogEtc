<?php

namespace WebDevEtc\BlogEtc\Middleware;

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
        if (!Helpers::hasAdminGateAccess()) {
            abort(401, 'User not authorised to manage blog posts: Your account is not authorised to edit blog posts');
        }

        return $next($request);
    }
}
