<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\Post;

/**
 * Class BlogPostEdited
 * @package WebDevEtc\BlogEtc\Events
 */
class BlogPostEdited
{
    use Dispatchable, SerializesModels;

    /** @var  Post */
    public $blogEtcPost;

    /**
     * BlogPostEdited constructor.
     * @param Post $blogEtcPost
     */
    public function __construct(Post $blogEtcPost)
    {
        $this->blogEtcPost = $blogEtcPost;
    }
}
