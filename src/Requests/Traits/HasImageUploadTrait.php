<?php namespace WebDevEtc\BlogEtc\Requests\Traits;

use Illuminate\Http\UploadedFile;

//TODO - remoe/replace this

/**
 * Trait HasImageUploadTrait
 * @package WebDevEtc\BlogEtc\Requests\Traits
 */
trait HasImageUploadTrait
{
    /**
     * @param $size
     * @return UploadedFile|null
     */
    public function get_image_file($size): ?UploadedFile
    {

        if ($this->file($size)) {
            return $this->file($size);
        }

        // not found? lets cycle through all the images and see if anything was submitted, and use that instead
        foreach (config('blogetc.image_sizes') as $image_size_name => $image_size_info) {
            if ($this->file($image_size_name)) {
                return $this->file($image_size_name);
            }
        }

        return null;
    }
}
