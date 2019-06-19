<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;

/**
 * Class BlogPostEdited
 * @package WebDevEtc\BlogEtc\Events
 */
class BlogPostEdited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  BlogEtcPost */
    public $blogEtcPost;

    /**
     * BlogPostEdited constructor.
     * @param BlogEtcPost $blogEtcPost
     */
    public function __construct(BlogEtcPost $blogEtcPost)
    {
        $this->blogEtcPost = $blogEtcPost;
    }

}
