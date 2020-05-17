<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\BlogEtcComment;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;

/**
 * Class CommentAdded.
 */
class CommentAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var BlogEtcPost */
    public $blogEtcPost;
    /** @var BlogEtcComment */
    public $newComment;

    /**
     * CommentAdded constructor.
     * @param BlogEtcPost $blogEtcPost
     * @param BlogEtcComment $newComment
     */
    public function __construct(BlogEtcPost $blogEtcPost, BlogEtcComment $newComment)
    {
        $this->blogEtcPost = $blogEtcPost;
        $this->newComment = $newComment;
    }
}
