<?php

namespace WebDevEtc\BlogEtc\Models;

use App\User;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use WebDevEtc\BlogEtc\Scopes\BlogEtcPublishedScope;

/**
 * Class BlogEtcPost
 * @package WebDevEtc\BlogEtc\Models
 */
class BlogEtcPost extends Model
{

    use Sluggable;

    /**
     * @var array
     */
    public $casts = [
        'is_published' => 'boolean',
    ];

    /**
     * @var array
     */
    public $dates = [
        'posted_at'
    ];

    /**
     * @var array
     */
    public $fillable = [

        'title',
        'subtitle',
        'post_body',
        'meta_desc',
        'slug',
        'use_view_file',

        'is_published',
        'posted_at',
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }


    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // If user is logged in and \Auth::user()->canManageBlogEtcPosts() == true, show any/all posts.
        // otherwise (which will be for most users) it should only show published posts that have a posted_at
        // time <= Carbon::now(). This sets it up:
        static::addGlobalScope(new BlogEtcPublishedScope());
    }

    /**
     * The associated author (if user_id) is set
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Return author string (either from the User (via ->user_id), or the submitted author_name value
     * @return string
     */
    public function author_string()
    {
        if ($this->author) {
            return optional($this->author)->name;
        } else {
            return 'Unknown Author';
        }

    }


    /**
     * The associated categories for this blog post
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(BlogEtcCategory::class, 'blog_etc_post_categories');
    }

    /**
     * Comments for this post
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(BlogEtcComment::class);
    }

    /**
     * Returns the public facing URL to view this blog post
     *
     * @return string
     */
    public function url()
    {
        return route("blogetc.single", $this->slug);
    }

    /**
     * Return the URL for editing the post (used for admin users)
     * @return string
     */
    public function edit_url()
    {
        return route("blogetc.admin.edit_post", $this->id);
    }

    /**
     * If $this->user_view_file is not empty, then it'll return the dot syntax location of the blade file it should look for
     * @return string
     * @throws \Exception
     */
    public function full_view_file_path()
    {
        if (!$this->use_view_file) {
            throw new \RuntimeException("use_view_file was empty, so cannot use " . __METHOD__);
        }
        return "custom_blog_posts." . $this->use_view_file;
    }


    /**
     * Does this object have an uploaded image of that size...?
     *
     * @param string $size
     * @return int
     */
    public function has_image($size = 'medium')
    {
        $this->check_valid_image_size($size);
        return strlen($this->{"image_" . $size});
    }

    /**
     * Get the full URL for an image
     * You should use ::has_image($size) to check if the size is valid
     *
     * @param string $size - should be 'medium' , 'large' or 'thumbnail'
     * @return string
     */
    public function image_url($size = 'medium')
    {
        $this->check_valid_image_size($size);
        $filename = $this->{"image_" . $size};
        return asset("blog_images/" . $filename);
    }

    /**
     * Generate a full <img src='' alt=''> img tag
     *
     * @param string $size - large, medium, thumbnail
     * @param boolean $auto_link - if true then itll add <a href=''>...</a> around the <img> tag
     * @param null|string $classes - if you want any additional CSS classes for this tag
     * @return string
     */
    public function image_tag($size = 'medium', $auto_link = true, $classes = null)
    {

        if ($this->has_image($size)) {
            $url = e($this->image_url($size));
            $alt = e($this->title);
            $img = "<img src='$url' alt='$alt' class='" . e($classes) . "' >";
            return $auto_link ? "<a href='" . e($this->url()) . "'>$img</a>" : $img;
        }

        return '';
    }

    public function post_body_output()
    {


        if (config("blogetc.use_custom_view_files") && $this->use_view_file) {
            // using custom view files is enabled, and this post has a use_view_file set, so render it:
            $return = view("blogetc::partials.use_view_file")->render();
        } else {
            // just use the plain ->post_body
            $return = $this->post_body;
        }


        if (!config("blogetc.echo_html")) {
            // if this is not true, then we should escape the output

            if (config("blogetc.strip_html")) {
                $return=strip_tags($return);
            }

            $return = e($return);

            if (config("blogetc.auto_nl2br")) {
                $return = nl2br($return);
            }

        }

        return $return;

    }


    /**
     * Throws an exception if $size is not valid
     * It should be either 'large','medium','thumbnail'
     * @param string $size
     * @return bool
     * @throws \InvalidArgumentException
     */
    protected function check_valid_image_size(string $size = 'medium')
    {

        if (!in_array($size, [
            'large', 'medium', 'thumbnail'
        ])
        ) {
            throw new \InvalidArgumentException("BlogEtcPost image size should be 'large','medium','thumbnail'. Provided size ($size) is not valid");
        }

        return true;
    }
}
