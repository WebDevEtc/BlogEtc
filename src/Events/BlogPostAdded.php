<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;

/**
 * Class BlogPostAdded.
 */
class BlogPostAdded
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** @var BlogEtcPost */
    public $blogEtcPost;

    /**
     * BlogPostAdded constructor.
     */
    public function __construct(BlogEtcPost $blogEtcPost)
    {
        $this->blogEtcPost = $blogEtcPost;
    }
}
