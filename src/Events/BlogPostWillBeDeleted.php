<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\Post;

/**
 * Class BlogPostWillBeDeleted.
 */
class BlogPostWillBeDeleted
{
    use Dispatchable, SerializesModels;

    /** @var Post */
    public $blogEtcPost;

    /**
     * BlogPostWillBeDeleted constructor.
     *
     * @param Post $blogEtcPost
     */
    public function __construct(Post $blogEtcPost)
    {
        $this->blogEtcPost = $blogEtcPost;
    }
}
