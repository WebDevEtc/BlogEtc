<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;

/**
 * Class BlogPostWillBeDeleted
 * @package WebDevEtc\BlogEtc\Events
 */
class BlogPostWillBeDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  BlogEtcPost */
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
