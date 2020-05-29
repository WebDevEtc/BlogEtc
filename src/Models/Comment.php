<?php

namespace WebDevEtc\BlogEtc\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use WebDevEtc\BlogEtc\Scopes\BlogCommentApprovedAndDefaultOrderScope;

class Comment extends Model
{
    public $casts = [
        'approved' => 'boolean',
    ];

    public $fillable = [
        'comment',
        'author_name',
    ];
    protected $table = 'blog_etc_comments';

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BlogCommentApprovedAndDefaultOrderScope());
    }

    /**
     * The associated BlogEtcPost.
     *
     * @return BelongsTo
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'blog_etc_post_id');
    }

    /**
     * Comment author user (if set).
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Return author string (either from the User (via ->user_id), or the submitted author_name value.
     *
     * @return string
     */
    public function author()
    {
        if ($this->user_id) {
            $field = config('blogetc.comments.user_field_for_author_name', 'name');

            return optional($this->user)->$field;
        }

        return $this->author_name;
    }
}
