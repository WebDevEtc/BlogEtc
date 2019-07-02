<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\Image;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;

/**
 * Class UploadedImage
 * @package WebDevEtc\BlogEtc\Events
 */
class UploadedImage
{
    use Dispatchable, SerializesModels;
    /** @var string */
    private $imageFilename;
    /** @var Image */
    private $image;
    /** @var BlogEtcPost */
    private $blogEtcPost;
    /** @var string|null */
    private $source;

    /**
     * UploadedImage constructor.
     *
     * $source =  the method name which was firing this event (or other string)
     * @param $imageFilename - the new filename
     * @param BlogEtcPost $blogEtcPost
     * @param $image
     * @param $source string|null
     */
    public function __construct(
        string $imageFilename,
        Image $image,
        BlogEtcPost $blogEtcPost = null,
        ?string $source = 'other'
    ) {
        $this->imageFilename = $imageFilename;
        $this->image = $image;
        $this->blogEtcPost = $blogEtcPost;
        $this->source = $source;
    }
}
