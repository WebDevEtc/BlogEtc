<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\BlogEtcComment;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;

/**
 * Class CommentApproved
 * @package WebDevEtc\BlogEtc\Events
 */
class CommentApproved
{
    use Dispatchable, SerializesModels;

    /** @var BlogEtcComment */
    public $comment;

    /** @var BlogEtcPost */
    public $blogEtcPost;

    /**
     * CommentApproved constructor.
     * @param BlogEtcComment $comment
     */
    public function __construct(BlogEtcComment $comment)
    {
        $this->comment = $comment;
        $this->blogEtcPost = $comment->post;
    }
}
