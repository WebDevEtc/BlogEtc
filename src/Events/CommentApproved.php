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

class CommentApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  BlogEtcComment */
    public $comment;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(BlogEtcComment $comment)
    {
        $this->comment=$comment;
    }

}
