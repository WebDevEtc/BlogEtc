<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\BlogEtcComment;

/**
 * Class CommentApproved.
 */
class CommentApproved
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** @var BlogEtcComment */
    public $comment;

    /**
     * CommentApproved constructor.
     */
    public function __construct(BlogEtcComment $comment)
    {
        $this->comment = $comment;
        // you can get the blog post via $comment->post
    }
}
