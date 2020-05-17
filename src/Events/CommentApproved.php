<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\Comment;
use WebDevEtc\BlogEtc\Models\Post;

/**
 * Class CommentApproved.
 */
class CommentApproved
{
    use Dispatchable;
    use SerializesModels;

    /** @var Comment */
    public $comment;

    /** @var Post */
    public $post;

    /**
     * CommentApproved constructor.
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
        $this->post = $comment->post;
    }
}
