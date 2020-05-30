<?php

namespace WebDevEtc\BlogEtc\Middleware;

use Closure;
use WebDevEtc\BlogEtc\Helpers;

class UserCanManageBlogPosts
{
    public function handle($request, Closure $next)
    {
        abort_if(
            !Helpers::hasAdminGateAccess(),
            401,
            'User not authorised to manage blog posts: Your account is not authorised to edit blog posts'
        );

        return $next($request);
    }
}
