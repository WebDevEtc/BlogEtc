<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\Comment;

/**
 * Class CommentWillBeDeleted.
 */
class CommentWillBeDeleted
{
    use Dispatchable;
    use SerializesModels;

    /** @var Comment */
    public $comment;

    /**
     * CommentWillBeDeleted constructor.
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }
}
