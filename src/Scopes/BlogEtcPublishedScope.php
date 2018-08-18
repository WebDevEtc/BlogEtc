<?php

namespace WebDevEtc\BlogEtc\Scopes;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BlogEtcPublishedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (\Auth::check() && \Auth::user()->canManageBlogEtcPosts()) {
            // user can manage blog posts, so no need to restrict them to published posts.
        }
        else {
            $builder->where("is_published", true);
            $builder->where("posted_at", "<=", Carbon::now());
        }


    }
}