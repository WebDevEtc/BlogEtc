<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\Comment;
use WebDevEtc\BlogEtc\Models\Post;

/**
 * Class CommentAdded.
 */
class CommentAdded
{
    use Dispatchable;
    use SerializesModels;

    /** @var Post */
    public $blogEtcPost;
    /** @var Comment */
    public $newComment;

    /**
     * CommentAdded constructor.
     */
    public function __construct(Post $post, Comment $newComment)
    {
        $this->blogEtcPost = $post;
        $this->newComment = $newComment;
    }
}
