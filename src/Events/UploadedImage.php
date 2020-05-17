<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\Image;
use WebDevEtc\BlogEtc\Models\Post;

/**
 * Class UploadedImage.
 */
class UploadedImage
{
    use Dispatchable;
    use SerializesModels;

    /** @var string */
    public $imageFilename;
    /** @var Image */
    public $image;
    /** @var Post */
    public $post;
    /** @var string|null */
    public $source;

    /**
     * UploadedImage constructor.
     *
     * $source =  the method name which was firing this event (or other string)
     *
     * @param string $imageFilename - the new filename
     * @param Post   $post
     */
    public function __construct(
        string $imageFilename,
        Image $image,
        Post $post = null,
        ?string $source = 'other'
    ) {
        $this->imageFilename = $imageFilename;
        $this->image = $image;
        $this->post = $post;
        $this->source = $source;
    }
}
