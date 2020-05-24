<?php

namespace WebDevEtc\BlogEtc\Models;

use App\User;
use Cviebrock\EloquentSluggable\Sluggable;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Swis\Laravel\Fulltext\Indexable;
use WebDevEtc\BlogEtc\Interfaces\SearchResultInterface;
use WebDevEtc\BlogEtc\Scopes\BlogEtcPublishedScope;

/**
 * Class BlogEtcPost.
 */
class Post extends Model implements SearchResultInterface
{
    use Sluggable;
    use Indexable;

    protected $indexContentColumns = ['post_body', 'short_description', 'meta_desc'];
    protected $indexTitleColumns = ['title', 'subtitle', 'seo_title'];

    protected $table = 'blog_etc_posts';

    /**
     * @var array
     */
    public $casts = [
        'posted_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    /**
     * @var array
     */
    public $dates = [
        'posted_at',
    ];

    /**
     * @var array
     */
    public $fillable = [
        'title',
        'subtitle',
        'short_description',
        'post_body',
        'seo_title',
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
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }

    public function search_result_page_url()
    {
        return $this->url();
    }

    public function search_result_page_title()
    {
        return $this->title;
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        /* If user is logged in and \Auth::user()->canManageBlogEtcPosts() == true, show any/all posts.
           otherwise (which will be for most users) it should only show published posts that have a posted_at
           time <= Carbon::now(). This sets it up: */
        static::addGlobalScope(new BlogEtcPublishedScope());
    }

    /**
     * The associated author (if user_id) is set.
     *
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Return author string (either from the User (via ->user_id), or the submitted author_name value.
     *
     * @return string
     */
    public function authorString(): ?string
    {
        // TODO
//        if ($this->author) {
//            return is_callable(self::$authorNameResolver)
//                ? call_user_func(self::$authorNameResolver, $this->author)
//                : $this->author->{self::$authorNameResolver};
//        }
        if ($this->author) {
            return (string)optional($this->author)->name;
        }

        return 'Unknown Author';
    }

    /**
     * The associated categories for this blog post.
     *
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'blog_etc_post_categories', 'blog_etc_category_id',
            'blog_etc_post_id');
    }

    /**
     * Comments for this post.
     *
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'blog_etc_post_id');
    }

    /**
     * Returns the public facing URL to view this blog post.
     *
     * @return string
     */
    public function url(): string
    {
        return route('blogetc.single', $this->slug);
    }

    /**
     * Return the URL for editing the post (used for admin users).
     *
     * @return string
     */
    public function editUrl(): string
    {
        return route('blogetc.admin.edit_post', $this->id);
    }

    /**
     * If $this->user_view_file is not empty, then it'll return the dot syntax
     * location of the blade file it should look for.
     *
     * @throws Exception
     */
    public function bladeViewFile(): string
    {
        if (!$this->use_view_file) {
            throw new RuntimeException('use_view_file was empty, so cannot use fullViewFilePath()');
        }

        return 'custom_blog_posts.' . $this->use_view_file;
    }

    /**
     * Returns true if the database record indicates that this blog post
     * has a featured image of size $size.
     *
     * @param string $size
     */
    public function hasImage($size = 'medium'): bool
    {
        $this->check_valid_image_size($size);

        return array_key_exists('image_' . $size, $this->getAttributes()) && $this->{'image_' . $size};
    }

    /**
     * Get the full URL for an image
     * You should use ::has_image($size) to check if the size is valid.
     *
     * @param string $size - should be 'medium' , 'large' or 'thumbnail'
     */
    public function imageUrl($size = 'medium'): string
    {
        $this->checkValidImageSize($size);
        $filename = $this->{'image_' . $size};

        return asset(config('blogetc.blog_upload_dir', 'blog_images') . '/' . $filename);
//        return UploadsService::publicUrl($filename);
    }

    /**
     * Generate a full <img src='' alt=''> img tag.
     *
     * TODO - return HtmlString
     * @param string $size - large, medium, thumbnail
     * @param bool $addAHref - if true then it will add <a href=''>...</a> around the <img> tag
     * @param string|null $imgTagClass - if you want any additional CSS classes for this tag for the <IMG>
     * @param string|null $anchorTagClass - is you want any additional CSS classes in the <a> anchor tag
     */
    public function imageTag(
        $size = 'medium',
        $addAHref = true,
        $imgTagClass = null,
        $anchorTagClass = null
    ) {
        if (!$this->hasImage($size)) {
            // return an empty string if this image does not exist.
            return '';
        }
        $imageUrl = e($this->imageUrl($size));
        $imageAltText = e($this->title);
        $imgTag = '<img src="' . $imageUrl . '" alt="' . $imageAltText . '" class="' . e($imgTagClass) . '">';

        return $addAHref
            ? '<a class="' . e($anchorTagClass) . '" href="' . e($this->url()) . '">' . $imgTag . '</a>'
            : $imgTag;
    }

    /**
     * Generate an introduction, max length $max_len characters.
     */
    public function generateIntroduction(int $maxLen = 500): string
    {
        $base_text_to_use = $this->short_description;

        if (!trim($base_text_to_use)) {
            $base_text_to_use = $this->post_body;
        }
        $base_text_to_use = strip_tags($base_text_to_use);

        return Str::limit($base_text_to_use, $maxLen);
    }

    /**
     * Return post body HTML, ready for output.
     *
     * TODO: return HtmlString
     * @throws Throwable
     */
    public function renderBody()
    {
        if (config('blogetc.use_custom_view_files') && $this->use_view_file) {
            // using custom view files is enabled, and this post has a use_view_file set, so render it:
            $return = view('blogetc::partials.use_view_file', ['post' => $this])->render();
        } else {
            // just use the plain ->post_body
            $return = $this->post_body;
        }

        if (!config('blogetc.echo_html')) {
            // if this is not true, then we should escape the output
            if (config('blogetc.strip_html')) {
                $return = strip_tags($return);
            }

            $return = e($return);
            if (config('blogetc.auto_nl2br')) {
                $return = nl2br($return);
            }
        }

        return $return;
        // New logic todo:
//        $body = $this->use_view_file && config('blogetc.use_custom_view_files')
//            ? view('blogetc::partials.use_view_file', ['post' => $this])->render()
//            : $this->post_body;
//
//        if (!config('blogetc.echo_html')) {
//            // if this is not true, then we should escape the output
//            if (config('blogetc.strip_html')) {
//                // not perfect, but it will get wrapped in htmlspecialchars in e() anyway
//                $body = strip_tags($body);
//            }
//
//            $body = e($body);
//
//            if (config('blogetc.auto_nl2br')) {
//                $body = nl2br($body);
//            }
//        }
//
//        return new HtmlString($body);
    }

    /**
     * Throws an exception if $size is not valid
     * It should be either 'large','medium','thumbnail'.
     *
     * @throws InvalidArgumentException
     */
    protected function checkValidImageSize(string $size = 'medium'): bool
    {
        if (array_key_exists('image_' . $size, config('blogetc.image_sizes', []))) {
            // correct size string - just return
            return true;
        }

        // if it got this far then the size was not valid. As this error will probably be seen by whoever set up the
        // blog, return a useful message to help with debugging.

        throw new InvalidArgumentException('BlogEtcPost image size should be \'large\', \'medium\', \'thumbnail\'' . ' or another field as defined in config/blogetc.php. Provided size (' . e($size) . ') is not valid');
//        throw new InvalidImageSizeException('BlogEtcPost image size should be \'large\', \'medium\', \'thumbnail\''.' or another field as defined in config/blogetc.php. Provided size ('.e($size).') is not valid');
    }

    /**
     * If $this->seo_title was set, return that.
     * Otherwise just return $this->title.
     *
     * Basically return $this->seo_title ?? $this->title;
     *
     * TODO - what convention do we use for gen/generate/etc for naming of this.
     *
     * @return string
     */
    public function genSeoTitle(): ?string
    {
        if ($this->seo_title) {
            return $this->seo_title;
        }

        return $this->title;
    }

    /**
     * @deprecated - use genSeoTitle() instead
     */
    public function gen_seo_title(): ?string
    {
        return $this->genSeoTitle();
    }

    /**
     * @param mixed ...$args
     *
     * @deprecated - use imageTag() instead
     */
    public function image_tag(...$args)
    {
        return $this->imageTag(...$args);
    }

    /**
     * @param string $size
     *
     * @deprecated  - use hasImage() instead
     */
    public function has_image($size = 'medium'): bool
    {
        return $this->hasImage($size);
    }

    /**
     * @deprecated - use authorString() instead
     */
    public function author_string(): ?string
    {
        return $this->authorString();
    }

    /**
     * @deprecated - use editUrl() instead
     */
    public function edit_url(): string
    {
        return $this->editUrl();
    }

    /**
     * @deprecated - use checkValidImageSize()
     */
    protected function check_valid_image_size(string $size = 'medium'): bool
    {
        return $this->checkValidImageSize($size);
    }

    /**
     * @throws Exception
     *
     * @deprecated - use bladeViewFile() instead
     */
    public function full_view_file_path(): string
    {
        return $this->bladeViewFile();
    }

    /**
     * @param string $size
     *
     * @deprecated - use imageUrl() instead
     */
    public function image_url($size = 'medium'): string
    {
        return $this->imageUrl($size);
    }

    /**
     * @deprecated - use generateIntroduction() instead
     */
    public function generate_introduction(int $maxLen = 500): string
    {
        return $this->generateIntroduction($maxLen);
    }

    /**
     * @throws Throwable
     *
     * @deprecated - use renderBody() instead
     *
     * (post_body_output used to return a string, renderBody() now returns HtmlString)
     */
    public function post_body_output()
    {
        return $this->renderBody();
    }
}
