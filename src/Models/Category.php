<?php

namespace WebDevEtc\BlogEtc\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    public $fillable = [
        'category_name',
        'slug',
        'category_description',
    ];

    protected $table = 'blog_etc_categories';

    /**
     * @return BelongsToMany
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'blog_etc_post_categories', 'blog_etc_category_id','blog_etc_post_id');
    }

    /**
     * Returns the public facing URL of showing blog posts in this category.
     *
     * @return string
     */
    public function url(): string
    {
        return route('blogetc.view_category', $this->slug);
    }

    /**
     * Returns the URL for an admin user to edit this category.
     *
     * @return string
     */
    public function edit_url(): string
    {
        return route('blogetc.admin.categories.edit_category', $this->id);
    }
}
