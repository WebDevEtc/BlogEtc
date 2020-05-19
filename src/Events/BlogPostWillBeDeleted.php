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
    use Dispatchable;
    use SerializesModels;

    /** @var Post */
    public $blogEtcPost;

    /**
     * BlogPostWillBeDeleted constructor.
     */
    public function __construct(Post $post)
    {
        $this->blogEtcPost = $post;
    }
}
