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
    public $post;

    /**
     * BlogPostAdded constructor.
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }
}
