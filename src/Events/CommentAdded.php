<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\Comment;
use WebDevEtc\BlogEtc\Models\Post;

/**
 * Class CommentAdded
 * @package WebDevEtc\BlogEtc\Events
 */
class CommentAdded
{
    use Dispatchable, SerializesModels;

    /** @var Post */
    public $blogEtcPost;

    /** @var Comment */
    public $newComment;

    /**
     * CommentAdded constructor.
     * @param Post $blogEtcPost
     * @param Comment $newComment
     */
    public function __construct(Post $blogEtcPost, Comment $newComment)
    {
        $this->blogEtcPost = $blogEtcPost;
        $this->newComment = $newComment;
    }
}
