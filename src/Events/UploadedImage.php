<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;

/**
 * Class UploadedImage
 * @package WebDevEtc\BlogEtc\Events
 */
class UploadedImage
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  BlogEtcPost */
    public $blogEtcPost;
    /**
     * @var
     */
    public $image;

    /**
     * UploadedImage constructor.
     * @param BlogEtcPost $blogEtcPost
     * @param $image
     */
    public function __construct(BlogEtcPost $blogEtcPost, $image)
    {
        $this->blogEtcPost=$blogEtcPost;
        $this->image=$image;
    }

}
