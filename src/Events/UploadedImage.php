<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;
use WebDevEtc\BlogEtc\Models\Post;

/**
 * Class UploadedImage.
 */
class UploadedImage
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** @var Post|null */
    public $blogEtcPost;
    /**
     * @var
     */
    public $image;

    public $source;
    public $image_filename;

    /**
     * UploadedImage constructor.
     *
     * @param $image_filename - the new filename
     * @param Post $blogEtcPost
     * @param $image
     * @param $source string|null  the __METHOD__  firing this event (or other string)
     */
    public function __construct(
        string $image_filename,
        $image,
        Post $blogEtcPost = null,
        string $source = 'other'
    ) {
        $this->image_filename = $image_filename;
        $this->blogEtcPost = $blogEtcPost;
        $this->image = $image;
        $this->source = $source;
    }
}
