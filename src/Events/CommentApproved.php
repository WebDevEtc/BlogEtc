<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\BlogEtcComment;

/**
 * Class CommentApproved
 * @package WebDevEtc\BlogEtc\Events
 */
class CommentApproved
{
    use Dispatchable, SerializesModels;

    /** @var  BlogEtcComment */
    public $comment;

    /**
     * CommentApproved constructor.
     * @param BlogEtcComment $comment
     */
    public function __construct(BlogEtcComment $comment)
    {
        $this->comment = $comment;
        // you can get the blog post via $comment->post
    }
}
