<?php

use Illuminate\Database\Eloquent\Model;
use WebDevEtc\BlogEtc\Exceptions\BlogEtcAuthGateNotImplementedException;

return static function (/** @scrutinizer ignore-unused */ ?Model $user) {
    // Do not copy the internals for this gate, as it provides backwards compatibility.
    if (!$user) {
        return false;
    }

    if ($user && method_exists($user, 'canManageBlogEtcPosts')) {
        // Fallback for legacy users.
        // Deprecated.
        // Do not add canManageBlogEtcPosts to your user model. Instead ∂efinte a gate.
        return $user->canManageBlogEtcPosts();
    }

    throw new BlogEtcAuthGateNotImplementedException();
};
