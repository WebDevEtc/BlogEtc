<?php namespace WebDevEtc\BlogEtc\Requests\Traits;


use WebDevEtc\BlogEtc\Helpers;

trait HasImageUploadTrait
{
    /**
     * @param $size
     * @return \Illuminate\Http\UploadedFile|null
     */
    public function get_image_file($size)
    {

        if ($this->file($size)) {
            return $this->file($size);
        }

        // not found? lets cycle through all the images and see if anything was submitted, and use that instead
        foreach (array_keys(Helpers::image_sizes()) as $size) {
            if ($this->file($size)) {
                return $this->file($size);
            }
        }

        return null;


    }

}