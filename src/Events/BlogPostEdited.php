<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\Post;

/**
 * Class BlogPostEdited.
 */
class BlogPostEdited
{
    use Dispatchable;
    use SerializesModels;

    /** @var Post */
    public $blogEtcPost;

    /**
     * BlogPostEdited constructor.
     */
    public function __construct(Post $post)
    {
        $this->blogEtcPost = $post;
    }
}
