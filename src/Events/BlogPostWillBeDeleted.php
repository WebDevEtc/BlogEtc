<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;

/**
 * Class BlogPostWillBeDeleted.
 */
class BlogPostWillBeDeleted
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** @var BlogEtcPost */
    public $blogEtcPost;

    /**
     * BlogPostWillBeDeleted constructor.
     */
    public function __construct(BlogEtcPost $blogEtcPost)
    {
        $this->blogEtcPost = $blogEtcPost;
    }
}
