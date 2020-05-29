<?php

use Illuminate\Database\Eloquent\Model;

return function (?Model $user) {
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

    throw new LogicException('You must implement your own gate in AuthServiceProvider for the \WebDevEtc\BlogEtc\Gates\GateTypes::MANAGE_ADMIN gate.');
    // Add something like the following to AuthServiceProvider:

//                Gate::define(GateTypes::MANAGE_ADMIN, static function (?Model $user) {
//                    Implement your logic to allow or disallow admin access for $user
//                    return $model->is_admin === true;
//                    or:
//                    return $model->email === 'your-email@your-site.com';
//                });
};
