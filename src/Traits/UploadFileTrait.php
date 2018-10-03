<?php namespace WebDevEtc\BlogEtc\Traits;

use Illuminate\Http\UploadedFile;
use WebDevEtc\BlogEtc\Events\UploadedImage;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;
use File;

trait UploadFileTrait
{

    /**
     * If false, we check if the blog_images/ dir is writable, when uploading images
     * @var bool
     */
    protected $checked_blog_image_dir_is_writable = false;


    /**
     * Small method to increase memory limit.
     * This can be defined in the config file. If blogetc.memory_limit is false/null then it won't do anything.
     * This is needed though because if you upload a large image it'll not work
     */
    protected function increaseMemoryLimit()
    {
        // increase memory - change this setting in config file
        if (config("blogetc.memory_limit")) {
            @ini_set('memory_limit', config("blogetc.memory_limit"));
        }
    }


    /**
     * Get a filename (that doesn't exist) on the filesystem.
     *
     * Todo: support multiple filesystem locations.
     * Todo: move to its own file
     *
     * @param string $suggested_title
     * @param $image_size_details - either an array (with w/h attributes) or a string
     * @param UploadedFile $photo
     * @return string
     * @throws \RuntimeException
     */
    protected function getImageFilename(string $suggested_title,  $image_size_details, UploadedFile $photo)
    {


        $base = substr($suggested_title, 0, 100);
        if (!$base) {
            // if we have an empty string then we should use a random one:
            $base = 'image-'.str_random(5);
        }

        if (is_array($image_size_details)) {
            $wh = '-' . $image_size_details['w'] . 'x' . $image_size_details['h'];
        }
        elseif(is_string($image_size_details)) {

            $wh="-".str_slug($image_size_details);
        }
        else {
            throw new \RuntimeException("Invalid image_size_details: must be an array with w and h, or a string");
        }
        $ext = '.' . $photo->getClientOriginalExtension();


        $i = 1;

        while (true) {

            // add suffix if $i>1
            $suffix = $i > 1 ? '-' . str_random(5) : '';
            $attempt = str_slug($base . $suffix . $wh) . $ext;

            if (!File::exists($this->image_destination_path() . "/" . $attempt)) {
                return $attempt;
            }


            if ($i > 100) {
                throw new \RuntimeException("Unable to find a free filename after $i attempts - aborting now.");
            }

            $i++;
        }


    }


    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function image_destination_path()
    {
        $path = public_path('/' . config("blogetc.blog_upload_dir"));

        if (!$this->checked_blog_image_dir_is_writable) {
            if (!is_writable($path)) {
                throw new \RuntimeException("Image destination path is not writable ($path)");
            }
            $this->checked_blog_image_dir_is_writable = true;
        }

        return $path;
    }


    /**
     * @param BlogEtcPost $new_blog_post
     * @param $suggested_title - used to help generate the filename
     * @param $image_size_details - either an array (with 'w' and 'h') or a string (and it'll be uploaded at full size, no size reduction, but will use this string to generate the filename)
     * @param $photo
     * @return array
     * @throws \Exception
     */
    protected function UploadAndResize(BlogEtcPost $new_blog_post= null, $suggested_title, $image_size_details, $photo)
    {
        // get the filename/filepath
        $image_filename = $this->getImageFilename($suggested_title, $image_size_details, $photo);
        $destinationPath = $this->image_destination_path();

        // make image
        $resizedImage = \Image::make($photo->getRealPath());

        // if $iamge_size_detail is a string (i.e. full) then we need the original w/h values
        $w = $resizedImage->width();
        $h = $resizedImage->height();


        if (is_array($image_size_details)) {
            // resize
            $w = $image_size_details['w'];
            $h = $image_size_details['h'];
            if (isset($image_size_details['crop']) && $image_size_details['crop']) {
                $resizedImage = $resizedImage->fit($w, $h);
            } else {

                $resizedImage = $resizedImage->resize($w, $h, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
        }
        elseif ($image_size_details === 'fullsize') {
            // nothing to do here - no resizing needed.
        }
        else {
            throw new \Exception("Invalid image_size_details value");
        }

        // save image
        $resizedImage->save($destinationPath . '/' . $image_filename, config("blogetc.image_quality", 80));

        // fireevent
        event(new UploadedImage($image_filename, $resizedImage, $new_blog_post, __METHOD__));

        // return the filename
        return [
            'filename' => $image_filename,
            'w'=>$w,
            'h'=>$h,
        ];

    }


}