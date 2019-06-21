<?php

namespace WebDevEtc\BlogEtc\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use WebDevEtc\BlogEtc\Scopes\BlogCommentApprovedAndDefaultOrderScope;

/**
 * @property string author_name
 * @property User user
 * @property int user_id
 * @property mixed author_website
 * @property string|null ip
 * @property mixed author_email
 * @property bool approved
 */
class BlogEtcComment extends Model
{
    /**
     * Attributes which have specific casts
     *
     * @var array
     */
    public $casts = [
        'approved' => 'boolean',
    ];

    /**
     * Fillable attributes
     *
     * @var array
     */
    public $fillable = [
        'comment',
        'author_name',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        /* If user is logged in and \Auth::user()->canManageBlogEtcPosts() == true, show any/all posts.
           otherwise (which will be for most users) it should only show published posts that have a posted_at
           time <= Carbon::now(). This sets it up: */
        static::addGlobalScope(new BlogCommentApprovedAndDefaultOrderScope());
    }

    /**
     * The BlogEtcPost relationship
     *
     * @return BelongsTo
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogEtcPost::class, 'blog_etc_post_id');
    }

    /**
     * Comment author relationship
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('blogetc.user_model'));
    }

    /**
     * Return author string (either from the User (via ->user_id), or the submitted author_name value
     *
     * @return string|null
     */
    public function author(): ?string
    {
        if ($this->user_id) {
            // a user is associated with this
            $field = config('blogetc.comments.user_field_for_author_name', 'name');
            return optional($this->user)->$field;
        }

        // otherwise return the string value of 'author_name' which guests can submit:
        return $this->author_name;
    }
}
