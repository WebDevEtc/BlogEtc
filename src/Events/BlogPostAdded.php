<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use WebDevEtc\BlogEtc\Models\BlogEtcComment;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;

/**
 * Class BlogPostAdded
 * @package WebDevEtc\BlogEtc\Events
 */
class BlogPostAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  BlogEtcPost */
    public $blogEtcPost;

    /**
     * BlogPostAdded constructor.
     * @param BlogEtcPost $blogEtcPost
     */
    public function __construct(BlogEtcPost $blogEtcPost)
    {
        $this->blogEtcPost=$blogEtcPost;
    }

}
