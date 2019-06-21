<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;

/**
 * Class BlogPostWillBeDeleted
 * @package WebDevEtc\BlogEtc\Events
 */
class BlogPostWillBeDeleted
{
    use Dispatchable, SerializesModels;

    /** @var BlogEtcPost */
    public $blogEtcPost;

    /**
     * BlogPostWillBeDeleted constructor.
     * @param BlogEtcPost $blogEtcPost
     */
    public function __construct(BlogEtcPost $blogEtcPost)
    {
        $this->blogEtcPost = $blogEtcPost;
    }
}
