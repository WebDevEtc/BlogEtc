<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\Post;

/**
 * Class BlogPostAdded
 * @package WebDevEtc\BlogEtc\Events
 */
class BlogPostAdded
{
    use Dispatchable, SerializesModels;

    /** @var Post */
    public $blogEtcPost;

    /**
     * BlogPostAdded constructor.
     * @param Post $blogEtcPost
     */
    public function __construct(Post $blogEtcPost)
    {
        $this->blogEtcPost = $blogEtcPost;
    }
}
