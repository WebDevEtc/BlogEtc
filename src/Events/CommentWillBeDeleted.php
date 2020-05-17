<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\BlogEtcComment;

/**
 * Class CommentWillBeDeleted.
 */
class CommentWillBeDeleted
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** @var BlogEtcComment */
    public $comment;

    /**
     * CommentWillBeDeleted constructor.
     */
    public function __construct(BlogEtcComment $comment)
    {
        $this->comment = $comment;
    }
}
